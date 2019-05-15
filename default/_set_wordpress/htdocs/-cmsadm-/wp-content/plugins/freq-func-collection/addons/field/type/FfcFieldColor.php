<?php

if (!class_exists('FfcFieldColor')) :

require FFCOLLECTION_PLUGIN_DIR_PATH . FFCOLLECTION_ADDONS_DIR_NAME . '/field/type/FfcFieldType.php';

/**
 * フィールドクラス.
 * フィールドの自動生成を行う。
 */
class FfcFieldColor extends FfcFieldType
{

    protected $setting = array();

    /**
     * フィールドを自動生成する.
     *
     * @param mixed $value フィールドの値
     *
     * @access public
     * @return void
     */
    public function createField($value = '')
    {
        wp_enqueue_style('wp-color-picker');
        $this->main->resource->set('script', FFCOLLECTION_PLUGIN_DIR_URL . '/' . FFCOLLECTION_ADDONS_DIR_NAME . '/' . parent::DIRECTORY_NAME . '/js/sunat.colorpicker.js', array('deps' => array('wp-color-picker')), 'admin');

        parent::createField($value);
    }

}

endif;
