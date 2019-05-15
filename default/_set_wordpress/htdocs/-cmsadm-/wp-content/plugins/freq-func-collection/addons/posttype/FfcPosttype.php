<?php

if (!class_exists('FfcPosttype')) :

/**
 * カスタム投稿タイプクラス.
 * カスタム投稿タイプの追加を行う。
 */
class FfcPosttype extends FfcBaseClass
{

    const CONFIG_NAME = 'posttype';
    const DIRECTORY_NAME = 'posttype';
    const SEPARATOR_NAME_PREFIX = 'add-separator';
    const DEFAULT_POSITION = 25;
    protected $forbiddenPosttypeSlug = array(
        'post',
        'page',
        'attachment',
        'revision',
        'nav_menu_item',
        'action',
        'order',
        'theme'
    );
    protected $defaultPostPosition = array(
        'post' => 5,
        'page' => 20
    );
    protected $config = array();

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
        // 設定がなければ何もしない
        if (empty($this->config)) {
            return;
        }

        // 標準の投稿の設定があれば変更する
        if ($this->exists('post', $this->config)
            || $this->exists('page', $this->config)
        ) {
            $this->changeDefaultPost();
        }

        // カスタム投稿の設定
        foreach ($this->config as $slug => $posttype) {
            if (in_array($slug, $this->forbiddenPosttypeSlug, true)) {
                continue;
            }

            // post,page以外があればカスタム投稿あり
            add_action('init', array($this, 'registerPosttype'));
            break;
        }

