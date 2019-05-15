<?php

if (!class_exists('FfcFieldRadio')) :

require FFCOLLECTION_PLUGIN_DIR_PATH . FFCOLLECTION_ADDONS_DIR_NAME . '/field/type/FfcFieldType.php';

/**
 * フィールドクラス.
 * フィールドの自動生成を行う。
 */
class FfcFieldRadio extends FfcFieldType
{

    protected $setting = array(
        'options'   => array(),
        'delimiter' => "\n"
    );

    protected function normalize_options($value)
    {
        if (!is_array($value)) {
            $value = array();
        }

        return $value;
    }

    protected function normalize_delimiter($value)
    {
        return $value;
    }

}

endif;
