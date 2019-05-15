<?php
/*
Plugin Name: Freq Func COLLECTION
Plugin URI: https://wordpress.org/plugins/freq-func-collection/
Description: サイト構築時によく行われるカスタマイズを機能としてまとめた機能集です。
Version: 1.1.0
Author: Sunatmark
Author URI: https://www.sunatmark.co.jp
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: freq-func-collection
Domain Path: /languages/
*/

if (!class_exists('freq_func_collection')) :

    // 当プラグインディレクトリーパス
define('FFCOLLECTION_PLUGIN_DIR_PATH', trailingslashit(plugin_dir_path(__FILE__)));

require FFCOLLECTION_PLUGIN_DIR_PATH . 'includes/FfcBaseClass.php';

/**
 * プラグインメインクラス.
 * コンフィグクラスの読み込みおよび各アドオンの読み込み・インスタンス生成を行う。
 * 機能はコンフィグクラスおよび各アドオンに持たせるため、当クラスでは何もしない。
 */
class freq_func_collection extends FfcBaseClass
{

    const PLUGIN_VERSION = '1.1.0';
    const TAGS_FILE_NAME = 'tags.php';
    protected $config = null;
    protected $permitUseModule = array();
    protected $addons = array();
    protected $addonsInstance = array();

    /**
     * コンストラクタ.
     *
     * @access public
     * @return void
     */
    public function __construct($main)
    {
        add_action('plugins_loaded', array($this, 'pluginsLoaded'), 10);

        // 定数を設定する
        $this->define();

        // ドメイン設定、翻訳ファイル読み込み
        load_plugin_textdomain(FFCOLLECTION_PLUGIN_DIR_NAME, false, FFCOLLECTION_PLUGIN_DIR_NAME . '/languages');
    }

    public function pluginsLoaded()
    {
        require FFCOLLECTION_PLUGIN_DIR_PATH . 'config/FfcConfig.php';
        $this->config = new FfcConfig($this);

        $this->initialize();
    }

    protected function define()
    {
        // 当プラグインディレクトリー名
        define('FFCOLLECTION_PLUGIN_DIR_NAME', untrailingslashit(wp_basename(FFCOLLECTION_PLUGIN_DIR_PATH)));
        // 当プラグインディレクトリーURI
        define('FFCOLLECTION_PLUGIN_DIR_URL', untrailingslashit(plugins_url(wp_basename(FFCOLLECTION_PLUGIN_DIR_PATH))));
        // アドオンディレクトリー名
        define('FFCOLLECTION_ADDONS_DIR_NAME', 'addons');
    }

    /**
     * 初期化する.
     *
     * @access protected
     * @return void
     */
    protected function initialize()
    {
        // アドオンを読み込む
        $this->requireAddons(FFCOLLECTION_PLUGIN_DIR_PATH . FFCOLLECTION_ADDONS_DIR_NAME);

        // アドオンのインスタンスを生成する
        $this->createAddons();
    }

    /**
     * アドオンを読み込む.
     * ディレクトリーを走査する。
     *
     * @param string $path アドオンディレクトリー
     *
     * @access protected
     * @return void
     */
    protected function requireAddons($path)
    {
        // 他のアドオンの初期化時に使用できるように先に基本アドオンを読み込む
        $basisAddons = include FFCOLLECTION_PLUGIN_DIR_PATH . 'includes/basis_addons.php';
        foreach ($basisAddons as $addonName) {
            $this->requireAddon($path, $addonName);
        }

        // アドオンディレクトリー内を走査する
        // 基本アドオンは先に読み込んでいるため省く
        if ($dh = opendir($path)) {
            while (false !== ($addonName = readdir($dh))) {
                if (in_array($addonName, $basisAddons, true)
                    || ('.' === $addonName)
                    || ('..' === $addonName)
                    || !is_dir($path . '/' . $addonName)
                ) {
                    continue;
                }

                $this->requireAddon($path, $addonName);
            }
            closedir($dh);
        }
    }

    /**
     * アドオンを読み込む.
     * $path/[name]/Ffc[Name].php の構造のみ読み込む
     * $path/[name]/[TAGS_FILE_NAME] が存在すれば読み込む
     *
     * @param string $path      アドオンディレクトリー
     * @param string $addonName アドオン名
     *
     * @access protected
     * @return void
     */
    protected function requireAddon($path, $addonName)
    {
        $addonName = strtolower($addonName);
        $addonDir = $path . '/' . $addonName . '/';
        // $paht/[name]/Ffc[Name].php の構造のみアドオンとみなす
        $fullFilename = $addonDir . 'Ffc' . ucfirst($addonName) . '.php';
        if (!is_file($fullFilename)) {
            return;
        }

        require $fullFilename;
        // アドオンリストに登録する
        $this->addons[] = $addonName;

        // テンプレートタグが宣言されていれば読み込む
        $tagsFilename = $addonDir . self::TAGS_FILE_NAME;
        if (is_file($tagsFilename)) {
            require $tagsFilename;
        }
        // 現在が管理画面で、管理画面用のテンプレートタグが宣言されていれば読み込む
        $tagsFilename = $addonDir . 'admin-' . self::TAGS_FILE_NAME;
        if (is_admin() && is_file($tagsFilename)) {
            require $tagsFilename;
        }
    }

    /**
     * アドオンのインスタンスを生成する.
     *
     * @access protected
     * @return void
     */
    protected function createAddons()
    {
        // アドオンリストを基にインスタンスを生成する
        foreach ($this->addons as $addonName) {
            $className = 'Ffc' . ucfirst($addonName);
            $this->addonsInstance[$addonName] = new $className($this);
            // 使用許可リストに登録する
            $this->permitUseModule[] = $addonName;
        }
    }

    /**
     * コンフィグを取得する.
     * キーの指定がなければコンフィグすべてを返す。
     *
     * @param string $key 取得したいコンフィグのキー
     *
     * @access protected
     * @return array コンフィグ
     */
    public function version()
    {
        return self::PLUGIN_VERSION;
    }

    /**
     * コンフィグを取得する.
     * キーの指定がなければコンフィグすべてを返す。
     *
     * @param string $key 取得したいコンフィグのキー
     *
     * @access protected
     * @return array コンフィグ
     */
    public function getConfig($key = null)
    {
        return $this->config->getConfig($key);
    }

    /**
     * アドオンのインスタンスを取得するためのゲッター.
     *
     * @param string $module 取得したいアドオンのキー
     *
     * @access protected
     * @return FfcBaseClass/false 各アドオンのインスタンス
     */
    public function __get($module)
    {
        if (!in_array($module, $this->permitUseModule, true)) {
            // 許可されたモジュールでなければfalseを返す
            return false;
        }

        return $this->addonsInstance[$module];
    }

}
$ffCollection = new freq_func_collection(null);

endif;
