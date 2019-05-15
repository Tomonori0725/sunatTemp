<?php

if (!class_exists('FfcConst')) :

/**
 * 定数機能クラス.
 * config内の定数やdata.jsonを配置することで、その定数を取得できる。
 */
class FfcConst extends FfcBaseClass
{

    const CONFIG_NAME = 'const';
    const DIRECTORY_NAME = 'const';
    const DATA_FILE_NAME = 'data.json';
    protected $config = array();
    protected $data = array();

    /**
     * 初期化する.
     *
     * @access protected
     * @return void
     */
    protected function initialize()
    {
        // configの定数設定を読み込む
        $this->config = $this->main->getConfig(self::CONFIG_NAME);
        // データリストを読み込む
        $this->data = $this->loadJson(self::DATA_FILE_NAME, FFCOLLECTION_ADDONS_DIR_NAME . '/' . self::DIRECTORY_NAME . '/');

        if ($this->exists('define', $this->config)
            && $this->config['define']
        ) {
            $this->defineConst();
        }
    }

    public function defineConst()
    {
        if ($this->exists('const', $this->config)
            && is_array($this->config['const'])
        ) {
            foreach ($this->config['const'] as $key => $value) {
                if (!defined($key)) {
                    define($key, $value);
                }
            }
        }
    }

    /**
     * コンフィグに設定された定数を取得する.
     *
     * @param string $key 取得する定数のキー
     *
     * @access public
     * @return mixed 定数 / キーが存在しなければnull
     */
    public function getConst($key)
    {
        $data = null;
        if ($this->exists('const', $this->config)
            && $this->exists($key, $this->config['const'])
        ) {
            $data = $this->config['const'][$key];
        }

        return $data;
    }

    /**
     * dataからデータを取得する.
     * キーの指定がなければデータ種別すべてを返す。
     *
     * @param string $type データ種別
     * @param string $key  データキー
     *
     * @access public
     * @return mixed データ / データ種別もしくはキーが存在しなければnull
     */
    public function getData($type, $key = null)
    {
        $data = null;
        if (array_key_exists($type, $this->data)) {
            if (!is_null($key)) {
                if (array_key_exists($key, $this->data[$type])) {
                    $data = $this->data[$type][$key];
                }
            } else {
                $data = $this->data[$type];
            }
        }

        return $data;
    }

}

endif;
