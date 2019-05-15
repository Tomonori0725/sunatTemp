<?php

if (!class_exists('FfcFieldsTaxonomy')) :

/**
 * タクソノミー複数フィールドクラス.
 * タクソノミーの複数フィールドの設定を行う。
 */
class FfcFieldsTaxonomy extends FfcBaseClass
{

    const CONFIG_NAME = 'posttype';
    const DIRECTORY_NAME = 'posttype';
    protected $config = array();
    protected $isInitializedConfig = array();

    /**
     * 初期化する.
     *
     * @access protected
     * @return void
     */
    protected function initialize()
    {
        // configのカスタム投稿タイプ設定を読み込む
        $this->config = $this->main->getConfig(self::CONFIG_NAME);

        foreach ($this->config as $slug => $posttype) {
            if ($this->exists('taxonomy', $posttype, false, false, false)
                && is_array($posttype['taxonomy'])
            ) {
                foreach ($posttype['taxonomy'] as $taxSlug => $tax) {
                    if ($this->exists('fields', $tax, false, false, false)
                        && is_array($tax['fields'])
                    ) {
                        // カスタムフィールドあり
                        add_action($taxSlug . '_add_form_fields', array($this, 'addFieldsTaxonomy'), 10, 2);
                        add_action($taxSlug . '_edit_form_fields', array($this, 'editFieldsTaxonomy'), 10, 2);
                        add_action('created_' . $taxSlug, array($this, 'saveFieldsTaxonomy'), 10, 2);
                        add_action('edited_' . $taxSlug, array($this, 'saveFieldsTaxonomy'), 10, 2);
//                        add_filter('manage_edit-' . $taxSlug . '_columns', array($this, 'manageEditTaxonomyColumns'));
//                        add_filter('manage_' . $taxSlug . '_custom_column', array($this, 'manageTaxonomyCustomColumn'), 10, 3);
                    }
                }
            }
        }
        add_filter('get_term_metadata', array($this, 'getTermMetadata'), 10, 4);
    }

    protected function initialConfig($posttypeSlug, $taxonomySlug)
    {
        if ($this->exists($taxonomySlug, $this->isInitializedConfig, false, false, false)) {
            return;
        }
        $tax = $this->config[$posttypeSlug]['taxonomy'][$taxonomySlug];
        if ($this->exists('fields', $tax, false, false, false)
            && is_array($tax['fields'])
        ) {
            // カスタムフィールドあり
            $this->config[$posttypeSlug]['taxonomy'][$taxonomySlug]['fields'] = $this->main->field->setDefaultConfig($tax['fields']);
        }
        $this->isInitializedConfig[$taxonomySlug] = true;
    }

    public function getMetaData($term_id = null, $isDefault = true)
    {
        $isNew = false;
        if (is_null($term_id)) {
            global $post_type, $taxonomy;
            $isNew = true;
            $posttype = $post_type;
            $arrCustom = array();
        } else {
            $objTerm = get_term($term_id);
            $taxonomy = $objTerm->taxonomy;
            $objTaxonomy = get_taxonomy($taxonomy);
            $posttype = $objTaxonomy->object_type[0];
            $arrCustom = get_term_meta($term_id);
        }
        $arrMeta = array();

        if (!$this->exists($posttype, $this->config)
            || !$this->exists('taxonomy', $this->config[$posttype])
            || !$this->exists($taxonomy, $this->config[$posttype]['taxonomy'])
            || !$this->exists('fields', $this->config[$posttype]['taxonomy'][$taxonomy])
            || !is_array($this->config[$posttype]['taxonomy'][$taxonomy]['fields'])
        ) {
            return;
        }

        $fields = $this->config[$posttype]['taxonomy'][$taxonomy]['fields'];
        foreach ($fields as $setting) {
            if (!is_array($setting)) {
                continue;
            }

            if ($isNew && $isDefault) {
                // 新規追加時で初期値を使用する時は初期値を入れる
                $arrCustom[$setting['name']] = $setting['default'];
            } else {
                $arrCustom = $setting['instance']->setData($arrCustom);
            }
            $arrMeta[$setting['key']] = array(
                'name'    => $setting['key'],
                'label'   => $setting['title'],
                'value'   => $arrCustom[$setting['name']],
                'setting' => $setting
            );
        }

        return $arrMeta;
    }

