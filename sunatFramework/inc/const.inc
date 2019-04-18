<?php if (!defined('CONST_INC')): define('CONST_INC', 1);
/*****************************************************************************
This is non public. 無断転載,無断使用を禁ず
Copyright (C) 2011. Sunatmark Co,.Ltd. All Right Reserved.

ファイル概要: 定義ファイル

$Id:$
*****************************************************************************/

//define('PHP_CLI_PATH', '/usr/bin/php');

define('DEFAULT_USER_AGENT', 'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0)');

define('PROGRESS_BAR_WIDTH', 960);

define('INDEED_PARSER_ALERT_MAIL_TITLE', '【indeed】indeedデータ取得・パースエラー');
define('PRIMITIVE_MATCH_ERROR_MSG', '【緊急】正規表現による初期の解析に失敗した可能性があります。');
define('COUNT_MATCH_ERROR_MSG', '【緊急】正規表現による検索結果件数の取得に失敗した可能性があります。');
define('PAGER_MATCH_ERROR_MSG', '【緊急】正規表現によるページ繰り部の解析に失敗した可能性があります。');

// 検索結果の上限
define('MAX_RESULT', 1000);

define('ITOWN_PARSER_ALERT_MAIL_TITLE', '【itown】itownデータ取得・パースエラー');
define('ITOWN_MAX_RESULT', 100);
define('BOOK_SEARCH_MAX_RESULT', 3000);	//電話帳ﾘｱﾙﾀｲﾑ検索

//バックエンドレスポンスエラー
define('REQUEST_ERROR_ALERT_MAIL_TITLE', 'バックエンドサーバレスポンスエラー');



// バックエンドサーバ情報のキャッシュファイル
$root_dir = dirname(dirname(__FILE__));
define('CACHE_BACKEND_SERVER_LIST', $root_dir.'/cache/backend_server_list.dat');

// リダイレクト先（情報取得元のもともとのURL）を取得するためのURL
define('INDEED_REDIRECTER_URL_PATTERN', 'http://jp.indeed.com/rc/clk?jk=%s');

// INDEED内の仕事情報
define('INDEED_INSIDE_URL_PATTERN', 'http://jp.indeed.com/%%E4%%BB%%95%%E4%%BA%%8B?jk=%s');

// クローラーを途中終了させるまでの時間（RPCで読み込みが途絶えてから）
// 短くすると「通信が途絶えた可能性があるため、途中終了しました」になる可能性が高くなる。
define('CRAWLER_ABORT_SECONDS', 60);

/** PEAR::DBのデバッグモード */
define('PEAR_DB_DEBUG', 0);
/** PEAR::DBの持続的接続オプション */
define('PEAR_DB_PERSISTENT', false);


define('PAGER_MAX_PAGE', 12);	//ページ繰りの件数
define('PAGER_MAX_RECORD', 50);	//1ページの表示件数

// 利用端末
define('CLIENT_TYPE_PC', 1);		// PC
define('CLIENT_TYPE_SP', 2);		// スマフォ
define('CLIENT_TYPE_IPHONE', 3);	// iPhone



// 電話帳リアルタイム検索検索条件保存先
$root_dir = dirname(dirname(__FILE__));
define('BOOK_SEARCH_DIR', $root_dir.'/book_search/');
define('BOOK_SEARCH_TEMP_DIR', $root_dir.'/book_search_temp/');


endif/* CONST_INC */?>
