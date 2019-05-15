<?php

abstract class ThemePostTypeBase extends ThemeClassBase
{

    protected $defaultPostPosition = array(
        'post' => 5,
        'page' => 20
    );
    protected $config = array();

    protected function initialize()
    {
        $config = $this->getConfig();
        if (!$this->exists('posttype', $config)
            || !is_array($config['posttype'])
        ) {
            return;
        }
        $this->config = $config['posttype'];

        // 標準の投稿の設定があれば変更する
        if ($this->exists('post', $this->config)
            || $this->exists('page', $this->config)
        ) {
            $this->changeDefaultPost();
        }

        // カスタム投稿の設定
        $isExistsCustomPost = false;
        foreach ($this->config as $key => $value) {
            // post,page以外があればカスタム投稿あり
            if ('post' !== $key && 'page' !== $key) {
                $isExistsCustomPost = true;
                break;
            }
        }
        if ($isExistsCustomPost) {
            add_action('init', array($this, 'registerPostType'));
            add_action('admin_menu', array($this, 'adminMenuSeparator'));
            add_action('get_archives_link', array($this, 'getArchivesLink'));
        }

        add_action('wp_terms_checklist_args', array($this, 'wpTermsChecklistArgs'));
        add_filter('enter_title_here', array($this, 'enterTitleHere'), 10, 2);
    }

    protected function changeDefaultPost()
    {
        if ($this->exists('post', $this->config) || $this->exists('page', $this->config)) {
            add_action('admin_menu', array($this, 'deleteDefaultPost'));
            add_action('admin_bar_menu', array($this, 'deleteDefaultPostFromAdminbar'), 999 ,1);
            add_action('init', array($this, 'changeDefaultPostLabels'));
        }
    }

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

    public function changeDefaultPostLabels()
    {
        global $wp_post_types;

        foreach ($this->defaultPostPosition as $type => $position) {
            if (!$this->exists($type, $this->config)) {
                continue;
            }

            // 投稿名の設定があれば表示を変更する
            if ($this->exists('name', $this->config[$type], false)) {
                $name = $this->config[$type]['name'];
                $labels = &$wp_post_types[$type]->labels;

                $labels->name                  = $name;
                $labels->singular_name         = $name;
                $labels->add_new               = '新規追加';
                $labels->add_new_item          = '新規' . $name . 'を追加';
                $labels->edit_item             = $name . 'の編集';
                $labels->new_item              = '新規' . $name;
                $labels->view_item             = $name . 'を表示';
                $labels->view_items            = $name . 'の表示';
                $labels->search_items          = $name . 'を検索';
                $labels->not_found             = $name . 'が見つかりませんでした。';
                $labels->not_found_in_trash    = 'ゴミ箱内に' . $name . 'が見つかりませんでした。';
                $labels->all_items             = $name . '一覧';
                $labels->archives              = $name . 'アーカイブ';
                $labels->attributes            = $name . 'の属性';
                $labels->insert_into_item      = $name . 'に挿入';
                $labels->uploaded_to_this_item = 'この' . $name . 'へのアップロード';
                $labels->filter_items_list     = $name . 'リストの絞り込み';
                $labels->items_list_navigation = $name . 'リストナビゲーション';
                $labels->items_list            = $name . 'リスト';
                $labels->menu_name             = $name;
                $labels->name_admin_bar        = $name;
            }

            // メニュー位置の設定があれば位置を変更する
            if ($this->exists('position', $this->config[$type])) {
                $wp_post_types[$type]->menu_position = $this->config[$type]['position'];
            }

            // メニューアイコンの設定があればアイコンを変更する
            if ($this->exists('icon', $this->config[$type])) {
                $wp_post_types[$type]->menu_icon = $this->config[$type]['icon'];
            }
        }
    }

