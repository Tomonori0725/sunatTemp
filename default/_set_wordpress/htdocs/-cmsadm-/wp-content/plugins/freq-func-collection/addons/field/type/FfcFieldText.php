<?php

if (!class_exists('FfcFieldText')) :

require FFCOLLECTION_PLUGIN_DIR_PATH . FFCOLLECTION_ADDONS_DIR_NAME . '/field/type/FfcFieldType.php';

/**
 * フィールドクラス.
 * フィールドの自動生成を行う。
 */
class FfcFieldText extends FfcFieldType
{

    protected $setting = array();

}

endif;
