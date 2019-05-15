<?php

require FFCOLLECTION_PLUGIN_DIR_PATH . 'config/class/FfcConfigPage.php';

class FfcConfigResource extends FfcConfigPage
{

    const PAGE_NAME = 'resource';
    const PAGE_LABEL = 'リソース';
    protected $fieldKeyList = array(
        'support' => array(
            'dashicons' => array(
                'type'        => 'checkbox',
                'label'       => 'ダッシュアイコン',
                'description' => 'テーマ内でdashiconsが使用できるようになります。'
            )
        )
    );
    protected $sizeFieldKeyList = array(
        'image-size' => array(
            'label' => '',
            'type'  => ''
        )
    );

    protected function parse($arrValue)
    {
        $ret['image-size'] = array();
        if ($this->exists('image-size', $arrValue)
            && is_array($arrValue['image-size'])
        ) {
            foreach ($arrValue['image-size'] as $key => $setting) {
                $setting = wp_parse_args($setting, array(
                    'label'  => '',
                    'width'  => '',
                    'height' => '',
                    'crop'   => false
                ));
                $ret['image-size'][] = array(
                    'key'    => $key,
                    'label'  => $setting['label'],
                    'width'  => $setting['width'],
                    'height' => $setting['height'],
                    'crop'   => $setting['crop']
                );
            }
        }
        unset($arrValue['image-size']);
        $ret = array_merge($ret, $arrValue);

        return $ret;
    }

    public function preUpdatePageConfig($arrValue)
    {
        $ret = array();

        if ($this->exists('support', $arrValue)
            && is_array($arrValue['support'])
        ) {
            $ret['support'] = array_keys($arrValue['support']);
        }

        if ($this->exists('image-size', $arrValue)
            && $this->exists('key', $arrValue['image-size'])
            && is_array($arrValue['image-size']['key'])
        ) {
            $ret['image-size'] = array();
            foreach ($arrValue['image-size']['key'] as $no => $key) {
                if (!strlen($key)
                    || !$this->exists($no, $arrValue['image-size']['label'], false)
                    || !$this->exists($no, $arrValue['image-size']['width'], false)
                    || !$this->exists($no, $arrValue['image-size']['height'], false)
                    || !is_string($arrValue['image-size']['label'][$no])
                ) {
                    continue;
                }
                $ret['image-size'][$key] = array(
                    'label'  => $arrValue['image-size']['label'][$no],
                    'width'  => intval($arrValue['image-size']['width'][$no]),
                    'height' => intval($arrValue['image-size']['height'][$no])
                );
                if ($this->exists('crop', $arrValue['image-size'])
                    && $this->exists($no, $arrValue['image-size']['crop'])
                ) {
                    $ret['image-size'][$key]['crop'] = intval($arrValue['image-size']['crop'][$no]);
                }
            }
        }

        return $ret;
    }

}