    public function addFieldsTaxonomy($taxonomy_slug)
    {
        $objTaxonomy = get_taxonomy($taxonomy_slug);
        $this->initialConfig($objTaxonomy->object_type[0], $taxonomy_slug);
        $fields = $this->config[$objTaxonomy->object_type[0]]['taxonomy'][$taxonomy_slug]['fields'];
        $arrMeta = $this->getMetaData();
        foreach ($fields as $setting) {
            ?>
            <div class="form-field term-<?php echo esc_attr($setting['id']); ?>-wrap">
                <label for="<?php echo esc_attr($setting['id']); ?>"><?php echo esc_html($setting['title']); ?></label>
                <?php
                $setting['instance']->createField($arrMeta[$setting['key']]['value']);
                ?>
            </div>
            <?php
        }
    }

    public function editFieldsTaxonomy($term, $taxonomy)
    {
        $objTaxonomy = get_taxonomy($taxonomy);
        $this->initialConfig($objTaxonomy->object_type[0], $taxonomy);
        $fields = $this->config[$objTaxonomy->object_type[0]]['taxonomy'][$taxonomy]['fields'];
        $arrMeta = $this->getMetaData($term->term_id);
        foreach ($fields as $setting) {
            ?>
            <tr class="form-field term-<?php echo esc_attr($setting['id']); ?>-wrap">
                <th scope="row">
                    <label for="<?php echo esc_attr($setting['id']); ?>"><?php echo esc_html($setting['title']); ?></label>
                </th>
                <td>
                    <?php
                    $setting['instance']->createField($arrMeta[$setting['key']]['value']);
                    ?>
                </td>
            </tr>
            <?php
        }
    }

    public function saveFieldsTaxonomy($term_id, $tt_id)
    {
        $objTerm = get_term($term_id);
        $taxonomy = $objTerm->taxonomy;
        $objTaxonomy = get_taxonomy($taxonomy);
        $posttype = $objTaxonomy->object_type[0];
        $this->initialConfig($posttype, $taxonomy);

        if (!$this->exists($posttype, $this->config)
            || !$this->exists('taxonomy', $this->config[$posttype])
            || !$this->exists($taxonomy, $this->config[$posttype]['taxonomy'])
            || !$this->exists('fields', $this->config[$posttype]['taxonomy'][$taxonomy])
            || !is_array($this->config[$posttype]['taxonomy'][$taxonomy]['fields'])
        ) {
            return;
        }

        $fields = $this->config[$posttype]['taxonomy'][$taxonomy]['fields'];
        foreach ($fields as $setting) {
            if (!$this->exists($setting['name'], $_POST)) {
                // POSTされてなければDBから削除する
                delete_term_meta($term_id, $setting['name']);
                continue;
            }

            $setting['instance']->saveTerm($term_id, $_POST[$setting['name']]);
        }
    }

    public function getTermMetadata($dummy, $object_id, $meta_key, $single)
    {
        $ret = $dummy;

        if (!strlen($meta_key)) {
            return $ret;
        }


        $meta_cache = wp_cache_get($object_id, 'term_meta');
        if (!$meta_cache) {
            $meta_cache = update_meta_cache('term', array($object_id));
            $meta_cache = $meta_cache[$object_id];
        }

        $term = get_term($object_id);

        foreach ($this->config as $postType) {
            if ($this->exists('taxonomy', $postType)
                && is_array($postType['taxonomy'])
            ) {
                if (!$this->exists($term->taxonomy, $postType['taxonomy'])
                    || !is_array($postType['taxonomy'][$term->taxonomy])
                    || !$this->exists('fields', $postType['taxonomy'][$term->taxonomy])
                    || !is_array($postType['taxonomy'][$term->taxonomy]['fields'])
                ) {
                    continue;
                }

                $fields = $postType['taxonomy'][$term->taxonomy]['fields'];
                foreach ($fields as $setting) {
                    if ($this->exists($setting['key'], $meta_cache)) {
                        continue;
                    }

                    if ('map' === $setting['type']) {
                        $lats = '';
                        if ($this->exists($setting['key'] . '-lat', $meta_cache)) {
                            $lats = $meta_cache[$setting['key'] . '-lat'];
                        }
                        $lngs = '';
                        if ($this->exists($setting['key'] . '-lng', $meta_cache)) {
                            $lngs = $meta_cache[$setting['key'] . '-lng'];
                        }
                        $zooms = '';
                        if ($this->exists($setting['key'] . '-zoom', $meta_cache)) {
                            $zooms = $meta_cache[$setting['key'] . '-zoom'];
                        }
                        if ($lats && $lngs && $zooms) {
                            $cache = array();
                            foreach ($lats as $no => $lat) {
                                $cache[] = array(
                                    'lat'  => $lat,
                                    'lng'  => $lngs[$no],
                                    'zoom' => $zooms[$no]
                                );
                            }
                            $meta_cache[$setting['key']] = $cache;
                            wp_cache_set($object_id, $meta_cache, 'term_meta');
                        }
                    }
                }
            }
        }

        return $ret;
    }

}

endif;
