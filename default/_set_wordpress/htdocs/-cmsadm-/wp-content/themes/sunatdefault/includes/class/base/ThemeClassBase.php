<?php

abstract class ThemeClassBase
{

    const JSON_FILE_NAME = 'config/config.json';
    private static $loaded = false;
    private static $instance = array();
    private static $_config = array();

    final private function __construct()
    {
        if (isset(self::$instance[get_called_class()])) {
            throw new Exception('複数生成はできません。');
        }
        self::loadConfig();
        $this->initialize();
    }

    final private function __clone()
    {
        throw new Exception('クローン生成はできません。');
    }

    final public static function getInstance()
    {
        $class = get_called_class();
        if (!isset(self::$instance[$class])) {
            self::$instance[$class] = new $class();
        }
        return self::$instance[$class];
    }

    /**
     * 配列に値が存在しているかを調べる.
     * 次のいずれかの時、存在しないとして扱う。
     * ・指定されたキーがない
     * ・値がnullである
     * ・空配列である
     * ・空文字列である(第3引数による)
     * ・falseである(第4引数による)
     * ・0である(第5引数による)
     * 空文字列、false、0などは値としてあり得るため引数による設定がなければ除外する(存在する)。
     *
     * @param string  $key              チェックするキー
     * @param array   $array            チェックする配列
     * @param boolean $emptyStringValue 値が空文字列の時の振る舞い
     * @param boolean $falseValue       値がfalseの時の振る舞い
     * @param boolean $zeroValue        値が0の時の振る舞い
     *
     * @access protected
     * @return boolean true:存在する / false:存在しない
     */
    final protected function exists($key, $array, $emptyStringValue = true, $falseValue = true, $zeroValue = true)
    {
        if (!array_key_exists($key, $array)
            || is_null($array[$key])
            || (is_array($array[$key]) && empty($array[$key]))
        ) {
            return false;
        }
        if ('' === $array[$key]) {
            return !!$emptyStringValue;
        }
        if (false === $array[$key]) {
            return !!$falseValue;
        }
        if (0 === $array[$key]) {
            return !!$zeroValue;
        }

        return true;
    }

    private function loadConfig()
    {
        if (self::$loaded) {
            return;
        }
        self::$loaded = true;
        $jsonFile = trailingslashit(get_stylesheet_directory()) . self::JSON_FILE_NAME;
        if (!file_exists($jsonFile)) {
            self::$_config = array();
            self::error('設定ファイルが存在しません。');
        }
        $json = file_get_contents($jsonFile);
        $json = mb_convert_encoding($json, 'UTF8', 'ASCII, JIS, UTF-8, EUC-JP, SJIS-WIN');
        self::$_config = json_decode($json, true);
        if (is_null(self::$_config)) {
            self::$_config = array();
            self::error('設定ファイルの記述に誤りがあります。');
        }
        self::$loaded = true;
    }

    protected function getConfig()
    {
        return self::$_config;
    }

    protected function initialize()
    {
    }

    protected function error($message)
    {
        header("Content-type: text/html; charset=utf-8");
        throw new Exception($message);
    }

}
