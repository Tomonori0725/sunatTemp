<?php

require FFCOLLECTION_PLUGIN_DIR_PATH . 'config/class/FfcConfigPage.php';

class FfcConfigLogin extends FfcConfigPage
{

    const PAGE_NAME = 'login';
    const PAGE_LABEL = 'ログイン';
    protected $fieldKeyList = array(
        'url' => array(
            'type'        => 'text',
            'label'       => 'ログイン画面URL',
            'description' => '入力の先頭が / でない場合、 / を補完します。<br>設定がなければデフォルトのURLとなります。'
        ),
        'header' => array(
            'logo' => array(
                'type'        => 'image',
                'label'       => 'ロゴ画像',
                'description' => '設定がなければデフォルト(WordPressロゴ)が表示されます。'
            ),
            'url' => array(
                'type'       => 'checkbox',
                'label'      => 'リンク先',
                'checklabel' => 'ロゴのリンク先をフロントトップページにする'
            ),
            'title' => array(
                'type'        => 'checkbox',
                'label'       => 'タイトル',
                'checklabel'  => 'ロゴのタイトルをサイト名にする',
                'description' => 'ロゴにマウスカーソルをホバーした際に表示されるTIPSです。'
            )
        ),
        'background' => array(
            'image' => array(
                'type'        => 'image',
                'label'       => '背景画像ファイル',
                'description' => '設定がなければなにも表示されません。'
            ),
            'position' => array(
                'type'        => 'text',
                'label'       => '背景画像位置',
                'description' => 'CSSの background-position の値となります。 (背景画像ファイルが設定されている時のみ有効です)'
            ),
            'repeat' => array(
                'type'        => 'text',
                'label'       => '背景画像繰り返し',
                'description' => 'CSSの background-repeat の値となります。 (背景画像ファイルが設定されている時のみ有効です)'
            ),
            'color' => array(
                'type'        => 'color',
                'label'       => '背景色',
                'description' => 'CSSの background-color の値となります。<br>設定がなければデフォルト(#f1f1f1)が表示されます。'
            )
        ),
        'css' => array(
            'type'        => 'checkbox',
            'label'       => 'デザイン',
            'checklabel'  => 'デザインを変更する',
            'description' => '当プラグインのCSSを使用します。&nbsp;(若干小綺麗になるかも。お試しください)<br>使用する場合は併せてロゴの画像・リンク先・タイトルの設定もおすすめします。'
        ),
        'error' => array(
            'text' => array(
                'type'  => 'text',
                'label' => 'ログインエラー時のメッセージ'
            ),
            'reset' => array(
                'type'  => 'text',
                'label' => 'パスワードリセット用リンクテキスト'
            )
        )
    );

    protected function adminInitialize()
    {
        parent::adminInitialize();

        add_action('admin_enqueue_scripts', array($this, 'addAdminScript'));
    }

    public function addAdminScript() {
        wp_enqueue_media();
    }

    protected function parse($arrValue)
    {
        $this->main->resource->set('style', FFCOLLECTION_PLUGIN_DIR_URL . '/' . FFCOLLECTION_ADDONS_DIR_NAME . '/field/css/sunat.fileupload.css', array(), 'admin');

        $this->main->resource->set('script', FFCOLLECTION_PLUGIN_DIR_URL . '/' . FFCOLLECTION_ADDONS_DIR_NAME . '/field/js/sunat.utils.js', array('handle' => 'sunat-utils-js'), 'admin');
        $this->main->resource->set('script', FFCOLLECTION_PLUGIN_DIR_URL . '/' . FFCOLLECTION_ADDONS_DIR_NAME . '/field/js/sunat.fileupload.js', array('deps' => array('jquery', 'sunat-utils-js')), 'admin');

        wp_enqueue_style('wp-color-picker');
        $this->main->resource->set('script', FFCOLLECTION_PLUGIN_DIR_URL . '/' . FFCOLLECTION_ADDONS_DIR_NAME . '/field/js/sunat.colorpicker.js', array('deps' => array('wp-color-picker')), 'admin');

        return $arrValue;
    }

    public function preUpdatePageConfig($arrValue)
    {
        $ret = array();

        if ($this->exists('url', $arrValue, false)) {
            if (0 !== strpos($arrValue['url'], '/')) {
                $arrValue['url'] = '/' . $arrValue['url'];
            }
        }
        if (array_key_exists('header', $arrValue)) {
            if (array_key_exists('url', $arrValue['header'])) {
                $arrValue['header']['url'] = intval($arrValue['header']['url']);
            }
            if (array_key_exists('title', $arrValue['header'])) {
                $arrValue['header']['title'] = intval($arrValue['header']['title']);
            }
        }
        if (array_key_exists('css', $arrValue)) {
            $arrValue['css'] = intval($arrValue['css']);
        }
        $ret = $arrValue;

        return $ret;
    }

}
