<?php

if (!class_exists('FfcField')) :

/**
 * フィールドクラス.
 * フィールドの自動生成を行う。
 */
class FfcField extends FfcBaseClass
{

    const DIRECTORY_NAME = 'field';
    protected $fieldType = array(
        'text',
        'textarea',
        'select',
        'radio',
        'checkbox',
        'number',
        'image',
        'color',
        'date',
        'file',
        'map',
        'wysiwyg',
        'group'
    );

    /**
     * configを初期化する.
     *
     * @access protected
     * @return void
     */
    public function setDefaultConfig($fields)
    {
        $ret = array();
        foreach ($fields as $setting) {
            if ($this->exists('type', $setting, false, false, false)
                && is_string($setting['type'])
                && in_array(strtolower($setting['type']), $this->fieldType, true)
            ) {
                $setting['type'] = strtolower($setting['type']);
            } else {
                $setting['type'] = strtolower($this->fieldType[0]);
            }

            $className = 'FfcField' . ucfirst($setting['type']);
            require FFCOLLECTION_PLUGIN_DIR_PATH . FFCOLLECTION_ADDONS_DIR_NAME . '/' . self::DIRECTORY_NAME . '/type/' . $className . '.php';
            $instance = new $className($this->main, $setting['key'], $setting);
            $setting = $instance->getSetting();
            $setting['instance'] = $instance;

            $ret[$setting['key']] = $setting;
        }

        return $ret;
    }

    public function getElementGooglemap($latlng = array(), $height = 0)
    {
        if (!is_array($latlng)
            || 3 !== count($latlng)
        ) {
            return;
        }

        $params = '';
        $key = $this->main->const->getConst('GOOGLE_MAPS_API_KEY');
        if ($key) {
            $params = '?key=' . $key;
        }
        $this->main->resource->set('script', 'https://maps.googleapis.com/maps/api/js' . $params, array(
            'handle' => 'gmaps-api-js'
        ), 'front');
        $this->main->resource->set('script', FFCOLLECTION_PLUGIN_DIR_URL . '/' . FFCOLLECTION_ADDONS_DIR_NAME . '/' . self::DIRECTORY_NAME . '/js/sunat.utils.js', array('handle' => 'sunat-utils-js'), 'front');
        $this->main->resource->set('script', FFCOLLECTION_PLUGIN_DIR_URL . '/' . FFCOLLECTION_ADDONS_DIR_NAME . '/' . self::DIRECTORY_NAME . '/js/sunat.googlemap.js', array('handle' => 'sunat-googlemap-js', 'deps' => array('gmaps-api-js', 'jquery', 'sunat-utils-js')), 'front');
        $this->main->resource->set('script', FFCOLLECTION_PLUGIN_DIR_URL . '/' . FFCOLLECTION_ADDONS_DIR_NAME . '/' . self::DIRECTORY_NAME . '/js/ffc.googlemap.js', array('deps' => array('jquery', 'sunat-googlemap-js', 'sunat-utils-js')), 'front');

        $style = '';
        if ($height) {
            $style = ' style="height: ' . $height . 'px;"';
        }
        echo '<div class="mapCanvas" data-latlng="[' . esc_attr(implode(',', $latlng)) . ']"' . $style . '></div>';
    }

}

endif;
