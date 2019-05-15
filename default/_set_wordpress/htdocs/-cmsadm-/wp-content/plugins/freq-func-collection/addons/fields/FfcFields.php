<?php

if (!class_exists('FfcFields')) :

/**
 * 複数フィールドクラス.
 * 複数フィールドの設定を行う。
 */
class FfcFields extends FfcBaseClass
{

    const CONFIG_NAME = 'posttype';
    const DIRECTORY_NAME = 'fields';
    protected $config = array();
    protected $metabox = null;
    protected $taxonomy = null;

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

        require FFCOLLECTION_PLUGIN_DIR_PATH . FFCOLLECTION_ADDONS_DIR_NAME . '/' . self::DIRECTORY_NAME . '/type/FfcFieldsMetabox.php';
        require FFCOLLECTION_PLUGIN_DIR_PATH . FFCOLLECTION_ADDONS_DIR_NAME . '/' . self::DIRECTORY_NAME . '/type/FfcFieldsTaxonomy.php';

        $this->metabox = new FfcFieldsMetabox($this->main);
        $this->taxonomy = new FfcFieldsTaxonomy($this->main);
    }

}

endif;