        add_filter('enter_title_here', array($this, 'enterTitleHere'), 10, 2);
    }

    /**
     * デフォルト投稿を変更する.
     *
     * @access protected
     * @return void
     */
    protected function changeDefaultPost()
    {
        add_action('admin_menu', array($this, 'deleteDefaultPost'));
        add_action('admin_bar_menu', array($this, 'deleteDefaultPostFromAdminbar'), 99 ,1);
        add_action('init', array($this, 'changeDefaultPostLabels'));
    }

    /**
     * メニューからデフォルト投稿を削除する.
     * ・対象のデフォルト投稿はpost,pageのみ
     * ・"name": "" の時のみ行う。
     *
     * @access public
     * @return void
     */
    public function deleteDefaultPost()
    {
        global $menu;

        foreach ($this->defaultPostPosition as $type => $position) {
            if ($this->exists($type, $this->config)
                && $this->exists('name', $this->config[$type])
                && !strlen($this->config[$type]['name'])
            ) {
                // 投稿名があって空ならメニューから消す
                unset($menu[$position]);
            }
        }
    }

    /**
     * 管理バーの新規メニューからデフォルト投稿を削除する.
     * ・対象のデフォルト投稿はpost,pageのみ
     * ・"name": "" の時のみ行う。
     *
     * @access public
     * @return void
     */
    public function deleteDefaultPostFromAdminbar($wp_admin_bar)
    {
        foreach ($this->defaultPostPosition as $type => $position) {
            if ($this->exists($type, $this->config)
                && $this->exists('name', $this->config[$type])
                && !strlen($this->config[$type]['name'])
            ) {
                // 投稿名があって空なら管理メニューの新規から消す
                $wp_admin_bar->remove_menu('new-' . $type);
            }
        }
    }

    /**
     * デフォルト投稿の名前、メニュー位置、アイコンを変更する.
     * ・対象のデフォルト投稿はpost,pageのみ
     * ・"name": "" 以外の時のみ行う。
     *
     * @access public
     * @return void
     */
    public function changeDefaultPostLabels()
    {
        global $wp_post_types, $wp_taxonomies;

        foreach ($this->defaultPostPosition as $type => $position) {
            if (!$this->exists($type, $this->config)) {
                continue;
            }

            // 投稿名の設定があれば表示を変更する
            if ($this->exists('name', $this->config[$type], false)
                && is_string($this->config[$type]['name'])
            ) {
                $orgName = $wp_post_types[$type]->labels->name;
                $newName = $this->config[$type]['name'];

                $labels = get_object_vars($wp_post_types[$type]->labels);
                foreach ($labels as $key => $label) {
                    if (!is_string($label)) {
                        continue;
                    }
                    $wp_post_types[$type]->labels->{$key} = str_replace($orgName, $newName, $label);
                }
            }

            // メニュー位置の設定があれば位置を変更する
            if ($this->exists('position', $this->config[$type], false, false, false)
                && is_numeric($this->config[$type]['position'])
            ) {
                $wp_post_types[$type]->menu_position = $this->config[$type]['position'];
            }

            // メニューアイコンの設定があればアイコンを変更する
            if ($this->exists('icon', $this->config[$type], false)
                && is_string($this->config[$type]['icon'])
            ) {
                $wp_post_types[$type]->menu_icon = $this->config[$type]['icon'];
            }

            // タクソノミー
            if ('post' === $type) {
                if ($this->exists('taxonomy', $this->config[$type])
                    && is_array($this->config[$type]['taxonomy'])
                ) {
                    $taxonomy = $this->config[$type]['taxonomy'];
                    // category, post_tagの名称変更を先に処理する
                    foreach ($taxonomy as $slug => $tax) {
                        if ('category' === $slug
                            || 'post_tag' === $slug
                        ) {
                            $orgName = $wp_taxonomies[$slug]->labels->name;
                            $newName = $tax['name'];

                            $labels = get_object_vars($wp_taxonomies[$slug]->labels);
                            foreach ($labels as $key => $label) {
                                if (!is_string($label)) {
                                    continue;
                                }
                                $wp_taxonomies[$slug]->labels->{$key} = str_replace($orgName, $newName, $label);
                            }
                        }
                    }
                    // category, post_tagを省いて他に設定があれば登録する
                    unset($taxonomy['category'], $taxonomy['post_tag']);
                    if (!empty($taxonomy)) {
                        $this->registerTaxonomy($type, $this->config[$type]['taxonomy']);
                    }
                }
            }
        }
    }

    /**
     * カスタム投稿タイプを登録する.
     *
     * @access public
     * @return void
     */
    public function registerPosttype()
    {
        global $wp_post_types;

        $defaultPostLabels = get_object_vars($wp_post_types['post']->labels);

        foreach ($this->config as $slug => $posttype) {
            if (!is_array($posttype)) {
                continue;
            }

            if (in_array($slug, $this->forbiddenPosttypeSlug, true)) {
                if (!$this->exists($slug, $this->defaultPostPosition)) {
                    unset($this->config[$slug]);
                }
                continue;
            }

            // カスタム投稿名設定がなければスラッグを使用する
            $name = $slug;
            if ($this->exists('name', $posttype, false)
                && is_string($posttype['name'])
            ) {
                $name = $posttype['name'];
            }

            // メニュー位置設定がなければデフォルトを使用する
            $position = self::DEFAULT_POSITION;
            if ($this->exists('position', $posttype, false, false, false)
                && is_numeric($posttype['position'])
            ) {
                $position = $posttype['position'];
            }

            // アイコン設定がなければデフォルト(「投稿」のアイコン)とする
            $icon = null;
            if ($this->exists('icon', $posttype, false)
                && is_string($posttype['icon'])
            ) {
                $icon = $posttype['icon'];
            }

            // サポート設定がなければ「タイトル」「本文」「アイキャッチ」とする
            $supports = array('title', 'editor', 'thumbnail');
            if ($this->exists('supports', $posttype)
                && is_array($posttype['supports'])
            ) {
                $supports = array_values($posttype['supports']);
            }

            // トップページあり設定がなければなしとする
            $topArchive = false;
            if ($this->exists('archive', $posttype)
                && is_array($posttype['archive'])
                && in_array('top', $posttype['archive'], true)
            ) {
                // トップページあり
                $topArchive = true;
            }

            // リライト設定を投稿タイプより優先させるため先に登録する
            if ($this->exists('taxonomy', $posttype)) {
                $this->registerTaxonomy($slug, $posttype['taxonomy']);
            }

            // リライト設定
            $rewritePrefix = '';
            $rewriteSuffix = '';
            if ($this->exists('rewrite', $posttype)) {
                if ($this->exists('prefix', $posttype['rewrite'], false)
                    && is_string($posttype['rewrite']['prefix'])
                ) {
                    $rewritePrefix = $posttype['rewrite']['prefix'];
                }
                if ($this->exists('suffix', $posttype['rewrite'], false)
                    && is_string($posttype['rewrite']['suffix'])
                ) {
                    $rewriteSuffix = $posttype['rewrite']['suffix'];
                }
            }
            add_rewrite_tag('%' . $slug . '_rewrite%', preg_quote($rewritePrefix) . '(' . $slug . ')' . preg_quote($rewriteSuffix) , 'post_type=');

            // リライト
            $this->rewritePosttype($slug, $rewritePrefix, $rewriteSuffix, $posttype);

            // カスタム投稿を登録する
            register_post_type(
                $slug,
                array(
                    'labels'        => str_replace($defaultPostLabels['name'], $name, $defaultPostLabels),
                    'public'        => true,
                    'menu_position' => $position,
                    'menu_icon'     => $icon,
                    'hierarchical'  => false,
                    'supports'      => $supports,
                    'has_archive'   => $topArchive,
                    'rewrite'       => array(
                        'slug'       => $rewritePrefix . $slug . $rewriteSuffix,
                        'with_front' => false
                    )
                )
            );
        }
    }

    /**
     * カスタムタクソノミーを登録する.
     *
     * @param string $posttypeSlug カスタム投稿タイプのスラッグ
     * @param array  $taxonomy     カスタムタクソノミー設定
     *
     * @access protected
     * @return void
     */
    protected function registerTaxonomy($posttypeSlug, $taxonomy)
    {
        global $wp_taxonomies;

        $defaultTaxonomyLabels = get_object_vars($wp_taxonomies['category']->labels);

        foreach ($taxonomy as $slug => $tax) {
            // カスタム投稿名設定がなければスラッグを使用する
            $name = $slug;
            if ($this->exists('name', $tax, false)
                && is_string($tax['name'])
            ) {
                $name = $tax['name'];
            }

            // 親子関係設定がなければありとする
            $hierarchical = false;
            if ($this->exists('child', $tax)) {
                $hierarchical = !!$tax['child'];
            }

            // リライト設定
            $rewritePrefix = '';
            $rewriteSuffix = '';
            if ($this->exists('rewrite', $tax)) {
                if ($this->exists('prefix', $tax['rewrite'], false)
                    && is_string($tax['rewrite']['prefix'])
                ) {
                    $rewritePrefix = $tax['rewrite']['prefix'];
                }
                if ($this->exists('suffix', $tax['rewrite'], false)
                    && is_string($tax['rewrite']['suffix'])
                ) {
                    $rewriteSuffix = $tax['rewrite']['suffix'];
                }
            }

            // リライト
            $this->rewriteTaxonomy($posttypeSlug, $slug, $rewritePrefix, $rewriteSuffix, $tax);

            // カスタムタクソノミーを登録する
            register_taxonomy(
                $slug,
                $posttypeSlug,
                array(
                    'label'        => $name,
                    'labels'       => str_replace($defaultTaxonomyLabels['name'], $name, $defaultTaxonomyLabels),
                    'public'       => true,
                    'show_ui'      => true,
                    'hierarchical' => $hierarchical,
                    'rewrite'      => array(
                        'slug'       => $rewritePrefix . $slug . $rewriteSuffix,
                        'with_front' => false
                    )
                )
            );
        }
    }

    /**
     * カスタム投稿タイプのリライトを設定する.
     *
     * @param string $slug          カスタム投稿タイプのスラッグ
     * @param string $rewritePrefix リライト接頭辞設定
     * @param string $rewriteSuffix リライト接尾辞設定
     * @param array  $posttype      カスタム投稿タイプ設定
     *
     * @access protected
     * @return void
     */
    protected function rewritePosttype($slug, $rewritePrefix, $rewriteSuffix, $posttype)
    {
        if ($this->exists('archive', $posttype)
            && is_array($posttype['archive'])
        ) {
            if (in_array('year', $posttype['archive'], true)) {
                $this->rewritePostTypeYearArchive($slug, $rewritePrefix, $rewriteSuffix, $posttype);
            }
            if (in_array('month', $posttype['archive'], true)) {
                $this->rewritePostTypeMonthArchive($slug, $rewritePrefix, $rewriteSuffix, $posttype);
            }
        }
    }

    /**
     * カスタム投稿タイプの年別アーカイブのリライトを登録する.
     *
     * @param string $slug          カスタム投稿タイプのスラッグ
     * @param string $rewritePrefix リライト接頭辞設定
     * @param string $rewriteSuffix リライト接尾辞設定
     * @param array  $posttype      カスタム投稿タイプ設定
     *
     * @access protected
     * @return void
     */
    protected function rewritePostTypeYearArchive($slug, $rewritePrefix, $rewriteSuffix, $posttype)
    {
        // 年別アーカイブ
        add_permastruct($slug . '_year', '/%' . $slug . '_rewrite%/%year%/', array(
            'with_front' => false,
            'paged'      => true
        ));
    }

    /**
     * カスタム投稿タイプの月別アーカイブのリライトを登録する.
     *
     * @param string $slug          カスタム投稿タイプのスラッグ
     * @param string $rewritePrefix リライト接頭辞設定
     * @param string $rewriteSuffix リライト接尾辞設定
     * @param array  $posttype      カスタム投稿タイプ設定
     *
     * @access protected
     * @return void
     */
    protected function rewritePostTypeMonthArchive($slug, $rewritePrefix, $rewriteSuffix, $posttype)
    {
        // 月別アーカイブ
        add_permastruct($slug . '_month', '/%' . $slug . '_rewrite%/%year%/%monthnum%/', array(
            'with_front' => false,
            'paged'      => true
        ));
    }

    /**
     * カスタムタクソノミーのリライトを設定する.
     *
     * @param string $posttypeSlug  カスタム投稿タイプのスラッグ
     * @param string $slug          カスタムタクソノミーのスラッグ
     * @param string $rewritePrefix リライト接頭辞設定
     * @param string $rewriteSuffix リライト接尾辞設定
     * @param array  $tax           カスタムタクソノミー設定
     *
     * @access protected
     * @return void
     */
    protected function rewriteTaxonomy($posttypeSlug, $slug, $rewritePrefix, $rewriteSuffix, $tax)
    {
        if ($this->exists('archive', $tax)
            && is_array($tax['archive'])
        ) {
            if (in_array('year', $tax['archive'])) {
                $this->rewriteTaxonomyYearArchive($posttypeSlug, $slug, $rewritePrefix, $rewriteSuffix, $tax);
            }
            if (in_array('month', $tax['archive'])) {
                $this->rewriteTaxonomyMonthArchive($posttypeSlug, $slug, $rewritePrefix, $rewriteSuffix, $tax);
            }
        }
    }

    /**
     * カスタムタクソノミーの年別アーカイブのリライトを登録する.
     *
     * @param string $posttypeSlug  カスタム投稿タイプのスラッグ
     * @param string $slug          カスタムタクソノミーのスラッグ
     * @param string $rewritePrefix リライト接頭辞設定
     * @param string $rewriteSuffix リライト接尾辞設定
     * @param array  $tax           カスタムタクソノミー設定
     *
     * @access protected
     * @return void
     */
    protected function rewriteTaxonomyYearArchive($posttypeSlug, $slug, $rewritePrefix, $rewriteSuffix, $tax)
    {
        // 年別アーカイブ
        add_permastruct($slug . '_year', '/' . $rewritePrefix . $slug . $rewriteSuffix . '/%' . $slug . '%/%year%/', array(
            'with_front' => false,
            'paged'      => true
        ));
    }

    /**
     * カスタムタクソノミーの月別アーカイブのリライトを登録する.
     *
     * @param string $posttypeSlug  カスタム投稿タイプのスラッグ
     * @param string $slug          カスタムタクソノミーのスラッグ
     * @param string $rewritePrefix リライト接頭辞設定
     * @param string $rewriteSuffix リライト接尾辞設定
     * @param array  $tax           カスタムタクソノミー設定
     *
     * @access protected
     * @return void
     */
    protected function rewriteTaxonomyMonthArchive($posttypeSlug, $slug, $rewritePrefix, $rewriteSuffix, $tax)
    {
        // 月別アーカイブ
        add_permastruct($slug . '_month', '/' . $rewritePrefix . $slug . $rewriteSuffix . '/%' . $slug . '%/%year%/%monthnum%/', array(
            'with_front' => false,
            'paged'      => true
        ));
    }

    /**
     * 管理画面メニューに区切りを挿入する.
     *
     * @param string $posttypeSlug  カスタム投稿タイプのスラッグ
     * @param string $slug          カスタムタクソノミーのスラッグ
     * @param string $rewritePrefix リライト接頭辞設定
     * @param string $rewriteSuffix リライト接尾辞設定
     * @param array  $tax           カスタムタクソノミー設定
     *
     * @access protected
     * @return void
     */
    protected function addAdminMenuSeparator($position) {
        global $menu;

        $prefixLength = strlen(self::SEPARATOR_NAME_PREFIX);
        $index = 1;
        foreach ($menu as $offset => $section) {
            if (self::SEPARATOR_NAME_PREFIX === substr($section[2], 0, $prefixLength)) {
                $index++;
            }
        }

        for ($i = $position; $i < 99; $i++) {
            if (!$this->exists($i, $menu)) {
                $menu[$i] = array(
                    '',
                    'read',
                    self::SEPARATOR_NAME_PREFIX . sprintf('%02d', $index),
                    '',
                    'wp-menu-separator'
                );
                break;
            }
        }

        ksort($menu);
    }

    /**
     * タイトルのプレースホルダーを変更する.
     *
     * @param string  $enter_title_here タイトルのプレースホルダー
     * @param WP_Post $post             投稿のインスタンス
     *
     * @access public
     * @return void
     */
    public function enterTitleHere($enter_title_here, $post)
    {
        if ($this->exists($post->post_type, $this->config)
            && $this->exists('title-placeholder', $this->config[$post->post_type], false)
            && is_string($this->config[$post->post_type]['title-placeholder'])
        ) {
            $enter_title_here = esc_html($this->config[$post->post_type]['title-placeholder']);
        }

        return $enter_title_here;
    }

    /**
     * 投稿カスタムフィールドの選択式フィールドの選択肢を取得する.
     *
     * @param integer $post_id 投稿ID
     * @param string  $name    フィールドのname
     *
     * @access public
     * @return array 選択肢配列
     */
    public function getPostOptions($post_id, $name)
    {
        $objPost = get_post($post_id);
        $postType = $objPost->post_type;
        $options = array();
        if ($this->exists($postType, $this->config)
            && $this->exists('metabox', $this->config[$postType])
            && is_array($this->config[$postType]['metabox'])
        ) {
            $isFound = false;
            foreach ($this->config[$postType]['metabox'] as $metabox) {
                if (!$this->exists('fields', $metabox)
                    || !is_array($metabox['fields'])
                ) {
                    continue;
                }

                foreach ($metabox['fields'] as $field) {
                    if ($name !== $field['key']
                        || !$this->exists('options', $field)
                    ) {
                        continue;
                    }

                    $options = $field['options'];
                    $isFound = true;
                    break;
                }

                if ($isFound) {
                    break;
                }
            }
        }

        return $options;
    }

    /**
     * タームカスタムフィールドの選択式フィールドの選択肢を取得する.
     *
     * @param integer $term_id タームID
     * @param string  $name    フィールドのname
     *
     * @access public
     * @return array 選択肢配列
     */
    public function getTermOptions($term_id, $name)
    {
        $objTerm = get_term($term_id);
        $taxonomy = $objTerm->taxonomy;
        $objTaxonomy = get_taxonomy($taxonomy);
        $postType = $objTaxonomy->object_type[0];

        $options = array();
        if ($this->exists($postType, $this->config)
            && $this->exists('taxonomy', $this->config[$postType])
            && $this->exists($taxonomy, $this->config[$postType]['taxonomy'])
            && is_array($this->config[$postType]['taxonomy'][$taxonomy])
        ) {
            $setting = $this->config[$postType]['taxonomy'][$taxonomy];
            if ($this->exists('fields', $setting)
                && is_array($setting['fields'])
            ) {
                foreach ($setting['fields'] as $field) {
                    if ($name !== $field['key']
                        || !$this->exists('options', $field)
                    ) {
                        continue;
                    }

                    $options = $field['options'];
                    break;
                }
            }
        }

        return $options;
    }

}

endif;
