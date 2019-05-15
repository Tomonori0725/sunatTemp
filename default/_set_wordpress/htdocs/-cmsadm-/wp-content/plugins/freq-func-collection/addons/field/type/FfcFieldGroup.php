<?php

if (!class_exists('FfcFieldGroup')) :

require FFCOLLECTION_PLUGIN_DIR_PATH . FFCOLLECTION_ADDONS_DIR_NAME . '/field/type/FfcFieldType.php';

/**
 * フィールドグループクラス.
 * フィールドの自動生成を行う。
 */
class FfcFieldGroup extends FfcFieldType
{

    protected $setting = array(
        'item'   => array()
    );

    protected function initialize()
    {
        $setting = array();
        foreach ($this->setting['item'] as $item) {
            $item['name'] = $this->setting['name'] . '[' . $item['key'] . ']';
            $className = 'FfcField' . ucfirst($item['type']);
            require FFCOLLECTION_PLUGIN_DIR_PATH . FFCOLLECTION_ADDONS_DIR_NAME . '/' . self::DIRECTORY_NAME . '/type/' . $className . '.php';
            $instance = new $className($this->main, $item['key'], $item);
            $item = $instance->getSetting();
            $item['instance'] = $instance;

            $setting[$item['key']] = $item;
        }
        $this->setting['item'] = $setting;
    }

    protected function normalize_item($value)
    {
        if (!is_array($value)) {
            $value = array();
        }

        return $value;
    }

}

endif;
