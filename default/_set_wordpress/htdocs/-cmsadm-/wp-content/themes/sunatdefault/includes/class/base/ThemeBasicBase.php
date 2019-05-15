<?php

abstract class ThemeBasicBase extends ThemeClassBase
{

    protected $config = array();

    protected function initialize()
    {
        $config = $this->getConfig();
        if (!$this->exists('basic', $config)
            || !is_array($config['basic'])
        ) {
            return;
        }
        $this->config = $config['basic'];

        $this->removeHead();
        $this->addAction();
        $this->addFilter();
    }

    // head内の不要なタグを削除する
    protected function removeHead()
    {
        // WordPressのバージョン情報
        remove_action('wp_head', 'wp_generator');
        // 外部ツールを使ったブログ更新用のURL
        remove_action('wp_head', 'rsd_link');
        // wlwmanifestWindows Live Writerを使った記事投稿URL
        remove_action('wp_head', 'wlwmanifest_link');
        // デフォルトパーマリンクのURL
        remove_action('wp_head', 'wp_shortlink_wp_head');
        // ページネーション
        remove_action('wp_head', 'index_rel_link');
        remove_action('wp_head', 'parent_post_rel_link', 10, 0);
        remove_action('wp_head', 'start_post_rel_link', 10, 0);
        remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
    }

    // アクションフック
    protected function addAction()
    {
        add_action('init', array($this, 'removeAction'), 99);
        add_action('after_setup_theme', array($this, 'afterSetupTheme'));
        add_action('wp_dashboard_setup', array($this, 'wpDashboardSetup'));
        add_action('admin_bar_menu', array($this, 'adminBarMenu'), 99);
        add_action('wp_head', array($this, 'printOgp'));
        add_action('mwform_after_exec_shortcode', array($this, 'mwformAfterExecShortcode'));
    }

    // フィルターフック
    protected function addFilter()
    {
        if ($this->exists('excerpt', $this->config)) {
            if ($this->exists('length', $this->config['excerpt'], false)) {
                add_filter('excerpt_mblength', array($this, 'excerptMblength'));
            }
            if ($this->exists('more', $this->config['excerpt'])) {
                add_filter('excerpt_more', array($this, 'excerptMore'));
            }
        }
        if ($this->exists('query_vars', $this->config)) {
            add_filter('query_vars', array($this, 'queryVars'));
        }
    }

    // アクションフック削除
    public function removeAction()
    {
        // ようこそパネルを削除する
        remove_action('welcome_panel', 'wp_welcome_panel');
        // ログイン画面へのリダイレクトを禁止する
        remove_action('template_redirect', 'wp_redirect_admin_locations', 1000);
    }

    public function afterSetupTheme()
    {
        // titleタグ出力を有効にする
        add_theme_support('title-tag');
        // head内にRSSフィードのURLを追加する
        add_theme_support('automatic-feed-links');

        // カスタムメニューを登録する
        if ($this->exists('menu', $this->config)) {
            add_theme_support('menus');
            register_nav_menus($this->config['menu']);
        }
    }

