<?php

if (!class_exists('FfcBaseClass')) :

/**
 * ベースクラス.
 * プラグインメインクラスと各アドオンクラスの基底クラスとなる。
 * UPLOADS側優先のファイル検索、JSON読み込みなどが使用できる。
 */
abstract class FfcBaseClass
{

    protected $main = null;

    /**
     * コンストラクタ.
     *
     * @param Object $main freq_func_collectionインスタンス
     *
     * @access public
     * @return void
     */
    public function __construct($main)
    {
        $this->main = $main;
        $this->initialize();
    }

    /**
     * 初期化する.
     *
     * @access protected
     * @return void
     */
    protected function initialize()
    {
    }

    /**
     * 使用するフルファイル名を取得する.
     * UPLOADS内にあればUPLOADS内のファイル名、なければプラグイン内のファイル名を返す。
     * どちらにもなければfalseを返す。
     *
     * @param string  $fileName      FFCディレクトリーからの相対ファイル名
     * @param string  $pluginPrefix  プラグイン側のファイル名に付加する文字列(プラグインディレクトリーからの相対部分を付加する)
     * @param boolean $isUrl         URLを取得するかどうか(true:URL / false:リアルファイル)
     *
     * @access protected
     * @return string/false フルファイル名/ファイルがなければfalse
     */
    final protected function searchFile($fileName, $pluginPrefix = '', $isUrl = false)
    {
        // UPLOADS内にファイルがなければプラグイン内のものを使用する
        $fullFileName = FFCOLLECTION_PLUGIN_DIR_PATH . $pluginPrefix . $fileName;
        $fileUrl = FFCOLLECTION_PLUGIN_DIR_URL . '/' . $pluginPrefix . $fileName;
        if (!file_exists($fullFileName)) {
            // プラグイン内になければfalse
            $fullFileName = false;
            $fileUrl = false;
        }

        if ($isUrl) {
            return $fileUrl;
        } else {
            return $fullFileName;
        }
    }

    /**
     * JSONファイルを読み込む.
     *
     * @param string $fileName      FFCディレクトリーからの相対ファイル名
     * @param string $pluginPrefix  プラグイン側のファイル名に付加する文字列(プラグインディレクトリーからの相対部分を付加する)
     *
     * @access protected
     * @return array JSONをデコードした配列
     */
    final protected function loadJson($fileName, $pluginPrefix = '')
    {
        $jsonFile = $this->searchFile($fileName, $pluginPrefix);
        if (!$jsonFile) {
            // プラグイン内になければエラー
            $this->error(__('File not found.', FFCOLLECTION_PLUGIN_DIR_NAME) . '(' . $fileName . ')');
        }

        // ファイルを読み込み解析する
        $json = file_get_contents($jsonFile);
        $json = mb_convert_encoding($json, 'UTF8', 'ASCII, JIS, UTF-8, EUC-JP, SJIS-WIN');
        $json = json_decode($json, true);
        if (is_null($json)) {
            // 解析できなければエラー
            $json = array();
            $this->error(__('The description of the file is incorrect.', FFCOLLECTION_PLUGIN_DIR_NAME) . '(' . $fileName . ')');
        }

        return $json;
    }

    /**
     * 配列に値が存在しているかを調べる.
     * 次のいずれかの時、存在しないとして扱う。
     * ・指定されたキーがない
     * ・キーはあるが値がnullである
     * ・キーはあるが値が空配列である
     * ・空文字列である(第3引数がfalseの時)
     * ・falseである(第4引数がfalseの時)
     * ・0である(第5引数がfalseの時)
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
        if (!is_array($array)
            || !array_key_exists($key, $array)
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

    /**
     * 連想配列かどうかを調べる.
     * 次のいずれかの時、連想配列でないとする。
     * ・配列でない
     * ・空配列である
     * ・キーがすべて数値である
     *
     * @param array $arrData チェックする配列
     * @param array $strict  厳密チェック(true:キーが数値でも0からの連番でなければ連想配列とする)
     *
     * @access protected
     * @return boolean true:連想配列である / false:連想配列でない
     */
    final protected function isAssociative($arrData, $strict = false) {
        if (!is_array($arrData) || empty($arrData)) {
            return false;
        }

        $index = 0;
        foreach ($arrData as $key => $value) {
            if (!is_int($key)) {
                return true;
            }
            if ($strict
                && $index !== $key
            ) {
                return true;
            }
            $index++;
        }

        return false;
    }

    /**
     * 例外エラー表示を行う.
     *
     * @param string $message 表示するメッセージ
     *
     * @access protected
     * @return void
     */
    final protected function error($message)
    {
        header('Content-type: text/html; charset=utf-8');
        throw new Exception($message);
    }

}

endif;
