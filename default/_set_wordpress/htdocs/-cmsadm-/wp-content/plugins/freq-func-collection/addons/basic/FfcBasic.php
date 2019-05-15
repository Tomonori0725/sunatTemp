<?php

if (!class_exists('FfcBasic')) :

/**
 * 基本設定クラス.
 * WordPressの基本的な設定を行う。
 */
class FfcBasic extends FfcBaseClass
{

    const CONFIG_NAME = 'basic';
    const DIRECTORY_NAME = 'basic';
    protected $config = array();

    /**
     * 初期化する.
     *
     * @access protected
     * @return void
     */
    protected function initialize()
    {
        // configの基本設定を読み込む
        $this->config = $this->main->getConfig(self::CONFIG_NAME);
        // 設定がなければ何もしない
        if (empty($this->config)) {
            return;
        }

        if ($this->exists('remove', $this->config)
            && is_array($this->config['remove'])
        ) {
            $this->remove();
        }
        $this->addAction();
        $this->addFilter();
    }

    /**
     *  デフォルトフックハンドラの削除を行う.
     *
     * @access protected
     * @return void
     */
    public function remove()
    {
        $this->removeHead();
        $this->removeAction();
        $this->removeFilter();
    }

    /**
     * head内の不要な要素を削除する.
     *
     * @access protected
     * @return void
     */
    protected function removeHead()
    {
        // wp_headから削除する
        if ($this->exists('head', $this->config['remove'])
            && is_array($this->config['remove']['head'])
        ) {
            $config = $this->config['remove']['head'];

            // basic が設定されていれば基本系で不要と思われるものを削除する
            if ($this->exists('basic', $config)
                && $config['basic']
            ) {
                // WordPressのバージョン情報
                remove_action('wp_head', 'wp_generator');
                // デフォルトパーマリンクのURL
                remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
                // ページネーション
                remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
            }

            // external が設定されていれば基本系で不要と思われるものを削除する
            if ($this->exists('external', $config)
                && $config['external']
            ) {
                // 外部ツールを使ったブログ更新用のURL
                remove_action('wp_head', 'rsd_link');
                // Windows Live Writerを使った記事投稿URL
                remove_action('wp_head', 'wlwmanifest_link');
            }

            // feed が設定されていればフィード関連を削除する
            if ($this->exists('feed', $config)
                && $config['feed']
            ) {
                remove_action('wp_head', 'feed_links', 2);
                remove_action('wp_head', 'feed_links_extra', 3);
            }

            // emoji が設定されていれば絵文字関連を削除する
            if ($this->exists('emoji', $config)
                && $config['emoji']
            ) {
                remove_action('wp_head', 'print_emoji_detection_script', 7);
                remove_action('admin_print_scripts', 'print_emoji_detection_script');
                remove_action('embed_head', 'print_emoji_detection_script');
                remove_action('wp_print_styles', 'print_emoji_styles');
                remove_action('admin_print_styles', 'print_emoji_styles');
            }

            // rest が設定されていればREST API関連を削除する
            if ($this->exists('rest', $config)
                && $config['rest']
            ) {
                remove_action('wp_head', 'rest_output_link_wp_head', 10, 0);
                remove_action('wp_head', 'wp_oembed_add_discovery_links');
                remove_action('wp_head', 'wp_oembed_add_host_js');
                remove_action('xmlrpc_rsd_apis', 'rest_output_rsd');
                remove_action('template_redirect', 'rest_output_link_header', 11, 0);
            }

            // dns-prefetch が設定されていれば「dns-prefetch」を削除する
            if ($this->exists('dns-prefetch', $config)
                && $config['dns-prefetch']
            ) {
                add_filter('wp_resource_hints', array($this, 'removeDnsPrefetch'), 10, 2);
            }
        }
    }

    public function removeDnsPrefetch($hints, $relation_type)
    {
        if ('dns-prefetch' !== $relation_type) {
            return $hints;
        }

        return array_diff(wp_dependencies_unique_hosts(), $hints);
    }

