<?php

abstract class ThemeRefactoringBase extends ThemeClassBase
{

    protected $config = array();

    protected function initialize()
    {
        $this->config = $this->getConfig();

        $this->addAction();
        $this->addFilter();
        $this->addShortcode();
    }

    // アクションフック
    protected function addAction()
    {
        add_action('init', array($this, 'removeHooks'), 100);
        add_action('wp_list_categories', array($this, 'wpListCategories'), 10, 2);
        add_action('get_archives_link', array($this, 'getArchivesLink'));
//        add_action('create_term', array($this, 'uniquTermSlug'), 10, 3);
//        add_action('edit_term', array($this, 'uniquTermSlug'), 10, 3);
    }

    // フィルターフック
    protected function addFilter()
    {
        add_filter('wp_nav_menu_objects', array($this, 'wpNavMenuObjectsAction'), 10, 2);
        add_filter('tiny_mce_before_init', array($this, 'tinyMceBeforeInit'), 100);
//        add_filter('wp_unique_post_slug', array($this, 'wpUniquePostSlug'), 10, 4);
        add_filter('wp_trim_words', array($this, 'wpTrimWords'), 100, 4);
        add_filter('body_class', array($this, 'bodyClass'));
        add_filter('upload_dir', array($this, 'changeUploadDir'));

        add_filter('nav_menu_css_class', array($this, 'navMenuCssClass'), 100, 2);
        add_filter('nav_menu_item_id', array($this, 'navMenuItemId'), 100, 1);
        add_filter('page_css_class', array($this, 'pageCssClass'), 100, 1);

        add_filter('private_title_format', array($this, 'deletePrivateFormat'));
    }

    // フック削除
    public function removeHooks()
    {
        remove_filter('the_content', 'wpautop');
        remove_filter('the_content', 'wptexturize');
        remove_filter('the_excerpt', 'wpautop');
        remove_filter('the_excerpt', 'wptexturize');
    }

    public function navMenuItemId($id)
    {
        // idは削除
        return '';
    }

    public function pageCssClass($classes)
    {
        return array();
    }

    public function navMenuCssClass($classes, $item)
    {
        foreach ($classes as $key => $class) {
            // 不要なclassは削除する
            if (false !== strpos($class, 'menu-item')
                || false !== strpos($class, 'current_')
                || false !== strpos($class, 'page-')
                || false !== strpos($class, 'page_')
            ) {
                unset($classes[$key]);
            }
        }
        if ($item->current || $item->current_item_ancestor || $item->current_item_parent) {
            // 自分が、現在のページもしくは現在のページの先祖・親ならactiveとする
            $classes[] = 'active';
        } elseif ('post_type_archive' === $item->type) {
            if (is_post_type_archive()
                || is_tax()
                || is_category()
                || is_tag()
                || is_date()
                || is_single()
            ) {
                // 現在のページが、標準の投稿もしくはカスタム投稿のトップ・タクソノミー(カテゴリー)・個別ページなら
                // カスタム投稿タイプ名が一致すればactiveとする
                $postType = get_post_type_object(getPosttype());
                if ($postType->name === $item->object) {
                    $classes[] = 'active';
                }
            }
        }
        return $classes;
    }

    // ショートコード追加
    protected function addShortcode()
    {
        add_shortcode('site_url', array($this, 'shortcodeSiteUrl'));
        add_shortcode('theme_url', array($this, 'shortcodeThemeUrl'));
    }

    // カテゴリーリストの記事数をaタグの中に入れる
    public function wpListCategories($html)
    {
        return preg_replace('/<\/a>\s(\([0-9]*\))/', '&nbsp;<span class="count">$1</span></a>', $html);
    }

    // アーカイブリストの記事数をaタグの中に入れる
    public function getArchivesLink($html)
    {
        return preg_replace('/<\/a>&nbsp;(\([0-9]*\))/', '&nbsp;<span class="count">$1</span></a>', $html);
    }

    public function uniquTermSlug($term_id, $tt_id, $taxonomy)
    {
        $term = get_term($term_id, $taxonomy);

        if (preg_match('/(%[0-9a-f]{2})+/', $term->slug)) {
            $slug = utf8_uri_encode($taxonomy) . '-' . $term_id;
            global $wpdb;
            $wpdb->update($wpdb->terms, compact('slug'), compact('term_id'));
        }
    }

