<?php if (!defined('ERROR_INC')): define('ERROR_INC', 1);

/*----------------------------------------------------------------------------
機能概要: エラーリポート関数
機能説明:
	実行時エラーをフックしてエラーリポートを行う。
	error.php の内容がエラーを表示する時の実際の処理

引数:
	int    $errno   [in] エラー番号
	string $errstr  [in] エラーメッセージ
	string $errfile [in] エラーの発生したファイル名
	int    $errline [in] エラーの発生した行番号

戻り値:
	なし
----------------------------------------------------------------------------*/
function error_handler_dwd($errno, $errstr, $errfile, $errline, $errcontext) {
	if (error_reporting() == 0) return;
	if (preg_match('|/htdocs/.*wp-.*|', $errfile)) return; // WordPressのエラーはスルー
	if ($errno == E_DEPRECATED || $errno == E_STRICT) return;
	
	while (@ob_end_clean());
	ob_start('mb_output_handler');
	include('error.php');
//	flush();
	exit(0);

}

// エラー通知の設定を読み込む
function load_error_ini() {
	static $cache = NULL;
	if (is_null($cache)) {
		$env = array(
			'display_debug_trace'	=> 0,
			'mail_send'		=> 1,
			'project_name'		=> '',
			'mail_to'		=> 'error@office.sunatmark.co.jp',
			'mail_from'		=> 'null@sunatmark.co.jp',
		);
		foreach (explode(PATH_SEPARATOR, get_include_path()) as $path) {
			$inifile = "$path/error.ini";
			if (file_exists($inifile))
				$env = array_merge($env, parse_ini_file($inifile));
		}
		$cache = $env;
	}
	return $cache;
}

//error_reporting(E_ALL|E_STRICT);
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

set_error_handler('error_handler_dwd');

// 条件をチェックしてエラーを発生させる。
function ifthrow($expr, $message, $errno = E_USER_ERROR) {
	if ($expr) trigger_error($message, $errno);
}
// 式$expr が真になる場合にエラーページを出力する。
function iferror($expr, $title, $message, $return = NULL) {
	if ($expr) error($title, $message, $return);
}

/*----------------------------------------------------------------------------
機能概要: エラー表示
機能説明:
	クライアント端末別のエラー画面を出力する
引数:
	名前	(I/O) 説明
	$title	(I) エラータイトル
	$msg	(I) エラーメッセージ
	$return	(I) 戻り先URL
戻り値: なし
----------------------------------------------------------------------------*/
function error($title, $msg, $return = null) {
	$tmpl = 'error.html';
	$tmpl_arr = array(
		'title'		 => $title,
		'message'	 => $msg,
		'return'	 => $return
	);
	$temp = new HTMLTemplate($tmpl) ;
	print $temp->replace($tmpl_arr) ;
	exit;
}



endif?>
