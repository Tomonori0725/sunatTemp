<?php

if (!class_exists('FfcFieldWysiwyg')) :

require FFCOLLECTION_PLUGIN_DIR_PATH . FFCOLLECTION_ADDONS_DIR_NAME . '/field/type/FfcFieldType.php';

/**
 * フィールドクラス.
 * フィールドの自動生成を行う。
 */
class FfcFieldWysiwyg extends FfcFieldType
{

    protected $setting = array(
        'html' => 1
    );

}

endif;
