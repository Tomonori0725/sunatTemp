<?php

if (!class_exists('FfcLogin')) :

/**
 * ログインクラス.
 * ログインURLやデザイン、ロゴの変更を行う。
 */
class FfcLogin extends FfcBaseClass
{

    const CONFIG_NAME = 'login';
    const DIRECTORY_NAME = 'login';
    const CSS_FILE_NAME = 'style.css';
    const JS_FILE_NAME = 'scripts.js';
    protected $config = array();

    /**
     * 初期化する.
     *
     * @access protected
     * @return void
     */
    protected function initialize()
    {
        // configのログイン設定を読み込む
        $this->config = $this->main->getConfig(self::CONFIG_NAME);
        // 設定がなければ何もしない
        if (empty($this->config)) {
            return;
        }

        // URL設定があればログインURLを変更する
        if ($this->exists('url', $this->config, false)
            && is_string($this->config['url'])
        ) {
            add_action('wp_loaded', array($this, 'wpLoaded'));
            add_filter('site_url', array($this, 'siteUrl'), 10, 4);
            add_filter('wp_redirect', array($this, 'wpRedirect'), 10, 2);
            // ログイン画面へのリダイレクトを禁止する
            remove_action('template_redirect', 'wp_redirect_admin_locations', 1000);
        }

        if ($this->exists('header', $this->config)
            && is_array($this->config['header'])
        ) {
            $this->changeHeader();
        }

        if ($this->exists('background', $this->config)
            && is_array($this->config['background'])
        ) {
            add_action('login_head', array($this, 'changeBackground'));
        }

        if ($this->exists('error', $this->config)
            && is_array($this->config['error'])
        ) {
            add_filter('login_errors', array($this, 'loginErrors'), 10, 1);
        }

        $this->addLoginScripts();
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
        $url = parse_url(get_the_ffc_url('wp-top'));
        if (!$this->exists('path', $url)) {
            $url['path'] = '';
        }
        if ($url['path'] . $this->config['url'] === $request['path']) {
            global $error, $interim_login, $action, $user_login;
            include ABSPATH . 'wp-login.php';
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
            $url = get_the_ffc_url('wp-top') . $this->config['url'];
            if (1 < count($args)) {
                parse_str($args[1], $args);
                $url = add_query_arg($args, $url);
            }
        }

        return $url;
    }

    protected function changeHeader()
    {
        if ($this->exists('logo', $this->config['header'])
            && is_array($this->config['header']['logo'])
        ) {
            add_action('wp_print_scripts', array($this, 'loginHeaderLogo'));
        }
        if ($this->exists('url', $this->config['header'])
            && $this->config['header']['url']
        ) {
            add_filter('login_headerurl', array($this, 'loginHeaderUrl'));
        }
        if ($this->exists('title', $this->config['header'])
            && $this->config['header']['title']
        ) {
            add_filter('login_headertitle', array($this, 'loginHeaderTitle'));
        }
    }

    // ログイン画面のロゴと背景を変更する
    public function loginHeaderLogo()
    {
        $imageUrl = wp_get_attachment_url($this->config['header']['logo'][0], 'full');
        if (false !== $imageUrl) {
            echo '<script>' . "\n";
            echo "var loginLogo = '" . $imageUrl . "';\n";
            echo '</script>' . "\n";
        }
    }

    // ログイン画面のロゴのURLを変更する
    public function loginHeaderUrl()
    {
        return get_the_ffc_url('wp-top');
    }

    // ログイン画面のロゴのタイトルを変更する
    public function loginHeaderTitle()
    {
        return get_bloginfo('name');
    }

    public function changeBackground()
    {
        echo '<style>' . "\n";
        echo '.login {' . "\n";
        // 背景色設定
        if ($this->exists('color', $this->config['background'], false)
            && is_string($this->config['background']['color'])
        ) {
            echo 'background-color: ' . $this->config['background']['color'] . ';' . "\n";
        }
        // 背景画像設定
        if ($this->exists('image', $this->config['background'])
            && is_array($this->config['background']['image'])
        ) {
            $imageUrl = wp_get_attachment_url($this->config['background']['image'][0]);
            if (false !== $imageUrl) {
                echo 'background-image: url(' . $imageUrl . ');' . "\n";
                if ($this->exists('position', $this->config['background'], false)
                    && is_string($this->config['background']['position'])
                ) {
                    echo 'background-position: ' . $this->config['background']['position'] . ';' . "\n";
                }
                if ($this->exists('repeat', $this->config['background'], false)
                    && is_string($this->config['background']['repeat'])
                ) {
                    echo 'background-repeat: ' . $this->config['background']['repeat'] . ';' . "\n";
                }
            }
        }
        echo '}' . "\n";
        echo '</style>' . "\n";
    }

    // ログイン画面にCSSファイルを追加する
    public function addLoginScripts()
    {
        if ($this->exists('header', $this->config)
            && $this->exists('logo', $this->config['header'])
            && is_array($this->config['header']['logo'])
        ) {
            $css = $this->searchFile('logo_' . self::CSS_FILE_NAME, FFCOLLECTION_ADDONS_DIR_NAME . '/' . self::DIRECTORY_NAME . '/css/', true);
            $this->main->resource->set('style', $css, array('handle' => 'add_login_logo_style'), 'login');
            $js = $this->searchFile('logo_' . self::JS_FILE_NAME, FFCOLLECTION_ADDONS_DIR_NAME . '/' . self::DIRECTORY_NAME . '/js/', true);
            $this->main->resource->set('script', $js, array('handle' => 'add_login_logo_script', 'deps' => array('jquery')), 'login');
        }

        if ($this->exists('css', $this->config)
            && $this->config['css']
        ) {
            $css = $this->searchFile(self::CSS_FILE_NAME, FFCOLLECTION_ADDONS_DIR_NAME . '/' . self::DIRECTORY_NAME . '/css/', true);
            $this->main->resource->set('style', $css, array('handle' => 'add_login_style'), 'login');
            $js = $this->searchFile(self::JS_FILE_NAME, FFCOLLECTION_ADDONS_DIR_NAME . '/' . self::DIRECTORY_NAME . '/js/', true);
            $this->main->resource->set('script', $js, array('handle' => 'add_login_script', 'deps' => array('jquery')), 'login');
        }
    }

    // ログインエラー時のメッセージを変更する
    public function loginErrors($error)
    {
        global $action;
        if ('login' !== $action) {
            return $error;
        }

        if ($this->exists('text', $this->config['error'], false)
            && is_string($this->config['error']['text'])
        ) {
            $error = $this->config['error']['text'];
            if ($this->exists('reset', $this->config['error'], false)
                && is_string($this->config['error']['reset'])
            ) {
                $error .= '<br><a href="' . wp_lostpassword_url() . '">' . $this->config['error']['reset'] . '</a>';
            }
        }

        return $error;
    }

}

endif;
