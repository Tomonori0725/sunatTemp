<?php

if (!class_exists('FfcFieldMap')) :

require FFCOLLECTION_PLUGIN_DIR_PATH . FFCOLLECTION_ADDONS_DIR_NAME . '/field/type/FfcFieldType.php';

/**
 * フィールドクラス.
 * フィールドの自動生成を行う。
 */
class FfcFieldMap extends FfcFieldType
{

    protected $defaultCenter = array(
        '35.01112451225291',
        '135.76190575721537',
        18
    );
    protected $setting = array(
        'default' => array(
            'lat'  => '',
            'lng'  => '',
            'zoom' => ''
        ),
        'placeholder' => array(
            'lat'  => '',
            'lng'  => '',
            'zoom' => ''
        ),
        'center' => array(
            '',
            '',
            ''
        )
    );

    protected function normalize_default($value)
    {
        foreach (array('lat', 'lng', 'zoom') as $key) {
            if (!$this->exists($key, $value)
                || !is_numeric($value[$key])
            ) {
                $value[$key] = '';
            }
        }

        return $value;
    }

    protected function normalize_placeholder($value)
    {
        foreach (array('lat', 'lng', 'zoom') as $key) {
            if (!$this->exists($key, $value)
                || !is_string($value[$key])
            ) {
                $value[$key] = '';
            }
        }

        return $value;
    }

    protected function normalize_center($value)
    {
        for ($i = 0; $i < 3; $i++) {
            if (!$this->exists($i, $value)
                || !is_numeric($value[$i])
            ) {
                $value[$i] = $this->defaultCenter[$i];
            }
        }
        return $value;
    }

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
        $params = '';
        $key = $this->main->const->getConst('GOOGLE_MAPS_API_KEY');
        if ($key) {
            $params = '?key=' . $key;
        }
        $this->main->resource->set('script', 'https://maps.googleapis.com/maps/api/js' . $params, array(
            'handle' => 'gmaps-api-js'
        ), 'admin');
        $this->main->resource->set('script', FFCOLLECTION_PLUGIN_DIR_URL . '/' . FFCOLLECTION_ADDONS_DIR_NAME . '/' . parent::DIRECTORY_NAME . '/js/sunat.utils.js', array('handle' => 'sunat-utils-js'), 'admin');
        $this->main->resource->set('script', FFCOLLECTION_PLUGIN_DIR_URL . '/' . FFCOLLECTION_ADDONS_DIR_NAME . '/' . parent::DIRECTORY_NAME . '/js/sunat.googlemap.js', array('handle' => 'sunat-googlemap-js', 'deps' => array('gmaps-api-js', 'jquery', 'sunat-utils-js')), 'admin');
        $this->main->resource->set('script', FFCOLLECTION_PLUGIN_DIR_URL . '/' . FFCOLLECTION_ADDONS_DIR_NAME . '/' . parent::DIRECTORY_NAME . '/js/googlemap.js', array('deps' => array('jquery', 'sunat-googlemap-js', 'sunat-utils-js')), 'admin');