    /**
     * remove_actionを行う.
     *
     * @access protected
     * @return void
     */
    protected function removeAction()
    {
        if (is_admin()) {
            // welcome が設定されていれば「ようこそ」を削除する
            if ($this->exists('welcome', $this->config['remove'])
                && $this->config['remove']['welcome']
            ) {
                remove_action('welcome_panel', 'wp_welcome_panel');
            }
        }
    }

    /**
     * remove_filterを行う.
     *
     * @access protected
     * @return void
     */
    protected function removeFilter()
    {
    }

    /**
     * add_actionを行う.
     *
     * @access protected
     * @return void
     */
    protected function addAction()
    {
        add_action('init', array($this, 'settingTheme'));

        if ($this->exists('redirect', $this->config)) {
            add_action('parse_query', array($this, 'templateRedirect'));
        }

        if ($this->exists('adminbar', $this->config)
            && is_array($this->config['adminbar'])
        ) {
            // 管理バー設定
            add_action('admin_bar_menu', array($this, 'adminBarMenu'), 99);
        }

        if (is_admin()) {
            if ($this->exists('dashboard', $this->config)
                && is_array($this->config['dashboard'])
            ) {
                // ダッシュボード設定
                add_action('wp_dashboard_setup', array($this, 'wpDashboardSetup'));
            }
        }
    }

    /**
     * add_filterを行う.
     *
     * @access protected
     * @return void
     */
    protected function addFilter()
    {
        if ($this->exists('query-vars', $this->config)
            && is_array($this->config['query-vars'])
        ) {
            add_filter('query_vars', array($this, 'queryVars'));
        }
    }

    /**
     * 基本設定を行う.
     *
     * @access public
     * @return void
     */
    public function settingTheme()
    {
        if ($this->exists('feed', $this->config)
            && $this->config['feed']
        ) {
            // フィードの設定がされていればwp_headにフィードURLを出力する
            add_theme_support('automatic-feed-links');
        }

        if ($this->exists('menu', $this->config)
            && is_array($this->config['menu'])
        ) {
            // カスタムメニューの設定がされていれば登録する
            add_theme_support('menus');
            register_nav_menus($this->config['menu']);
        }
    }

    /**
     * 管理バーの設定を行う.
     *
     * @param WP_Admin_Bar $wp_admin_bar 管理バークラスインスタンス
     *
     * @access public
     * @return void
     */
    public function adminBarMenu($wp_admin_bar)
    {
        // logo が設定されていればWordPressのロゴを削除する
        if ($this->exists('logo', $this->config['adminbar'])
            && $this->config['adminbar']['logo']
        ) {
            $wp_admin_bar->remove_node('wp-logo');
        }
    }

    /**
     * ダッシュボードの設定を行う.
     *
     * @access public
     * @return void
     */
    public function wpDashboardSetup()
    {
        // default が設定されていればデフォルトのボックスを削除する
        if ($this->exists('default', $this->config['dashboard'])
            && $this->config['dashboard']['default']
        ) {
            remove_meta_box('dashboard_right_now', 'dashboard', 'normal');
            remove_meta_box('dashboard_activity', 'dashboard', 'normal');
            remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
            remove_meta_box('dashboard_primary', 'dashboard', 'side');
        }
    }

    public function templateRedirect()
    {
        if (is_author()) {
            if ($this->exists('author', $this->config['redirect'])
                && $this->config['redirect']['author']
            ) {
                global $wp_query;
                $wp_query->set_404();
                status_header(404);
                nocache_headers();
            }
        }
    }

    /**
     * クエリ変数に追加する.
     *
     * @param array $vars クエリ変数配列
     *
     * @access public
     * @return void
     */
    public function queryVars($vars)
    {
        $vars = array_merge($vars, array_values($this->config['query-vars']));

        return $vars;
    }

}

endif;
