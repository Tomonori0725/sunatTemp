<?php

if (!class_exists('FfcFieldSelect')) :

require FFCOLLECTION_PLUGIN_DIR_PATH . FFCOLLECTION_ADDONS_DIR_NAME . '/field/type/FfcFieldType.php';

/**
 * フィールドクラス.
 * フィールドの自動生成を行う。
 */
class FfcFieldSelect extends FfcFieldType
{

    protected $setting = array(
        'unselected' => '',
        'options'    => array()
    );

    protected function normalize_unselected($value)
    {
        if (!is_string($value)) {
            $value = '';
        }

        return $value;
    }

    protected function normalize_options($value)
    {
        if (!is_array($value)) {
            $value = array();
        }

        return $value;
    }

}

endif;
