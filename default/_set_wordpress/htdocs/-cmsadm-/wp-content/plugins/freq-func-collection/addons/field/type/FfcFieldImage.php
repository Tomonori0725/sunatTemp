<?php

if (!class_exists('FfcFieldImage')) :

require FFCOLLECTION_PLUGIN_DIR_PATH . FFCOLLECTION_ADDONS_DIR_NAME . '/field/type/FfcFieldType.php';

/**
 * フィールドクラス.
 * フィールドの自動生成を行う。
 */
class FfcFieldImage extends FfcFieldType
{

    protected $setting = array(
        'multiply' => 1,
        'multi'    => 1,
        'sortable' => 1
    );

    protected function initialize()
    {
        add_action('admin_enqueue_scripts', array($this, 'addAdminScript'));
    }

    public function addAdminScript() {
        wp_enqueue_media();
    }

    protected function normalize_multi($value)
    {
        $value = intval(!!$value);

        return $value;
    }

    protected function normalize_sortable($value)
    {
        $value = intval(!!$value);

        return $value;
    }

    public function createField($value = '')
    {
        $this->main->resource->set('style', FFCOLLECTION_PLUGIN_DIR_URL . '/' . FFCOLLECTION_ADDONS_DIR_NAME . '/' . parent::DIRECTORY_NAME . '/css/sunat.fileupload.css', array(), 'admin');

        $this->main->resource->set('script', FFCOLLECTION_PLUGIN_DIR_URL . '/' . FFCOLLECTION_ADDONS_DIR_NAME . '/' . parent::DIRECTORY_NAME . '/js/sunat.utils.js', array('handle' => 'sunat-utils-js'), 'admin');
        $this->main->resource->set('script', FFCOLLECTION_PLUGIN_DIR_URL . '/' . FFCOLLECTION_ADDONS_DIR_NAME . '/' . parent::DIRECTORY_NAME . '/js/sunat.fileupload.js', array('deps' => array('jquery-ui-sortable', 'sunat-utils-js')), 'admin');

        parent::createField($value);
    }

}

endif;
