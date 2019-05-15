<?php

abstract class ThemeLoginBase extends ThemeClassBase
{

    const CSS_PATH = 'assets/css';
    const IMG_PATH = 'assets/img';
    protected $config = array();

    protected function initialize()
    {
        $config = $this->getConfig();
        if (!$this->exists('login', $config)
            || !is_array($config['login'])
        ) {
            return;
        }
        $this->config = $config['login'];

        add_filter('login_headerurl', array($this, 'changeLoginHeaderUrl'));
        add_filter('login_headertitle', array($this, 'changeLoginHeaderTitle'));
        if ($this->exists('url', $this->config, false)) {
            add_action('wp_loaded', array($this, 'wpLoaded'));
            add_filter('site_url', array($this, 'siteUrl'), 10, 4);
            add_filter('wp_redirect', array($this, 'wpRedirect'), 10, 2);
        }
        if ($this->exists('css', $this->config)) {
            add_action('login_enqueue_scripts', array($this, 'addLoginStyle'));
        }
        if ($this->exists('background', $this->config)
            || $this->exists('logo', $this->config, false)
        ) {
            add_action('login_head', array($this, 'addLoginImage'));
        }
        if ($this->exists('error', $this->config, false)) {
            add_filter('login_errors', array($this, 'changeLoginErrors'));
        }
    }

    // ログイン画面のロゴのURLを変更する
    public function changeLoginHeaderUrl()
    {
        return getUrl('site-top');
    }

    // ログイン画面のロゴのタイトルを変更する
    public function changeLoginHeaderTitle()
    {
        return get_bloginfo('name');
    }

    public function wpLoaded()
    {
        // 未ログイン状態で管理画面にアクセスされた時は404とする
        // 標準のログイン画面にアクセスされた時は404とする
        global $pagenow;
        if ((is_admin() && !is_user_logged_in() && !defined('DOING_AJAX'))
            || 'wp-login.php' === $pagenow
        ) {
            global $wp_query;
            $wp_query->set_404();
            status_header(404);
            nocache_headers();
            get_template_part(404);
            exit;
        }

        // アクセスされたのがログイン画面であれば表示する
        $request = parse_url($_SERVER['REQUEST_URI']);
        $url = parse_url(getUrl('site-top'));
        if (!$this->exists('path', $url)) {
            $url['path'] = '';
        }
        if ($url['path'] . $this->config['url'] === $request['path']) {
            global $error, $interim_login, $action, $user_login;
            require_once ABSPATH . 'wp-login.php';
            exit;
        }
    }

    public function siteUrl($url, $path, $orig_scheme, $blog_id)
    {
        $url = $this->replaceLoginUrl($url);

        return $url;
    }

    public function wpRedirect($location, $status)
    {
        $location = $this->replaceLoginUrl($location);

        return $location;
    }

    // ログイン画面のURLを設定のURLへ置き換える
    protected function replaceLoginUrl($url)
    {
        $args = explode('?', $url);
        if (false !== strpos($url, 'wp-login.php')) {
            $url = getUrl('site-top') . $this->config['url'];
            if (1 < count($args)) {
                parse_str($args[1], $args);
                $url = add_query_arg($args, $url);
            }
        }

        return $url;
    }

    // ログイン画面にCSSファイルを追加する
    public function addLoginStyle()
    {
        foreach ($this->config['css'] as $key => $val) {
            if (file_exists(getPath('wp-style') . '/' . self::CSS_PATH . '/' . $val)) {
                wp_enqueue_style('add_login_style' . $key, getUrl('wp-style') . '/' . self::CSS_PATH . '/' . $val, array());
            }
        }
    }

    // ログイン画面のロゴと背景を変更する
    public function addLoginImage()
    {
        echo '<style>';
        if ($this->exists('background', $this->config)) {
            echo '.login {';
            // 背景色設定
            if ($this->exists('color', $this->config['background'], false)) {
                echo ' background-color: ' . $this->config['background']['color'] . ';';
            }
            // 背景画像設定
            if ($this->exists('image', $this->config['background'], false)) {
                echo ' background-image: url(' . getUrl('wp-style') . '/' . self::IMG_PATH . '/' . $this->config['background']['image'] . ');';
                if ($this->exists('position', $this->config['background'], false)) {
                    echo ' background-position: ' . $this->config['background']['position'] . ';';
                }
                if ($this->exists('repeat', $this->config['background'], false)) {
                    echo ' background-repeat: ' . $this->config['background']['repeat'] . ';';
                }
            }
            echo ' }';
        }
        if ($this->exists('logo', $this->config, false)) {
            echo '.login #login h1 a {';
            echo ' background-image: url(' . getUrl('wp-style') . '/' . self::IMG_PATH . '/' . $this->config['logo'] . ');';
            echo ' }';
        }
        echo '</style>';
    }

    // ログインエラー時のメッセージを変更する
    public function changeLoginErrors($error)
    {
        global $action;
        if ('login' === $action) {
            $error = $this->config['error'];
            if ($this->exists('reset', $this->config, false)) {
                $error .= '<br><a href="' . wp_lostpassword_url() . '">' . $this->config['reset'] . '</a>';
            }
        }
        return $error;
    }

}
