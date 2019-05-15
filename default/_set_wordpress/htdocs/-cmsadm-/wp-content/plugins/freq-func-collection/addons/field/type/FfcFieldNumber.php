<?php

if (!class_exists('FfcFieldNumber')) :

require FFCOLLECTION_PLUGIN_DIR_PATH . FFCOLLECTION_ADDONS_DIR_NAME . '/field/type/FfcFieldType.php';

/**
 * フィールドクラス.
 * フィールドの自動生成を行う。
 */
class FfcFieldNumber extends FfcFieldType
{

    protected $setting = array(
        'min'  => false,
        'max'  => false,
        'step' => 1
    );

    protected function normalize_min($value)
    {
        if (false !== $value) {
            if (!is_int($value)) {
                $value = intval($value);
            }
        }

        return $value;
    }

    protected function normalize_max($value)
    {
        if (false !== $value) {
            if (!is_int($value)) {
                $value = intval($value);
            }
        }

        return $value;
    }

    protected function normalize_step($value)
    {
        if (false !== $value) {
            if (!is_int($value)) {
                $value = intval($value);
            }
        }

        return $value;
    }

    public function createField($value = '')
    {
        $this->main->resource->set('style', FFCOLLECTION_PLUGIN_DIR_URL . '/' . FFCOLLECTION_ADDONS_DIR_NAME . '/' . parent::DIRECTORY_NAME . '/css/sunat.spinner.css', array(), 'admin');

        $this->main->resource->set('script', FFCOLLECTION_PLUGIN_DIR_URL . '/' . FFCOLLECTION_ADDONS_DIR_NAME . '/' . parent::DIRECTORY_NAME . '/js/sunat.utils.js', array('handle' => 'sunat-utils-js'), 'admin');
        $this->main->resource->set('script', FFCOLLECTION_PLUGIN_DIR_URL . '/' . FFCOLLECTION_ADDONS_DIR_NAME . '/' . parent::DIRECTORY_NAME . '/js/sunat.spinner.js', array('deps' => array('sunat-utils-js')), 'admin');

        foreach (array('min', 'max', 'step') as $key) {
            ${$key} = '';
            if (false !== $this->setting[$key]) {
                ${$key} = ' data-num-' . $key . '="' . esc_attr($this->setting[$key]) . '"';
            }
        }

        $setting = $this->setting;
        $name = $setting['name'];
        if ($setting['multiply']) {
            $name = $name . '[]';
        }
        include FFCOLLECTION_PLUGIN_DIR_PATH . FFCOLLECTION_ADDONS_DIR_NAME . '/' . self::DIRECTORY_NAME . '/view/' . $this->setting['type'] . '.php';
    }

}

endif;
