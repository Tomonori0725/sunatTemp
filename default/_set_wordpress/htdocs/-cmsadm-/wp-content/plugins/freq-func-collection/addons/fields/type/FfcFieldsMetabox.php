<?php

if (!class_exists('FfcFieldsMetabox')) :

/**
 * メタボックス複数フィールドクラス.
 * メタボックスの複数フィールドの設定を行う。
 */
class FfcFieldsMetabox extends FfcBaseClass
{

    const CONFIG_NAME = 'posttype';
    const DIRECTORY_NAME = 'fields';
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

        $isExistsPosttypeMetabox = false;
        foreach ($this->config as $slug => $posttype) {
            if ($this->exists('metabox', $posttype, false, false, false)
                && is_array($posttype['metabox'])
            ) {
                // カスタムフィールドあり
                $isExistsPosttypeMetabox = true;
                add_action('add_meta_boxes_' . $slug, array($this, 'addMetaboxes'), 10, 1);
                add_action('save_post_' . $slug, array($this, 'savePost'), 10, 3);
            }
        }
        if ($isExistsPosttypeMetabox) {
            add_filter('the_posts', array($this, 'thePosts'), 10, 2);
            add_filter('get_post_metadata', array($this, 'getPostMetadata'), 10, 4);
        }
    }

    protected function initialConfig($slug)
    {
        if ($this->exists($slug, $this->isInitializedConfig, false, false, false)) {
            return;
        }
        $posttype = $this->config[$slug];
        if ($this->exists('metabox', $posttype, false, false, false)
            && is_array($posttype['metabox'])
        ) {
            foreach ($posttype['metabox'] as $no => $fields) {
                if (!is_array($fields)
                    || !$this->exists('fields', $fields)
                    || !is_array($fields['fields'])
                ) {
                    unset($this->config[$slug]['metabox'][$no]);
                    continue;
                }
                $this->config[$slug]['metabox'][$no]['fields'] = $this->main->field->setDefaultConfig($fields['fields']);
            }
        }
        $this->isInitializedConfig[$slug] = true;
    }

    /**
     * カスタム投稿タイプにメタボックスを追加する.
     *
     * @param WP_Post $post 投稿のインスタンス
     *
     * @access public
     * @return void
     */
    public function addMetaboxes($post)
    {
        $this->initialConfig($post->post_type);

        $arrMeta = $this->getMetaData();
        foreach ($this->config[$post->post_type]['metabox'] as $no => $metabox) {
            $no++;
            $id = $post->post_type . 'Section' . $no;
            if (!$this->exists('title', $metabox, false)
               || !is_string($metabox['title'])
            ) {
                $metabox['title'] = $id . __('Box', FFCOLLECTION_PLUGIN_DIR_NAME);
                add_filter('postbox_classes_' . $post->post_type . '_' . $id, array($this, 'postboxClasses'));
            }
            $title = $metabox['title'];
            add_meta_box(
                $id,
                $title,
                array($this, 'createMetaSection'),
                $post->post_type,
                'normal',
                'high',
                array_merge($arrMeta, array('sectionNo' => $no))
            );
        }
    }

    public function postboxClasses($classes) {
        array_push($classes, 'noBoxSection');

        return $classes;
    }

    public function createMetaSection($post, $arrMeta)
    {
        $arrMeta = $arrMeta['args'];
        $config = $this->config[$post->post_type]['metabox'][$arrMeta['sectionNo'] - 1];

        // 最初のメタボックスのみnonceを出力する
        if (1 === $arrMeta['sectionNo']) {
            echo '<input type="hidden" name="custom_nonce" value="' . wp_create_nonce(FFCOLLECTION_PLUGIN_DIR_PATH) . '" />';
        }

        // メタボックス用テンプレートがあればそちらを使用する
        if ($this->exists('template', $config, false)
            && is_string($config['template'])
        ) {
//            include get_path('wp-style') . self::METABOX_PATH . $config['template'] . '.php';
//            return;
        }

        // テンプレートがなければフィールドを自動生成する
        foreach ($config['fields'] as $setting) {
            if ($this->exists('title-hide', $setting) && $setting['title-hide']) {
                $attr = 'class="screen-reader-text"';
            } else {
                $attr = 'for="' . esc_attr($setting['id']) . '"';
            }

            echo '<div class="field">';
            echo '<label ' . $attr . '>' . esc_html($setting['title']) . '</label>';
            $setting['instance']->createField($arrMeta[$setting['key']]['value']);
            echo '</div>';
        }
    }

    public function getMetaData($post_id = null, $isDefault = true)
    {
        if (is_null($post_id)) {
            $post_id = get_the_ID();
        }
        $arrMeta = array();
        $isNew = false;
        $posttype = get_post_type($post_id);
        $arrCustom = get_post_custom($post_id);

        // カスタムフィールドが空なら新規追加である
        // 新規でなければ値が空でも管理用データが入っている
        if (empty($arrCustom)) {
            $isNew = true;
        }

        if (!$this->exists($posttype, $this->config)
            || !$this->exists('metabox', $this->config[$posttype])
            || !is_array($this->config[$posttype]['metabox'])
        ) {
            return;
        }

        $metaboxes = $this->config[$posttype]['metabox'];
        foreach ($metaboxes as $metabox) {
            if (!$this->exists('fields', $metabox)
                || !is_array($metabox['fields'])
            ) {
                continue;
            }

            foreach ($metabox['fields'] as $setting) {
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
        }

        return $arrMeta;
    }

    public function savePost($post_id, $post, $update)
    {
        if (!$this->checkSavePost($post_id, $post, $update)
            || !$this->exists($post->post_type, $this->config)
            || !$this->exists('metabox', $this->config[$post->post_type])
            || !is_array($this->config[$post->post_type]['metabox'])
        ) {
            return;
        }

        $this->initialConfig($post->post_type);

        // カスタムフィールドを保存する
        $metaboxes = $this->config[$post->post_type]['metabox'];
        foreach ($metaboxes as $metabox) {
            if (!$this->exists('fields', $metabox)
                || !is_array($metabox['fields'])
            ) {
                continue;
            }

            foreach ($metabox['fields'] as $setting) {
                if (!$this->exists($setting['name'], $_POST)) {
                    // POSTされてなければDBから削除する
                    delete_post_meta($post_id, $setting['name']);
                    continue;
                }

                $setting['instance']->savePost($post_id, $_POST[$setting['name']]);
            }
        }
    }

    protected function checkSavePost($post_id, $post, $update)
    {
        // nonce
        $custom_nonce = null;
        if ($this->exists('custom_nonce', $_POST, false)
            && is_string($_POST['custom_nonce'])
        ) {
            $custom_nonce = $_POST['custom_nonce'];
        }
        if (is_null($custom_nonce)) {
            return false;
        }
        if (!wp_verify_nonce($custom_nonce, FFCOLLECTION_PLUGIN_DIR_PATH)) {
            return false;
        }

        // 権限
        $edit = 'edit_post';
        if ('page' === $post->post_type) {
            $edit = 'edit_page';
        }
        if (!current_user_can($edit, $post_id)) {
            return false;
        }

        // 自動保存
        if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            || 'auto-draft' === $post->post_status
        ) {
            return false;
        }

        return true;
    }

    // カスタムフィールドの値を投稿データに含める
    public function thePosts($posts, $wp_query)
    {
        foreach ($posts as $key => $post) {
            if (!$this->exists($post->post_type, $this->config)
                || !$this->exists('metabox', $this->config[$post->post_type])
                || !is_array($this->config[$post->post_type]['metabox'])
            ) {
                continue;
            }

            $this->initialConfig($post->post_type);

            $posts[$key]->metaFields = $this->getMetaData($post->ID);
        }

        return $posts;
    }

    public function getPostMetadata($dummy, $object_id, $meta_key, $single)
    {
        $ret = $dummy;

        if (!strlen($meta_key)) {
            return $ret;
        }

        $meta_cache = wp_cache_get($object_id, 'post_meta');
        if (!$meta_cache) {
            $meta_cache = update_meta_cache('post', array($object_id));
            $meta_cache = $meta_cache[$object_id];
        }

        $post = get_post($object_id);
        if (is_null($post)
            || !$this->exists($post->post_type, $this->config)
            || !$this->exists('metabox', $this->config[$post->post_type])
            || !is_array($this->config[$post->post_type]['metabox'])
        ) {
            return $ret;
        }

        $metaboxes = $this->config[$post->post_type]['metabox'];
        foreach ($metaboxes as $metabox) {
            if (!$this->exists('fields', $metabox)
                || !is_array($metabox['fields'])
            ) {
                continue;
            }

            foreach ($metabox['fields'] as $setting) {
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
                        wp_cache_set($object_id, $meta_cache, 'post_meta');
                    }
                }
            }
        }

        return $ret;
    }

}

endif;