        parent::createField($value);
    }

    /**
     * フィールド用の値を取得する.
     *
     * @param array $arrValue 全フィールドの値
     *
     * @access public
     * @return array 全フィールドの値
     */
    public function setData($arrValue)
    {
        $arrValue[$this->setting['name']] = array();
        foreach (array('lat', 'lng', 'zoom') as $key) {
            if ($this->setting['multiply']) {
                // 複数値
                if (!$this->exists($this->setting['name'] . '-' . $key, $arrValue)) {
                    // 値がなければ空配列とする
                    $arrValue[$this->setting['name']][$key] = array();
                } else {
                    $arrValue[$this->setting['name']][$key] = $arrValue[$this->setting['name'] . '-' . $key];
                }
            } else {
                // 単一値
                if (!$this->exists($this->setting['name'] . '-' . $key, $arrValue)) {
                    // 値がなければ空文字とする
                    $arrValue[$this->setting['name']][$key] = '';
                } else {
                    $arrValue[$this->setting['name']][$key] = $arrValue[$this->setting['name'] . '-' . $key][0];
                }
            }
            unset($arrValue[$this->setting['name'] . '-' . $key]);
        }

        return $arrValue;
    }

    /**
     * 投稿用カスタムフィールドの値を保存する.
     *
     * @param integer $post_id 投稿ID
     * @param mixed   $value 値
     *
     * @access public
     * @return void
     */
    public function savePost($post_id, $arrValue)
    {
        // HTML可なら有害タグを除去する
        $isKses = !!$this->exists('html', $this->setting);

        // $arrValue[map][lat], $arrValue[map][lng], $arrValue[map][zoom]の形で来る
        foreach ($arrValue as $key => $value) {
            if ($this->setting['multiply']) {
                // 複数値なら一度すべて削除してから追加する
                delete_post_meta($post_id, $this->setting['name'] . '-' . $key);
                foreach ($value as $val) {
                    if ($isKses) {
                        $val = wp_kses_post($val);
                    }
                    if (strlen($val)
                        || !!$this->exists('save-empty', $this->setting, false, false, false)
                    ) {
                        // 値があるか、値がなくても保存する設定なら保存する
                        add_post_meta($post_id, $this->setting['name'] . '-' . $key, $val);
                    }
                }
            } else {
                // 単一要素なら更新する
                if ($isKses) {
                    $value = wp_kses_post($value);
                }
                if (strlen($value)
                    || !!$this->exists('save-empty', $this->setting, false, false, false)
                ) {
                    // 値があるか、値がなくても保存する設定なら保存する
                    update_post_meta($post_id, $this->setting['name'] . '-' . $key, $value);
                } else {
                    // 空なら削除する
                    delete_post_meta($post_id, $this->setting['name'] . '-' . $key);
                }
            }
        }
    }

    /**
     * タクソノミー用カスタムフィールドの値を保存する.
     *
     * @param integer $term_id タームID
     * @param mixed   $value 値
     *
     * @access public
     * @return void
     */
    public function saveTerm($term_id, $arrValue)
    {
        // HTML可なら有害タグを除去する
        $isKses = !!$this->exists('html', $this->setting);

        // $arrValue[map][lat], $arrValue[map][lng], $arrValue[map][zoom]の形で来る
        foreach ($arrValue as $key => $value) {
            if ($this->setting['multiply']) {
                // 複数値なら一度すべて削除してから追加する
                delete_term_meta($term_id, $this->setting['name'] . '-' . $key);
                foreach ($value as $val) {
                    if ($isKses) {
                        $val = wp_kses_post($val);
                    }
                    if (strlen($val)
                        || !!$this->exists('save-empty', $this->setting, false, false, false)
                    ) {
                        // 値があるか、値がなくても保存する設定なら保存する
                        add_term_meta($term_id, $this->setting['name'] . '-' . $key, $val);
                    }
                }
            } else {
                // 単一要素なら更新する
                if ($isKses) {
                    $value = wp_kses_post($value);
                }
                if (strlen($value)
                    || !!$this->exists('save-empty', $this->setting, false, false, false)
                ) {
                    // 値があるか、値がなくても保存する設定なら保存する
                    update_term_meta($term_id, $this->setting['name'] . '-' . $key, $value);
                } else {
                    // 空なら削除する
                    delete_term_meta($term_id, $this->setting['name'] . '-' . $key);
                }
            }
        }
    }

    /**
     * メニューページ用カスタムフィールドの値を取得する.
     *
     * @access public
     * @return mixed カスタムフィールドの値
     */
    public function getOption()
    {
        $ret = array();
        foreach (array('lat', 'lng', 'zoom') as $key) {
            $value = get_option($this->setting['name'] . '-' . $key);
            if (false === $value) {
                $value = '';
            }
            $ret[$key] = $value;
        }

        return $ret;
    }

    /**
     * メニューページ用カスタムフィールドの値を保存前に変更する.
     *
     * @param mixed $value     新しい値
     * @param mixed $old_value 前回の値
     *
     * @access public
     * @return mixed 変更後の値
     */
    public function preUpdateOption($arrValue, $old_value)
    {
        // HTML可なら有害タグを除去する
        $isKses = !!$this->exists('html', $this->setting);

        // $arrValue[lat], $arrValue[lng], $arrValue[zoom]の形で来る
        foreach ($arrValue as $key => $value) {
            if ($this->setting['multiply']) {
                // 複数値なら一度すべて削除してから追加する
                delete_option($this->setting['name'] . '-' . $key);
                foreach ($value as $val) {
                    if ($isKses) {
                        $val = wp_kses_post($val);
                    }
                    if (strlen($val)
                        || !!$this->exists('save-empty', $this->setting, false, false, false)
                    ) {
                        // 値があるか、値がなくても保存する設定なら保存する
                        add_option($this->setting['name'] . '-' . $key, $val);
                    }
                }
            } else {
                // 単一要素なら更新する
                if ($isKses) {
                    $value = wp_kses_post($value);
                }
                if (strlen($value)
                    || !!$this->exists('save-empty', $this->setting, false, false, false)
                ) {
                    // 値があるか、値がなくても保存する設定なら保存する
                    update_option($this->setting['name'] . '-' . $key, $value);
                } else {
                    // 空なら削除する
                    delete_option($this->setting['name'] . '-' . $key);
                }
            }
        }

        return false;
    }

}

endif;