    public function registerPostType()
    {
        foreach ($this->config as $slug => $posttype) {
            if ('post' === $slug || 'page' === $slug) {
                continue;
            }

            // カスタム投稿名設定がなければスラッグを使用する
            $name = $slug;
            if ($this->exists('name', $posttype, false)) {
                $name = $posttype['name'];
            }

            // メニュー位置設定がなければ31を使用する
            $position = 31;
            if ($this->exists('position', $posttype, false)) {
                $position = $posttype['position'];
            }

            // サポート設定がなければ「タイトル」「本文」「アイキャッチ」とする
            $supports = array('title', 'editor', 'thumbnail');
            if ($this->exists('supports', $posttype)
                && is_array($posttype['supports'])
            ) {
                $supports = array_values($posttype['supports']);
            }

            // アイコン設定がなければデフォルト(「投稿」のアイコン)とする
            $icon = null;
            if ($this->exists('icon', $posttype, false)) {
                $icon = $posttype['icon'];
            }

            // トップページあり設定がなければなしとする
            $topArchive = false;
            if ($this->exists('archive', $posttype)
                && in_array('top', $posttype['archive'])
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
                if ($this->exists('prefix', $posttype['rewrite'], false)) {
                    $rewritePrefix = $posttype['rewrite']['prefix'];
                }
                if ($this->exists('suffix', $posttype['rewrite'], false)) {
                    $rewriteSuffix = $posttype['rewrite']['suffix'];
                }
            }
            add_rewrite_tag('%' . $slug . '_rewrite%', preg_quote($rewritePrefix) . '(' . $slug . ')' . preg_quote($rewriteSuffix) , 'post_type=');

            // リライト
            $this->rewritePostType($slug, $rewritePrefix, $rewriteSuffix, $posttype);

            // カスタム投稿を登録する
            register_post_type(
                $slug,
                array(
                    'labels' => array(
                        'name'               => $name,
                        'singular_name'      => $name,
                        'menu_name'          => $name,
                        'name_admin_bar'     => $name,
                        'all_items'          => $name . '一覧',
                        'add_new'            => '新規追加',
                        'add_new_item'       => $name . 'を追加',
                        'edit_item'          => $name . 'の編集',
                        'new_item'           => '新規' . $name,
                        'view_item'          => $name . 'を表示',
                        'search_items'       => $name . 'を検索',
                        'not_found'          => $name . 'が見つかりませんでした。',
                        'not_found_in_trash' => 'ゴミ箱内に' . $name . 'が見つかりませんでした。',
                        'archives'           => $name
                    ),
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

    public function registerTaxonomy($posttypeSlug, $taxonomy)
    {
        foreach ($taxonomy as $slug => $tax) {
            // カスタム投稿名設定がなければスラッグを使用する
            $name = $slug;
            if ($this->exists('name', $tax, false)) {
                $name = $tax['name'];
            }

            // カスタムフィールド
            if ($this->exists('metabox', $tax)) {
                add_action($slug . '_add_form_fields', array($this, 'addFieldsTaxonomy'), 10, 2);
                add_action($slug . '_edit_form_fields', array($this, 'editFieldsTaxonomy'), 10, 2);
                add_action('created_' . $slug, array($this, 'saveFieldsTaxonomy'), 10, 2);
                add_action('edited_' . $slug, array($this, 'saveFieldsTaxonomy'), 10, 2);
                add_filter('manage_edit-' . $slug . '_columns', array($this, 'manageEditTaxonomyColumns'));
                add_filter('manage_' . $slug . '_custom_column', array($this, 'manageTaxonomyCustomColumn'), 10, 3);
            }

            // リライト設定
            $rewritePrefix = '';
            $rewriteSuffix = '';
            if ($this->exists('rewrite', $tax)) {
                if ($this->exists('prefix', $tax['rewrite'], false)) {
                    $rewritePrefix = $tax['rewrite']['prefix'];
                }
                if ($this->exists('suffix', $tax['rewrite'], false)) {
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
                    'label'    => $name,
                    'labels' => array(
                        'name'          => $name,
                        'singular_name' => $name,
                        'menu_name'     => $name,
                        'all_items'     => $name . '一覧',
                        'edit_item'     => $name . 'の編集',
                        'view_item'     => $name . 'を表示',
                        'update_item'   => $name . 'を更新',
                        'add_new_item'  => '新規' . $name . 'を追加',
                        'search_items'  => $name . 'を検索',
                        'not_found'     => $name . 'が見つかりませんでした。'
                    ),
                    'public'       => true,
                    'show_ui'      => true,
                    'hierarchical' => !array_key_exists('child', $tax) || $tax['child'],
                    'rewrite'      => array(
                        'slug'       => $rewritePrefix . $slug . $rewriteSuffix,
                        'with_front' => false
                    )
                )
            );
        }
    }

    public function adminMenuSeparator()
    {
        $this->addAdminMenuSeparator(30);
    }

    protected function addAdminMenuSeparator($position) {
        global $menu;

        $index = 1;
        foreach ($menu as $offset => $section) {
            if ('add-separator' === substr($section[2], 0, 13)) {
                $index++;
            }
            if ($offset >= $position) {
                if (isset($menu[$position])) {
                    $position++;
                } else {
                    $menu[$position] = array(
                        '',
                        'read',
                        'add-separator' . $index,
                        '',
                        'wp-menu-separator'
                    );
                    break;
                }
            }
        }
        ksort($menu);
    }

    protected function rewritePostType($slug, $rewritePrefix, $rewriteSuffix, $posttype)
    {
        if ($this->exists('archive', $posttype)) {
            if (in_array('year', $posttype['archive'])) {
                $this->rewritePostTypeYearArchive($slug, $rewritePrefix, $rewriteSuffix, $posttype);
            }
            if (in_array('month', $posttype['archive'])) {
                $this->rewritePostTypeMonthArchive($slug, $rewritePrefix, $rewriteSuffix, $posttype);
            }
        }
    }

    protected function rewritePostTypeYearArchive($slug, $rewritePrefix, $rewriteSuffix, $posttype)
    {
        // 年別アーカイブ
        add_permastruct($slug . '_year', '/%' . $slug . '_rewrite%/%year%/', array(
            'with_front' => false,
            'paged'      => true
        ));
    }

    protected function rewritePostTypeMonthArchive($slug, $rewritePrefix, $rewriteSuffix, $posttype)
    {
        // 月別アーカイブ
        add_permastruct($slug . '_month', '/%' . $slug . '_rewrite%/%year%/%monthnum%/', array(
            'with_front' => false,
            'paged'      => true
        ));
    }

    protected function rewriteTaxonomy($posttypeSlug, $slug, $rewritePrefix, $rewriteSuffix, $tax)
    {
        if ($this->exists('archive', $tax)) {
            if (in_array('year', $tax['archive'])) {
                $this->rewriteTaxonomyYearArchive($posttypeSlug, $slug, $rewritePrefix, $rewriteSuffix, $tax);
            }
            if (in_array('month', $tax['archive'])) {
                $this->rewriteTaxonomyMonthArchive($posttypeSlug, $slug, $rewritePrefix, $rewriteSuffix, $tax);
            }
        }
    }

    protected function rewriteTaxonomyYearArchive($posttypeSlug, $slug, $rewritePrefix, $rewriteSuffix, $tax)
    {
        // 年別アーカイブ
        add_permastruct($slug . '_year', '/' . $rewritePrefix . $slug . $rewriteSuffix . '/%' . $slug . '%/%year%/', array(
            'with_front' => false,
            'paged'      => true
        ));
    }

    protected function rewriteTaxonomyMonthArchive($posttypeSlug, $slug, $rewritePrefix, $rewriteSuffix, $tax)
    {
        // 月別アーカイブ
        add_permastruct($slug . '_month', '/' . $rewritePrefix . $slug . $rewriteSuffix . '/%' . $slug . '%/%year%/%monthnum%/', array(
            'with_front' => false,
            'paged'      => true
        ));
    }

    public function enterTitleHere($enter_title_here, $post)
    {
        if ($this->exists($post->post_type, $this->config)
            && $this->exists('title-placeholder', $this->config[$post->post_type])
        ) {
            $enter_title_here = esc_html($this->config[$post->post_type]['title-placeholder']);
        }
        return $enter_title_here;
    }

    public function wpTermsChecklistArgs($args)
    {
        $args['checked_ontop'] = false;

        if (!$this->exists('taxonomy', $args)) {
            return $args;
        }

        $taxonomy = get_taxonomy($args['taxonomy']);
        $postType = $taxonomy->object_type[0];
        if ($this->exists($postType, $this->config)
            && $this->exists('taxonomy', $this->config[$postType])
        ) {
            $tax = $this->config[$postType]['taxonomy'][$args['taxonomy']];
            if (!$this->exists('type', $tax)) {
                $tax['type'] = 'checkbox';
            }
            $walker = null;
            switch ($tax['type']) {
                case 'radio':
                    require_once getPath('wp-style') . 'includes/walker/WalkerCategoryRadio.php';
                    $walker = new WalkerCategoryRadio();
                    break;
                case 'checkbox':
                default:
                    break;
            }
            if (!is_null($walker)) {
                $args['walker'] = $walker;
            }
        }
        return $args;
    }

    public function addFieldsTaxonomy($taxonomy_slug)
    {
        $objTaxonomy = get_taxonomy($taxonomy_slug);
        $items = $this->config[$objTaxonomy->object_type[0]]['taxonomy'][$taxonomy_slug]['metabox'];
        foreach ($items as $name => $value) {
            switch ($value[1]) {
                case 'text':
                    ?>
                        <div class="form-field term-<?php echo $name; ?>-wrap">
                            <label for="<?php echo $name; ?>"><?php echo $value[0]; ?></label>
                            <input name="<?php echo $name; ?>" id="<?php echo $name; ?>" type="text" value="" size="40">
                            <?php if ($this->exists(2, $value) && !empty($value[2])) : ?>
                                <p><?php echo $value[2]; ?></p>
                            <?php endif; ?>
                        </div>
                    <?php
                    break;
                case 'textarea':
                    ?>
                        <div class="form-field term-<?php echo $name; ?>-wrap">
                            <label for="<?php echo $name; ?>"><?php echo $value[0]; ?></label>
                            <textarea name="<?php echo $name; ?>" id="<?php echo $name; ?>" class="autosize" rows="5"></textarea>
                            <?php if ($this->exists(2, $value) && !empty($value[2])) : ?>
                                <p><?php echo $value[2]; ?></p>
                            <?php endif; ?>
                        </div>
                    <?php
                    break;
                case 'color':
                    ?>
                        <div class="form-field term-<?php echo $name; ?>-wrap">
                            <label for="<?php echo $name; ?>"><?php echo $value[0]; ?></label>
                            <span class="viewColor">クリックで色指定ウィンドウが<span>開き</span>ます。</span>
                            <input type="hidden" id="<?php echo $name; ?>" class="colorpicker" data-view="false" name="<?php echo $name; ?>" value="" />
                            <?php if ($this->exists(2, $value) && !empty($value[2])) : ?>
                                <p><?php echo $value[2]; ?></p>
                            <?php endif; ?>
                        </div>
                    <?php
                    break;
                case 'image':
                    ?>
                        <div class="form-field term-<?php echo $name; ?>-wrap">
                            <label for="<?php echo $name; ?>"><?php echo $value[0]; ?></label>
                            <div class="uploadImageGroup" data-upload-multi="false">
                                <ul class="uploadImageList" data-upload-name="<?php echo $name; ?>" data-upload-text="false" data-upload-sortable="false">
                                    <li class="noImage">
                                        登録がありません。
                                    </li>
                                </ul>
                                <a class="uploadImageButton button">選択</a>
                            </div>
                            <?php if ($this->exists(2, $value) && !empty($value[2])) : ?>
                                <p><?php echo $value[2]; ?></p>
                            <?php endif; ?>
                        </div>
                    <?php
                    break;
                case 'checkbox':
                    ?>
                        <div class="form-field term-<?php echo $name; ?>-wrap">
                            <label><?php echo $value[0]; ?></label>
                            <?php foreach ($value[4] as $key => $label) : ?>
                                <label><input name="<?php echo $name; ?>[]" type="checkbox" value="<?php esc_attr_e($key); ?>"><?php esc_html_e($label); ?></label>
                            <?php endforeach; ?>
                            <?php if ($this->exists(2, $value) && !empty($value[2])) : ?>
                                <p><?php echo $value[2]; ?></p>
                            <?php endif; ?>
                        </div>
                    <?php
                    break;
                default:
                    break;
            }
        }
    }

    public function editFieldsTaxonomy($term, $taxonomy)
    {
        $objTaxonomy = get_taxonomy($taxonomy);
        $items = $this->config[$objTaxonomy->object_type[0]]['taxonomy'][$term->taxonomy]['metabox'];
        foreach ($items as $name => $setting) {
            switch ($setting[1]) {
                case 'text':
                    $value = get_term_meta($term->term_id, $name, true);
                    ?>
                        <tr class="form-field term-<?php echo $name; ?>-wrap">
                            <th scope="row">
                                <label for="<?php echo $name; ?>"><?php echo $setting[0]; ?></label>
                            </th>
                            <td>
                                <input name="<?php echo $name; ?>" id="<?php echo $name; ?>" type="text" value="<?php echo esc_html($value); ?>" size="40">
                                <?php if ($this->exists(2, $setting) && !empty($setting[2])) : ?>
                                    <p><?php echo $setting[2]; ?></p>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php
                    break;
                case 'textarea':
                    $value = get_term_meta($term->term_id, $name, true);
                    ?>
                        <tr class="form-field term-<?php echo $name; ?>-wrap">
                            <th scope="row">
                                <label for="<?php echo $name; ?>"><?php echo $setting[0]; ?></label>
                            </th>
                            <td>
                                <textarea name="<?php echo $name; ?>" id="<?php echo $name; ?>" class="autosize" rows="5"><?php echo "\n" . esc_html($value); ?></textarea>
                                <?php if ($this->exists(2, $setting) && !empty($setting[2])) : ?>
                                    <p><?php echo $setting[2]; ?></p>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php
                    break;
                case 'color':
                    $value = get_term_meta($term->term_id, $name, true);
                    ?>
                        <tr class="form-field term-<?php echo $name; ?>-wrap">
                            <th scope="row">
                                <label for="<?php echo $name; ?>"><?php echo $setting[0]; ?></label>
                            </th>
                            <td>
                                <span class="viewColor">クリックで色指定ウィンドウが<span>開き</span>ます。</span>
                                <input type="hidden" id="<?php echo $name; ?>" class="colorpicker" data-view="false" name="<?php echo $name; ?>" value="<?php echo esc_html($value); ?>" />
                                <?php if ($this->exists(2, $setting) && !empty($setting[2])) : ?>
                                    <p><?php echo $setting[2]; ?></p>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php
                    break;
                case 'image':
                    $value = get_term_meta($term->term_id, $name, true);
                    ?>
                        <tr class="form-field term-<?php echo $name; ?>-wrap">
                            <th scope="row">
                                <label for="<?php echo $name; ?>"><?php echo $setting[0]; ?></label>
                            </th>
                            <td>
                                <div class="form-field term-<?php echo $name; ?>-wrap">
                                    <div class="uploadImageGroup" data-upload-multi="false">
                                        <ul class="uploadImageList" data-upload-name="<?php echo $name; ?>" data-upload-text="false" data-upload-sortable="false">
                                            <?php $srcList = wp_get_attachment_image_src($value, 'medium'); ?>
                                            <?php if ($srcList && $srcList[0]) : ?>
                                                <li class="image" id="image_<?php echo $value; ?>">
                                                    <div class="imageWrap">
                                                        <a class="removeImageButton dashicons dashicons-dismiss"></a>
                                                        <div><img src="<?php echo $srcList[0]; ?>" class="sortHandle"></div>
                                                        <input type="hidden" name="<?php echo $name; ?>" value="<?php echo $value; ?>">
                                                    </div>
                                                </li>
                                            <?php else : ?>
                                                <li class="noImage">
                                                    登録がありません。
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                        <a class="uploadImageButton button">選択</a>
                                    </div>
                                    <?php if ($this->exists(2, $setting) && !empty($setting[2])) : ?>
                                        <p><?php echo $setting[2]; ?></p>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php
                    break;
                case 'checkbox':
                    $value = get_term_meta($term->term_id, $name);
                    ?>
                        <tr class="form-field term-<?php echo $name; ?>-wrap">
                            <th scope="row">
                                <label><?php echo $setting[0]; ?></label>
                            </th>
                            <td>
                                <ul style="margin: 0;">
                                    <?php foreach ($setting[4] as $key => $label) : ?>
                                        <li><label><input name="<?php echo $name; ?>[]" type="checkbox" value="<?php esc_attr_e($key); ?>"<?php checked(in_array($key, $value, true), true); ?>><?php esc_html_e($label); ?></label></li>
                                    <?php endforeach; ?>
                                </ul>
                                <?php if ($this->exists(2, $setting) && !empty($setting[2])) : ?>
                                    <p><?php echo $setting[2]; ?></p>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php
                    break;
                default:
                    break;
            }
        }
    }

    public function saveFieldsTaxonomy($term_id, $tt_id)
    {
        $themeRefactoring = ThemeRefactoring::getInstance();
        $term = $themeRefactoring->getTermByTermTaxonomyId($tt_id);
        $objTaxonomy = get_taxonomy($term->taxonomy);
        $items = $this->config[$objTaxonomy->object_type[0]]['taxonomy'][$term->taxonomy]['metabox'];
        foreach ($items as $name => $setting) {
            if (!$this->exists($name, $_POST)) {
                delete_term_meta($term_id, $name);
                continue;
            }

            $isSanitize = false;
            if (!$this->exists(3, $setting) || !$setting[3]) {
                $isSanitize = true;
            }

            $value = $_POST[$name];
            if (is_array($value)) {
                delete_term_meta($term_id, $name);
                foreach ($value as $val) {
                    if ($isSanitize) {
                        $val = sanitize_text_field($val);
                    }
                    if (strlen($val)) {
                        add_term_meta($term_id, $name, $val);
                    }
                }
            } else {
                if ($isSanitize) {
                    $value = sanitize_text_field($value);
                }
                if (strlen($value)) {
                    update_term_meta($term_id, $name, $value);
                } else {
                    delete_term_meta($term_id, $name);
                }
            }
        }
    }

    public function manageEditTaxonomyColumns($columns)
    {
        global $taxonomy;

        $objTaxonomy = get_taxonomy($taxonomy);
        $items = $this->config[$objTaxonomy->object_type[0]]['taxonomy'][$taxonomy]['metabox'];
        foreach ($items as $name => $value) {
            $columns[$name] = $value[0];
        }

        return $columns;
    }

    public function manageTaxonomyCustomColumn($dummy, $column_name, $term_id)
    {
        global $taxonomy;

        $objTaxonomy = get_taxonomy($taxonomy);
        $items = $this->config[$objTaxonomy->object_type[0]]['taxonomy'][$taxonomy]['metabox'];
        if ($this->exists($column_name, $items)) {
            switch ($items[$column_name][1]) {
                case 'text':
                    $value = get_term_meta($term_id, $column_name, true);
                    return esc_html($value);
                    break;
                case 'textarea':
                    $value = get_term_meta($term_id, $column_name, true);
                    return nl2br(esc_html($value));
                    break;
                case 'color':
                    $value = get_term_meta($term_id, $column_name, true);
                    return '<span class="listColorColumn" style="background-color:' . esc_html($value) . ';">' . esc_html($value) . '</span>';
                    break;
                case 'image':
                    $value = get_term_meta($term_id, $column_name, true);
                    $srcList = wp_get_attachment_image_src($value, 'thumbnail');
                    if ($srcList && $srcList[0]) {
                        return '<img src="' . $srcList[0] . '">';
                    }
                    break;
                case 'checkbox':
                    $value = get_term_meta($term_id, $column_name);
                    $ret = '<ul>';
                    foreach ($value as $val) {
                        $ret .= '<li>' . esc_html($items[$column_name][4][$val]) . '</li>';
                    }
                    $ret .= '<ul>';
                    return $ret;
                    break;
                default:
                    break;
            }
        }
    }

    public function getArchivesLink($link_html)
    {
        if (preg_match('/\?post_type=([^\']+)\'/', $link_html, $m)) {
            if ('post' !== $m[1]) {
                $link_html = str_replace(
                    array(home_url('/'), '?post_type=' . $m[1]),
                    array(home_url('/') . $m[1] . '/', ''),
                    $link_html
                );
            }
        }

        return $link_html;
    }

    public function getDescription()
    {
        $description = '';
        if (is_front_page() || is_home()) {
            $description = get_bloginfo('description');
        } elseif (is_post_type_archive()) {
            $objQuery = get_queried_object();
            if ($this->exists($objQuery->name, $this->config)
                && $this->exists('description', $this->config[$objQuery->name], false)
            ) {
                $description = $this->config[$objQuery->name]['description'];
            }
            if (!$description) {
                $description = $objQuery->label;
            }
        } elseif (is_singular() && !is_attachment()) {
            $description = getTheField('description');
            if (!$description) {
                $description = get_the_title();
            }
        } elseif (is_search()) {
        } elseif (is_tag()) {
        } elseif (is_404()) {
        } elseif (is_tax()) {
        } elseif (is_category()) {
        } elseif (is_author()) {
        } elseif (is_attachment()) {
        }

        return $description;
    }

}
