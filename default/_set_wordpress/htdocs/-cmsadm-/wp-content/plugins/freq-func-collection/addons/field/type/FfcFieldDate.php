<?php

if (!class_exists('FfcFieldDate')) :

require FFCOLLECTION_PLUGIN_DIR_PATH . FFCOLLECTION_ADDONS_DIR_NAME . '/field/type/FfcFieldType.php';

/**
 * フィールドクラス.
 * フィールドの自動生成を行う。
 */
class FfcFieldDate extends FfcFieldType
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
        $this->main->resource->set('style', FFCOLLECTION_PLUGIN_DIR_URL . '/' . FFCOLLECTION_ADDONS_DIR_NAME . '/' . parent::DIRECTORY_NAME . '/css/sunat.datepicker.css', array(), 'admin');

        $this->main->resource->set('script', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1/i18n/jquery.ui.datepicker-ja.min.js', array('handle' => 'jquery-ui-datepicker-ja', 'deps' => array('jquery-ui-datepicker')), 'admin');
        $this->main->resource->set('script', FFCOLLECTION_PLUGIN_DIR_URL . '/' . FFCOLLECTION_ADDONS_DIR_NAME . '/' . parent::DIRECTORY_NAME . '/js/sunat.datepicker.js', array('deps' => array('jquery-ui-datepicker-ja')), 'admin');

        parent::createField($value);
    }

}

endif;