    public function wpDashboardSetup()
    {
        // ダッシュボードの不要なパネルを削除する
        global $wp_meta_boxes;
        unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);
        unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_activity']);
        unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);
        unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
    }

    public function adminBarMenu($wp_admin_bar)
    {
        // 管理バーからWordPressロゴを削除する
        $wp_admin_bar->remove_node('wp-logo');
    }

    // 本文からの抜粋の長さを変更する
    public function excerptMblength($length) {
        return $this->config['excerpt']['length'];
    }

    // 本文を省略する際に付加する文字列を変更する
    public function excerptMore($more) {
        // 省略文字が設定されていればそれに置き換える
        if ($this->exists('text', $this->config['excerpt']['more'])) {
            $more = $this->config['excerpt']['more']['text'];
        }
        // 設定されていれば記事ページへのリンクを付加する
        if ($this->exists('link', $this->config['excerpt']['more'])
            && $this->exists('text', $this->config['excerpt']['more']['link'])
        ) {
            $class = '';
            if ($this->exists('class', $this->config['excerpt']['more']['link'])) {
                $class = implode(' ', $this->config['excerpt']['more']['link']['class']);
                $class = ' class="' . $class . '"';
            }
            $more .= '<a href="' . get_the_permalink() . '"' . $class . '>' . $this->config['excerpt']['more']['link']['text'] . '</a>';
        }

        return $more;
    }

    // クエリ変数に追加する
    public function queryVars($vars)
    {
        if (is_array($this->config['query_vars'])) {
            $vars = array_merge($vars, $this->config['query_vars']);
        }

        return $vars;
    }

    // OGPタグを出力する
    public function printOgp()
    {
        // トップページ、投稿ページ(記事ページ、固定ページ)のみとする
        if (!is_front_page() && !is_home() && !is_singular()) {
            return;
        }

        $arrOgp = array(
            'title'       => '',
            'description' => '',
            'url'         => '',
            'type'        => '',
            'image'       => '',
            'site_name'   => get_bloginfo('name'),
            'locale'      => 'ja_JP'
        );

        if (is_singular()) {
            // 投稿ページなら投稿の内容を使用する
            global $post;
            setup_postdata($post);
            $arrOgp['title'] = get_the_title();
            $arrOgp['description'] = mb_substr(get_the_excerpt(), 0, 100);
            $arrOgp['url'] = get_permalink();
            wp_reset_postdata();
            $arrOgp['type'] = 'article';
        } else {
            // トップページならサイト情報を使用する
            $arrOgp['title'] = get_bloginfo('name');
            $arrOgp['description'] = get_bloginfo('description');
            $arrOgp['url'] = getUrl('site-top');
            $arrOgp['type'] = 'website';
        }

        // アイキャッチ画像のURL
        $arrOgp['image'] = getEyecatchUrl();
        if (!strlen($arrOgp['image'])) {
            // OGPに画像は必須である
            return;
        }

        // OGPタグを出力する
        foreach ($arrOgp as $property => $ogp) {
            echo '<meta property="og:' . $property . '" content="' . $ogp . '">' . "\n";
        }

        // 設定されていればTwitter, Facebookの専用OGPを出力する
        if (!$this->exists('ogp', $this->config)) {
            return;
        }
        $config = $this->config['ogp'];

        $card = 'summary_large_image';
        if ($this->exists('twitter', $config)) {
            // カードサイズ
            if ($this->exists('card', $config['twitter'], false)) {
                $card = $config['twitter']['card'];
            }

            // アカウント
            if ($this->exists('site', $config['twitter'], false)) {
                $site = $config['twitter']['site'];
                if ('@' !== $site[0]) {
                    $site = '@' . $site;
                }
                echo '<meta name="twitter:site" content="' . $site . '">' . "\n";
            }
        }
        echo '<meta name="twitter:card" content="' . $card . '">' . "\n";

        if ($this->exists('facebook', $config)) {
            if ($this->exists('appid', $config['facebook'], false)) {
                echo '<meta property="fb:app_id" content="' . $config['facebook']['appid'] . '">' . "\n";
            }
        }
    }

    // MW WP Form設定用関数を登録する
    public function mwformAfterExecShortcode($formKey)
    {
        if (is_null($formKey)) {
            return;
        }

        // $formKey(mw-wp-form-xxx)からフォームの投稿ID(xxx部分)を取り出し投稿スラッグを取得する
        $id = str_replace('mw-wp-form-', '', $formKey);
        $slug = get_post_field('post_name', $id);

        // 当該フォームのラジオボタン、チェックボックス、セレクトボックスの選択項目を変更する関数が存在すればフックする
        if (function_exists('setChoiceItem_' . $slug)) {
            add_filter('mwform_choices_' . $formKey, 'setChoiceItem_' . $slug, 10, 2);
        }
        // 当該フォームのバリデーションを追加する関数が存在すればフックする
        if (function_exists('setEventValidationRule_' . $slug)) {
            add_filter('mwform_validation_' . $formKey, 'setEventValidationRule_' . $slug, 10, 2);
        }
    }

}