    public function wpNavMenuObjectsAction($menu, $args)
    {
        $no = 1;
        foreach ($menu as $key => $item) {
            if ($item->menu_item_parent == 0) {
                $item->classes[] = 'menu' . sprintf('%02d', $no);
                if ($no == 1) {
                    $item->classes[] = 'first';
                }
                if ($no == count($menu)) {
                    $item->classes[] = 'last';
                }
                $no++;
            }
        }
        return $menu;
    }

    // ビジュアルエディター振る舞い改善
    public function tinyMceBeforeInit($init_array)
    {
        global $allowedposttags;
        $init_array['valid_elements']          = '*[*]';
        $init_array['extended_valid_elements'] = '*[*]';
        $init_array['valid_children']          = '+a[' . implode('|', array_keys($allowedposttags)) . ']';
        $init_array['element_format']          = 'html';
        $init_array['indent']                  = true;
        $init_array['wpautop']                 = false;
        $init_array['forced_root_block']       = false;
        $init_array['keep_styles']             = true;
        $init_array['remove_trailing_brs']     = false;

        return $init_array;
    }

    // スラッグに日本語が入っていれば [ポストタイプ]-[ポストID] に置き換える
    public function wpUniquePostSlug($slug, $post_ID, $post_status, $post_type)
    {
        if (preg_match('/(%[0-9a-f]{2})+/', $slug) && $post_ID) {
            $slug = utf8_uri_encode($post_type) . '-' . $post_ID;
        }
        return $slug;
    }

    // 許可されたタグを考慮して本文から抜粋する
    public function wpTrimWords($text, $num_words, $more, $original_text)
    {
        $text = $original_text;
        $allowed_tags = '';
        $twopart_tags = '';
        if (array_key_exists('basic', $this->config) && !empty($this->config['basic'])) {
            if (array_key_exists('excerpt', $this->config['basic']) && !empty($this->config['basic']['excerpt'])) {
                if (array_key_exists('allow', $this->config['basic']['excerpt']) && !empty($this->config['basic']['excerpt']['allow'])) {
                    $allowed_tags = '<' . implode('>,<', $this->config['basic']['excerpt']['allow']) . '>';
                }
                if (array_key_exists('pair', $this->config['basic']['excerpt']) && !empty($this->config['basic']['excerpt']['pair'])) {
                    $twopart_tags = '<' . implode('>,<', $this->config['basic']['excerpt']['pair']) . '>';
                }
            }
        }
        $search_patterns = '/' . str_replace(',', '|', str_replace(array('<', '>'), array('<\/?', '[^>]*>'), $twopart_tags)) . '/';
        $text = strip_tags($text, $allowed_tags);

//        if ('characters' == _x('words', 'word count: words or characters?') && preg_match('/^utf\-?8$/i', get_option('blog_charset'))) {
        if (preg_match('/^utf\-?8$/i', get_option('blog_charset'))) {
            $text = trim(preg_replace('/[\n\r\t ]+/', ' ', $text), ' ');
            preg_match_all('/./u', $text, $words_array);
            $words_array = array_slice($words_array[0], 0, $num_words + 1);
            $sep = '';
        } else {
            $words_array = preg_split('/[\n\r\t ]+/', $text, $num_words + 1, PREG_SPLIT_NO_EMPTY);
            $sep = ' ';
        }
        if (count($words_array) > $num_words) {
            $text = implode($sep, $words_array);
        } else {
            $text = implode($sep, $words_array);
        }

        $text = preg_replace('/<[^>]*$/', '', $text);

        preg_match_all ($search_patterns, $text, $matches);
        $tagsfound = $matches[0];

        $tagstack = array();
        while (count($tagsfound) > 0) {
            $tagmatch = array_shift($tagsfound);
            if (!strpos($tagmatch,'/')) {
                $endtag = str_replace('<', '</', $tagmatch);
                if ($key = array_search($endtag, $tagsfound)) {
                    unset($tagsfound[$key]);
                } else {
                    array_push($tagstack, $endtag);
                }
            }
        }

        while (count ($tagstack) > 0) {
            $text = $text . array_pop($tagstack);
        }

        if (count($words_array) > $num_words) {
            $text = $text . $more;
        }

        return $text;
    }

    public function deletePrivateFormat()
    {
        return '%s';
    }

