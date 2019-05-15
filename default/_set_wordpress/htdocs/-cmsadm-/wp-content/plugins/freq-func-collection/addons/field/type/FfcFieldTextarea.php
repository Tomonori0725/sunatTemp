<?php

if (!class_exists('FfcFieldTextarea')) :

require FFCOLLECTION_PLUGIN_DIR_PATH . FFCOLLECTION_ADDONS_DIR_NAME . '/field/type/FfcFieldType.php';

/**
 * フィールドクラス.
 * フィールドの自動生成を行う。
 */
class FfcFieldTextarea extends FfcFieldType
{

    protected $setting = array(
        'rows' => 5
    );

    protected function normalize_rows($value)
    {
        if (!is_int($value)) {
            $value = intval($value);
        }
        if (1 > $value) {
            $value = 1;
        }

        return $value;
    }

}

endif;
