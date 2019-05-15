<?php

if (!class_exists('FfcFieldType')) :

/**
 * フィールドクラス.
 * フィールドの自動生成を行う。
 */
abstract class FfcFieldType extends FfcBaseClass
{

    const DIRECTORY_NAME = 'field';
    protected $defaultSetting = array(
        'key'          => '',
        'name'         => '',
        'type'         => 'text',
        'title'        => '',
        'title-hide'   => 0,
        'id'           => '',
        'placeholder'  => '',
        'html'         => 0,
        'save-empty'   => 0,
        'multiply'     => 0,
//        'quantity'     => 1,
//        'min-quantity' => 0,
//        'max-quantity' => false,
        'description'  => '',
        'default'      => ''
    );
    protected $name = '';
    protected $setting = array();

    /**
     * コンストラクタ.
     *
     * @param Object $main freq_func_collectionインスタンス
     *
     * @access public
     * @return void
     */
    public function __construct($main, $key, $setting)
    {
        $this->main = $main;
        $this->key = $key;
        $this->setting = array_merge($this->defaultSetting, $this->setting, $setting);
        $this->normalizeSetting();
        $this->initialize();
    }

    protected function normalizeSetting()
    {
        foreach ($this->setting as $key => $value) {
            $method = 'normalize_' . str_replace('-', '_', $key);
            if (method_exists($this, $method)) {
                $this->setting[$key] = $this->{$method}($value);
            } else {
                unset($this->setting[$key]);
            }
        }
    }

    protected function normalize_key($value)
    {
        if (!is_string($value)) {
            $value = '';
        }
        if (0 === strlen($value)) {
            $value = $this->key;
        }

        return $value;
    }

    protected function normalize_name($value)
    {
        if (!is_string($value)) {
            $value = '';
        }
        if (0 === strlen($value)) {
            $value = $this->key;
        }

        return $value;
    }

    protected function normalize_type($value)
    {
        // FfcField::setDefaultConfig()でチェック済み
        return $value;
    }

    protected function normalize_title($value)
    {
        if (!is_string($value)) {
            $value = '';
        }

        return $value;
    }

    protected function normalize_title_hide($value)
    {
        $value = intval(!!$value);

        return $value;
    }

    protected function normalize_id($value)
    {
        if (!is_string($value)) {
            $value = '';
        }
        if (0 === strlen($value)) {
            $value = $this->setting['name'];
        }

        return $value;
    }

    protected function normalize_placeholder($value)
    {
        if (!is_string($value)) {
            $value = '';
        }

        return $value;
    }

    protected function normalize_html($value)
    {
        $value = intval(!!$value);

        return $value;
    }

    protected function normalize_save_empty($value)
    {
        $value = intval(!!$value);

        return $value;
    }

    protected function normalize_multiply($value)
    {
        $value = intval(!!$value);

        return $value;
    }

//    protected function normalize_quantity($value)
//    {
//        if (!is_int($value)) {
//            $value = intval($value);
//        }
//        if (1 > $value) {
//            $value = 1;
//        }
//
//        return $value;
//    }
//
//    protected function normalize_min_quantity($value)
//    {
//        if (!is_int($value)) {
//            $value = intval($value);
//        }
//        if (0 > $value) {
//            $value = 0;
//        }
//
//        return $value;
//    }
//
//    protected function normalize_max_quantity($value)
//    {
//        if (!is_int($value)) {
//            $value = intval($value);
//        }
//        if (1 > $value) {
//            $value = false;
//        }
//
//        return $value;
//    }

    protected function normalize_description($value)
    {
        if (!is_string($value)) {
            $value = '';
        }

        return $value;
    }

    protected function normalize_default($value)
    {
        if ($this->setting['multiply']) {
            if (!is_array($value)) {
                $value = array();
            }
        } else {
            if (is_array($value)) {
                $value = '';
            }
        }

        return $value;
    }

    /**
     * 設定情報を取得する.
     *
     * @access public
     * @return array 設定情報
     */
    public function getSetting()
    {
        return $this->setting;
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
        $setting = $this->setting;
        $name = $setting['name'];
        if ($setting['multiply']) {
            $name = $name . '[]';
        }
        include FFCOLLECTION_PLUGIN_DIR_PATH . FFCOLLECTION_ADDONS_DIR_NAME . '/' . self::DIRECTORY_NAME . '/view/' . $this->setting['type'] . '.php';
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
        if ($this->setting['multiply']) {
            // 複数値
            if (!$this->exists($this->setting['name'], $arrValue)) {
                // 値がなければ空配列とする
                $arrValue[$this->setting['name']] = array();
            }
        } else {
            // 単一値
            if (!$this->exists($this->setting['name'], $arrValue)) {
                // 値がなければ空文字とする
                $arrValue[$this->setting['name']] = '';
            } else {
                $arrValue[$this->setting['name']] = $arrValue[$this->setting['name']][0];
            }
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
    public function savePost($post_id, $value)
    {
        // HTML可なら有害タグを除去する
        $isKses = !!$this->exists('html', $this->setting);

        if ($this->setting['multiply']) {
            // 複数値なら一度すべて削除してから追加する
            delete_post_meta($post_id, $this->setting['name']);
            foreach ($value as $val) {
                if ($isKses) {
                    $val = wp_kses_post($val);
                }
                if (strlen($val)
                    || !!$this->exists('save-empty', $this->setting)
                ) {
                    // 値があるか、値がなくても保存する設定なら保存する
                    add_post_meta($post_id, $this->setting['name'], $val);
                }
            }
        } else {
            // 単一要素なら更新する
            if ($isKses) {
                $value = wp_kses_post($value);
            }
            if (strlen($value)
                || !!$this->exists('save-empty', $this->setting)
            ) {
                // 値があるか、値がなくても保存する設定なら保存する
                update_post_meta($post_id, $this->setting['name'], $value);
            } else {
                // 空なら削除する
                delete_post_meta($post_id, $this->setting['name']);
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
    public function saveTerm($term_id, $value)
    {
        // HTML可なら有害タグを除去する
        $isKses = !!$this->exists('html', $this->setting);

        if ($this->setting['multiply']) {
            // 複数値なら一度すべて削除してから追加する
            delete_term_meta($term_id, $this->setting['name']);
            foreach ($value as $val) {
                if ($isKses) {
                    $val = wp_kses_post($val);
                }
                if (strlen($val)
                    || !!$this->exists('save-empty', $this->setting)
                ) {
                    // 値があるか、値がなくても保存する設定なら保存する
                    add_term_meta($term_id, $this->setting['name'], $val);
                }
            }
        } else {
            // 単一要素なら更新する
            if ($isKses) {
                $value = wp_kses_post($value);
            }
            if (strlen($value)
                || !!$this->exists('save-empty', $this->setting)
            ) {
                // 値があるか、値がなくても保存する設定なら保存する
                update_term_meta($term_id, $this->setting['name'], $value);
            } else {
                // 空なら削除する
                delete_term_meta($term_id, $this->setting['name']);
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
        return get_option($this->setting['name']);
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
    public function preUpdateOption($value, $old_value)
    {
        return $value;
    }

}

endif;