    // bodyのclassを設定する
    public function bodyClass($classes)
    {
        global $post;

        // ページの識別用
        if (is_front_page()) {
            $class = 'index';
        } elseif (is_search()) {
            $class = 'search';
        } elseif (is_tag()) {
            $class = 'tag';
        } elseif (is_404()) {
            $class = 'error';
        } elseif (is_author()) {
            $class = 'author';
        } elseif (is_attachment()) {
            $class = 'attachment';
        } elseif (is_page()) {
            if ($post->post_parent != 0) {
                $ancestors = array_reverse(get_post_ancestors($post->ID));
                $ancestor = get_post(array_shift($ancestors));
                $class = $ancestor->post_name;
            } else {
                $class = $post->post_name;
            }
        } else {
            $class = getPostType();
            if (!strlen($class)) {
                if (is_post_type_archive()) {
                    $class = get_query_var('post_type');
                } elseif (is_tax()) {
                    $taxonomy = get_taxonomy(get_query_var('taxonomy'));
                    $class = $taxonomy->object_type[0];
                } elseif (is_category()) {
                    $class = 'post';
                } elseif (is_date()) {
                    $class = 'post';
                } elseif (is_archive()) {
                    $class = 'post';
                } else {
                    $class = '';
                }
            }
        }

        if ($class) {
            $classes[] = 'page' . esc_attr(ucfirst($class));
        }

        // ページの種別用
        if (is_post_type_archive()
            || is_tax()
            || is_category()
            || is_archive()
            || is_search()
            || is_tag()
            || is_date()
        ) {
            $classes[] = 'index';
            $classes[] = 'list';
        } elseif (is_page()) {
            if ($post->post_parent != 0) {
                $classes[] = $post->post_name;
            } else {
                $classes[] = 'index';
            }
        } elseif (is_single()) {
            $classes[] = 'entry';
            $classes[] = 'detail';
        }

        return $classes;
    }

    // 画像アップロードディレクトリーを Y/m/d/[post_id] へ変更する
    // post_id がない場合は Y/m/d
    public function changeUploadDir($path)
    {
        $addPath = '/' . date_i18n('d');
        if (is_admin()
            && array_key_exists('post_id', $_REQUEST)
            && is_numeric($_REQUEST['post_id'])
            && $_REQUEST['post_id'] > 0
        ) {
            $addPath .= '/' . $_REQUEST['post_id'];
        }
        $path['path'] .= $addPath;
        $path['url'] .= $addPath;
        $path['subdir'] .= $addPath;
        return $path;
    }

    public function shortcodeSiteUrl()
    {
        return getUrl('site-top');
    }

    public function shortcodeThemeUrl()
    {
        return getUrl('wp-style');
    }

    public function getTermByTermTaxonomyId($term_taxonomy_id)
    {
        $taxonomies = get_taxonomies();
        foreach ($taxonomies as $taxonomy) {
            $term = get_term_by('term_taxonomy_id', $term_taxonomy_id, $taxonomy);
            if ($term) {
                return $term;
            }
        }
        return null;
    }

    // 各種URLを取得
    public function getUrl($type)
    {
        switch ($type) {
            case 'site-top':    // サイトにアクセスするためのURL
                $ret = home_url();
                break;
            case 'wp-top':        // WordPressがインストールされているURL
                $ret = site_url();
                break;
            case 'wp-content':
                $ret = trailingslashit(site_url()) . 'wp-content';
                break;
            case 'wp-theme':
                $ret = get_template_directory_uri();
                break;
            case 'wp-style':
                $ret = get_stylesheet_directory_uri();
                break;
            default:
                break;
        }
        return untrailingslashit($ret);
    }

    // 各種パスを取得
    public function getPath($type)
    {
        switch ($type) {
            case 'wp-top':
                $ret = ABSPATH;
                break;
            case 'wp-content':
                $ret = WP_CONTENT_DIR;
                break;
            case 'wp-theme':
                $ret = get_template_directory();
                break;
            case 'wp-style':
                $ret = get_stylesheet_directory();
                break;
            default:
                break;
        }
        return trailingslashit($ret);
    }

    public function getPostType()
    {
        if (is_tax()) {
            $taxonomy = get_query_var('taxonomy');
            $posttype = get_taxonomy($taxonomy)->object_type[0];
        } elseif (is_post_type_archive()) {
            $posttype = get_query_var('post_type');
        } elseif (is_singular() || is_home() || is_archive()) {
            $posttype = get_post_type();
        } else {
            $posttype = '';
        }

        return $posttype;
    }

