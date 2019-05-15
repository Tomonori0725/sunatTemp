<?php

if (!class_exists('FfcConfig')) :

/**
 * メニューページクラス.
 * メニューページの追加を行う。
 */
class FfcConfig extends FfcBaseClass
{

    protected $class = array();
    protected $menuPage = array(
        'basic',
        'const',
        'login',
        'posttype',
        'menupage',
        'resource',
        'widget'
    );
    const MENU_POSITION = 81;

    /**
     * 初期化する.
     *
     * @access protected
     * @return void
     */
    protected function initialize()
    {
        // 各設定ページ用のクラスのインスタンスを作成・保持する
        foreach ($this->menuPage as $pageName) {
            $className = 'FfcConfig' . ucfirst($pageName);
            require FFCOLLECTION_PLUGIN_DIR_PATH . 'config/class/' . $className . '.php';
            $this->class[$pageName] = new $className($this->main);
        }

        $this->checkVersion();

        if (is_admin()) {
            // プラグイン一覧に「設定」を追加する
            add_filter('plugin_action_links_' . FFCOLLECTION_PLUGIN_DIR_NAME . '/' . FFCOLLECTION_PLUGIN_DIR_NAME . '.php', array($this, 'addConfigLink'), 10, 4);
            // メニューページを追加する
            add_action('admin_menu', array($this, 'addMenuPage'));
        }
    }

    /**
     * プラグイン一覧に「設定」を追加する.
     *
     * @param array  $actions     プラグインアクションリンク配列
     * @param string $plugin_file プラグインファイルのパス
     * @param array  $plugin_data プラグインデータ配列
     * @param string $context     プラグインコンテキスト
     *
     * @access public
     * @return void
     */
    public function addConfigLink($actions, $plugin_file, $plugin_data, $context)
    {
        $actions['config'] = '<a href="admin.php?page=ffc-config-' . $this->menuPage[0] . '">設定</a>';
        return $actions;
    }

    /**
     * メニューページを追加する.
     *
     * @access public
     * @return void
     */
    public function addMenuPage()
    {
        // Settings API設定
        add_action('admin_init', array($this, 'register'));

        // 親メニューを作成する
        $menuSlug = 'ffc-config-' . $this->menuPage[0];
        add_menu_page('Freq Func COLLECTION', 'Freq Func COLLECTION', 'manage_options', $menuSlug, '', 'dashicons-admin-settings', self::MENU_POSITION);
        // 各設定ページのメニューページを追加する
        foreach ($this->class as $pageClass) {
            $pageClass->addMenuPage($menuSlug);
        }
    }

    public function register()
    {
        foreach ($this->class as $pageName => $pageClass) {
            add_settings_section(
                'ffc-config-' . $pageName . '-section',
                $pageClass::PAGE_LABEL . '設定',
                array($pageClass, 'description'),
                'ffc-config-' . $pageName
            );
            // フィールド表示は各設定ページクラスで直接行う
            // add_settings_field(
            //     'ffc-config-' . $pageName,
            //     '',
            //     function () {
            //     },
            //     'ffc-config-' . $pageName,
            //     'ffc-config-' . $pageName . '-section'
            // );
            register_setting(
                'ffc-config-' . $pageName . '-group',
                'ffc-config-' . $pageName
            );
        }
    }

    public function getConfig($key = null)
    {
        if (is_null($key)) {
            // キー指定がなければconfig全部を返す
            $config = array();
            foreach ($this->class as $pageName => $class) {
                $config[$pageName] = $class->getConfig();
            }

            return $config;
        }

        if (!$this->exists($key, $this->class)) {
            // 指定されたキーがなければ空配列を返す
            return array();
        }

        return $this->class[$key]->getConfig();
    }

    protected function checkVersion()
    {
        $dbVersion = get_option('ffc-config-version', '1.0.0');
        $currentVersion = $this->main->version();
        if (version_compare($dbVersion, $currentVersion, '==')) {
            return;
        }

        if (version_compare($dbVersion,  $currentVersion, '>')) {
            $this->error('以前より古いバージョンの『Freq Func COLLECTION』がインストールされました。');
        }

        // 各設定のDBバージョンアップを行う
        foreach ($this->class as $pageName => $pageClass) {
            $pageClass->migration($dbVersion, $currentVersion);
        }

        update_option('ffc-config-version', $currentVersion);
    }
}

endif;