    public function getEyecatchUrl()
    {
        $image = '';
        if (has_post_thumbnail()) {
            $imageId = get_post_thumbnail_id();
            $image = wp_get_attachment_image_src($imageId, 'full');
            $image = $image[0];
        } elseif (preg_match('/<img.*?src=(["\'])(.+?)\1.*?>/i', get_the_content(), $match)) {
            $image = $match[2];
        } else {
            $posttype = getPostType();
            if ($this->exists($posttype, $this->config['posttype'])
                && $this->exists('eyecatch', $this->config['posttype'][$posttype])
                && strlen($this->config['posttype'][$posttype]['eyecatch'])
            ) {
                $imageId = getTheField($this->config['posttype'][$posttype]['eyecatch']);
                if ($imageId) {
                    $image = wp_get_attachment_image_src($imageId[0], 'full');
                    $image = $image[0];
                }
            }
        }
        if (!$image && getConst('DEFAULT_IMAGE')) {
            $image = getUrl('wp-style') . '/' . getConst('DEFAULT_IMAGE');
        }

        return $image;
    }

    public function getTopicpath($params = array())
    {
        if (is_front_page()) {
            return array();
        }

        $defaults = array(
            'home'     => 'ホーム',
            'posttype' => true,
            'search'   => '検索結果',
            'taxonomy' => array('category')
        );
        $params = wp_parse_args($params, $defaults);

        global $post;
        $topicpathList = array();

        if (is_search()) {
            $topicpath = array();
            if (false !== $params['home']) {
                $topicpath[] = array('label' => $params['home'], 'link' => getUrl('site-top'));
            }
            $topicpath[] = array('label' => $params['search']);
            $topicpathList[] = $topicpath;
        } elseif (is_tag()) {
            $topicpath = array();
            if (false !== $params['home']) {
                $topicpath[] = array('label' => $params['home'], 'link' => getUrl('site-top'));
            }
            $topicpath[] = array('label' => 'タグ：' . single_tag_title('' , false));
            $topicpathList[] = $topicpath;
        } elseif (is_404()) {
            $topicpath = array();
            if (false !== $params['home']) {
                $topicpath[] = array('label' => $params['home'], 'link' => getUrl('site-top'));
            }
            $topicpath[] = array('label' => '404 Not found');
            $topicpathList[] = $topicpath;
        } elseif (is_date()) {
            $topicpath = array();
            if (false !== $params['home']) {
                $topicpath[] = array('label' => $params['home'], 'link' => getUrl('site-top'));
            }
            if (false !== $params['posttype']) {
                $postType = get_queried_object();
                if (true === $params['posttype']) {
                    $postTypelabel = $postType->label;
                } else {
                    $postTypelabel = $params['posttype'];
                }
                $topicpath[] = array('label' => $postTypelabel, 'link' => get_post_type_archive_link($postType->name));
            }
            if (is_year()) {
                $title = get_query_var('year') . '年';
            } elseif (is_month()) {
                $title = get_query_var('year') . '年' . get_query_var('month') . '月';
            } else {
                $title = get_query_var('year') . '年' . get_query_var('month') . '月' . get_query_var('day') . '日';
            }
            $topicpath[] = array('label' => $title);
            $topicpathList[] = $topicpath;
        } elseif (is_post_type_archive()) {
            $topicpath = array();
            if (false !== $params['home']) {
                $topicpath[] = array('label' => $params['home'], 'link' => getUrl('site-top'));
            }
            if (false !== $params['posttype']) {
                $postType = get_queried_object();
                if (true === $params['posttype']) {
                    $postTypelabel = $postType->label;
                } else {
                    $postTypelabel = $params['posttype'];
                }
                $topicpath[] = array('label' => $postTypelabel);
            }
            $topicpathList[] = $topicpath;
        } elseif (is_home()) {
            $topicpath = array();
            if (false !== $params['home']) {
                $topicpath[] = array('label' => $params['home'], 'link' => getUrl('site-top'));
            }
            if (false !== $params['posttype']) {
                $postType = get_post_type_object('post');
                if (true === $params['posttype']) {
                    $postTypelabel = $postType->label;
                } else {
                    $postTypelabel = $params['posttype'];
                }
                $topicpath[] = array('label' => $postTypelabel);
            }
            $topicpathList[] = $topicpath;
        } elseif (is_tax()) {
            $topicpath = array();
            if (false !== $params['home']) {
                $topicpath[] = array('label' => $params['home'], 'link' => getUrl('site-top'));
            }
            $objQuery = get_queried_object();
            $taxonomy = get_taxonomy($objQuery->taxonomy);

            if (false !== $params['posttype']) {
                $postType = get_post_type_object($taxonomy->object_type[0]);
                if (true === $params['posttype']) {
                    $postTypelabel = $postType->label;
                } else {
                    $postTypelabel = $params['posttype'];
                }
                $topicpath[] = array('label' => $postTypelabel, 'link' => get_post_type_archive_link($postType->name));
            }

            if (0 !== $objQuery->parent) {
                $ancestors = array_reverse(get_ancestors($objQuery->term_id, $objQuery->taxonomy));
                foreach ($ancestors as $ancestor) {
                    $term = get_term($ancestor);
                    $topicpath[] = array('label' => esc_html($term->name), 'link' => get_term_link($ancestor));
                }
            }
            $label = $objQuery->name;
            $topicpath[] = array('label' => $label);
            $topicpathList[] = $topicpath;
        } elseif (is_category()) {
            $topicpath = array();
            if (false !== $params['home']) {
                $topicpath[] = array('label' => $params['home'], 'link' => getUrl('site-top'));
            }
            if (false !== $params['posttype']) {
                $postType = get_post_type_object('post');
                if (true === $params['posttype']) {
                    $postTypelabel = $postType->label;
                } else {
                    $postTypelabel = $params['posttype'];
                }
                $topicpath[] = array('label' => $postTypelabel, 'link' => get_post_type_archive_link($postType->name));
            }

            $cat = get_queried_object();
            if ($cat->parent != 0) {
                $ancestors = array_reverse(get_ancestors($cat->cat_ID, 'category'));
                foreach ($ancestors as $ancestor) {
                    $topicpath[] = array('label' => get_cat_name($ancestor), 'link' => get_category_link($ancestor));
                }
            }
            $topicpath[] = array('label' => $cat->name);
            $topicpathList[] = $topicpath;
        } elseif (is_author()) {
            $topicpath = array();
            if (false !== $params['home']) {
                $topicpath[] = array('label' => $params['home'], 'link' => getUrl('site-top'));
            }
            $topicpath[] = array('label' => '投稿者：' . get_the_author_meta('display_name', get_query_var('author')));
            $topicpathList[] = $topicpath;
        } elseif (is_page()) {
            $topicpath = array();
            if (false !== $params['home']) {
                $topicpath[] = array('label' => $params['home'], 'link' => getUrl('site-top'));
            }
            if (0 !== $post->post_parent) {
                foreach ($ancestors as $ancestor) {
                    $topicpath[] = array('label' => get_the_title($ancestor), 'link' => get_permalink($ancestor));
                    $ancestorPost = get_post($ancestor);
                    if ('private' !== $ancestorPost->post_status) {
                        $link = get_permalink($ancestor);
                    } else {
                        // 非公開ならリンクを付けない
                        $link = false;
                    }
                    $topicpath[] = array('label' => get_the_title($ancestor), 'link' => $link);
                }
            }
            $topicpath[] = array('label' => $post->post_title);
            $topicpathList[] = $topicpath;
        } elseif (is_attachment()) {
            $topicpath = array();
            if (false !== $params['home']) {
                $topicpath[] = array('label' => $params['home'], 'link' => getUrl('site-top'));
            }
            if ($post->post_parent != 0) {
                $topicpath[] = array('label' => get_the_title($post->post_parent), 'link' => get_permalink($post->post_parent));
            }
            $topicpath[] = array('label' => $post->post_title);
            $topicpathList[] = $topicpath;
        } elseif (is_single()) {
            if ('post' == get_post_type()) {
                if (in_array('category', $params['taxonomy'], true)) {
                    $categories = get_the_category($post->ID);
                    foreach ($categories as $cat) {
                        $topicpath = array();
                        if (false !== $params['home']) {
                            $topicpath[] = array('label' => $params['home'], 'link' => getUrl('site-top'));
                        }

                        if (false !== $params['posttype']) {
                            $postType = get_post_type_object('post');
                            if (true === $params['posttype']) {
                                $postTypelabel = $postType->label;
                            } else {
                                $postTypelabel = $params['posttype'];
                            }
                            $topicpath[] = array('label' => $postTypelabel, 'link' => get_post_type_archive_link($postType->name));
                        }

                        if ($cat->parent != 0) {
                            $ancestors = array_reverse(get_ancestors($cat->cat_ID, 'category'));
                            foreach ($ancestors as $ancestor) {
                                $topicpath[] = array('label' => get_cat_name($ancestor), 'link' => get_category_link($ancestor));
                            }
                        }
                        $topicpath[] = array('label' => $cat->cat_name, 'link' => get_category_link($cat->term_id));

                        $topicpath[] = array('label' => $post->post_title);

                        $topicpathList[] = $topicpath;
                    }
                }
            } else {
                $taxonomies = get_object_taxonomies($post->post_type, 'objects');
                if (!empty($taxonomies)) {
                    foreach ($taxonomies as $temp) {
                        if (!in_array($temp->name, $params['taxonomy'], true)) {
                            continue;
                        }

                        $taxonomy = null;
                        $taxonomy = $temp;
                        if (!is_null($taxonomy)) {
                            $terms = get_the_terms($post->ID, $taxonomy->name);
                            if (!empty($terms)) {
                                foreach ($terms as $term) {
                                    $topicpath = array();
                                    if (false !== $params['home']) {
                                        $topicpath[] = array('label' => $params['home'], 'link' => getUrl('site-top'));
                                    }
                                    if (false !== $params['posttype']) {
                                        $postType = get_post_type_object($post->post_type);
                                        if (true === $params['posttype']) {
                                            $postTypelabel = $postType->label;
                                        } else {
                                            $postTypelabel = $params['posttype'];
                                        }
                                        $topicpath[] = array('label' => $postTypelabel, 'link' => get_post_type_archive_link($postType->name));
                                    }

                                    $label = $term->name;
                                    $topicpath[] = array('label' => $label, 'link' => get_term_link($term->slug, $taxonomy->name));
                                    $topicpath[] = array('label' => $post->post_title);
                                    $topicpathList[] = $topicpath;
                                }
                            }
                        }
                    }
                } else {
                    $topicpath = array();
                    if (false !== $params['home']) {
                        $topicpath[] = array('label' => $params['home'], 'link' => getUrl('site-top'));
                    }
                    if (false !== $params['posttype']) {
                        $postType = get_post_type_object($post->post_type);
                        if (true === $params['posttype']) {
                            $postTypelabel = $postType->label;
                        } else {
                            $postTypelabel = $params['posttype'];
                        }
                        $topicpath[] = array('label' => $postTypelabel, 'link' => getUrl('site-top') . '/' . $postType->name . '/');
                    }

                    $topicpath[] = array('label' => $post->post_title);
                    $topicpathList[] = $topicpath;
                }
            }
        } else {
            $topicpath = array();
            if (false !== $params['home']) {
                $topicpath[] = array('label' => $params['home'], 'link' => getUrl('site-top'));
            }
            $topicpath[] = array('label' => wp_title('', false));
            $topicpathList[] = $topicpath;
        }
        return $topicpathList;
    }

    public function pagination($range = 4)
    {
        // 現在のページ番号
        global $paged;
        if (empty($paged)) {
            $paged = 1;
        }

        // 総ページ数(=最終ページ番号)
        global $wp_query;
        $pages = (int)$wp_query->max_num_pages;
        if (!$pages) {
            $pages = 1;
        }

        $pagination = array();
        if (1 < $pages) {
            $pagination = array(
                'now'      => $paged,
                'page_num' => $pages,
                'first'    => get_pagenum_link(1),
                'last'     => get_pagenum_link($pages)
            );

            if ($paged > 1) {
                $pagination['prev'] = get_pagenum_link($paged - 1);
            } else {
                $pagination['prev'] = false;
            }
            if ($paged < $pages) {
                $pagination['next'] = get_pagenum_link($paged + 1);
            } else {
                $pagination['next'] = false;
            }

            $pagination['page'] = array();
            for ($page = 1; $page <= $pages; $page++) {
                if (($paged - $range <= $page) && ($page <= $paged + $range)) {
                    $pagination['page'][$page] = get_pagenum_link($page);
                }
            }
        }
        return $pagination;
    }

}
