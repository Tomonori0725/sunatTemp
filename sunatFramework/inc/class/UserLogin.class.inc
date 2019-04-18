<?php declare(encoding = 'utf-8');
if (!defined('USER_LOGIN_CLASS_INC')): define('USER_LOGIN_CLASS_INC', 1);
/*****************************************************************************
  This is non public. 無断転載,無断使用を禁ず
  Copyright (C) 2009. SUNATMARK Co.,Ltd. All Right Reserved.

ファイル概要:
 在庫管理システム ログインライブラリ

$Id$
*****************************************************************************/

// 読み込まれる順番によって session.inc または UserLogin で定義される定数
if (!defined('REFERER')) {
	define( 'REFERER',		'referer' ) ;
	define( 'REFERER2',		'referer2' ) ;
}

class UserLogin {

	// セッション(機能)名
	const LOGIN_INFO		= 'login_info' ;
	const REFERER			= '*login.*referer' ;
	const REFERER_SAVE_CNT	= 50 ;

	private static $_instance		= NULL ;
	protected $tpl_class			= NULL ;
	protected $tpl_values			= array();
	//protected $order_login_info_session	= NULL;

	/*----------------------------------------------------------------------------
	機能概要: コンストラクタ
	機能説明:
	引数:	なし
	戻り値:	なし
	----------------------------------------------------------------------------*/
	protected function __construct() {
		$this->tpl_class = 'HTMLTemplate';
		$this->setReferer();
	}

	/*----------------------------------------------------------------------------
	機能概要: セッションスタート
	機能説明:
	引数:	なし
	戻り値:	なし
	----------------------------------------------------------------------------*/
	public static function session_start() {
		session_start();
	}

	/*----------------------------------------------------------------------------
	機能概要: インスタンス取得
	機能説明:
	引数:	なし
	戻り値:	Userクラスのオブジェクト
	----------------------------------------------------------------------------*/
	protected static function getSelf() {
		return new self();
	}
	public static function getInstance() {
		if (is_null(self::$_instance)) {
			self::$_instance = static::getSelf();
		}
		return self::$_instance ;
	}


	/*----------------------------------------------------------------------------
	機能概要: ログイン情報配列チェック
	機能説明:
	引数:
	戻り値:	true:正常   false:不正
	----------------------------------------------------------------------------*/
	protected function LoginInfoCheck($account_type, $login_info) {
		$ret = true;
		return $ret;
	}


	/*----------------------------------------------------------------------------
	機能概要: アクセサメソッドのラッパー
	機能説明:
		ユーザ情報をダイレクトで取得する
		例: $user->loginid
	引数:	プロパティ名
	戻り値:	プロパティの値
	----------------------------------------------------------------------------*/
	public function __get( $name ) {
		if ( $this->isUserLogin()) {
			$uinfo = $this->getUserLoginInfo() ;
			return array_key_exists($name, $uinfo) ? $uinfo[$name] : NULL;
		}
		return NULL ;
	}


	/*----------------------------------------------------------------------------
	機能概要: アクセサメソッドのラッパー
	機能説明:
		ユーザ情報をダイレクトで設定する
		例: $user->loginid
	引数:	プロパティ名, 値
	戻り値:	void
	----------------------------------------------------------------------------*/
	public function __set( $name, $value ) {
		if ( $this->isUserLogin()) {
			$uinfo = $this->getUserLoginInfo() ;
			if (array_key_exists($name, $uinfo)) {
				$uinfo[$name] = $value;
				$_SESSION[self::LOGIN_INFO] = $uinfo;
			}
		}
		//return NULL ;
	}


	/*----------------------------------------------------------------------------
	機能概要: プロトコル取得
	機能説明: 現在の通信プロトコルを返す(http or https)

	引数:		無し

	戻り値:	string $proto
	----------------------------------------------------------------------------*/
	public static function GetProtocol() {
		return array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'] == 'on'? 'https://': 'http://';
	}


	/*----------------------------------------------------------------------------
	機能概要: カレントディレクトリのURL取得
	機能説明: 呼出したスクリプトのディレクトリのURLを取得

	引数:		無し

	戻り値:	string $cur_dir
	----------------------------------------------------------------------------*/
	public static function GetProjectName($with_sub_dir = 0) {
		$ret_elems = array();
		$elems = explode('/', $_SERVER['SCRIPT_NAME']);
		if (!is_array($elems) || count($elems) == 1) return '/';
		array_shift($elems);
		if ($elems[0] == 'mb') {
			array_shift($elems);
			$ret_elems[] = 'mb/';
		}
		if (isset($elems[0]) && in_array($elems[0], array('item', 'shop', 'admin'))) {
			$ret_elems[] = $elems[0].'/';
		}
		if ($with_sub_dir) {
			if (isset($elems[1]) && in_array($elems[1], array('cart', 'member'))) {
				$ret_elems[] = $elems[1].'/';
			}
		}
		return '/'.implode('', $ret_elems);
	}


	public static function GetDirectry() {
		return self::GetProtocol().$_SERVER['HTTP_HOST'].self::GetProjectName(1);
	}


	public static function GetBaseDirectry() {
		return self::GetProtocol().$_SERVER['HTTP_HOST'].self::GetProjectName(0);
	}


	/*----------------------------------------------------------------------------
	機能概要: SSL環境のサーバールートまたはプロジェクトのルートのURLを取得する
	機能説明: SSL環境のサーバールートまたはプロジェクトのルートのURLを取得する

	引数:	int  $with_project_name  1であればプロジェクトのルート、0であればサイトルートを返す

	戻り値:	string
	----------------------------------------------------------------------------*/
	public static function GetSSLBaseDirectry($with_project_name = 1) {
		if ($with_project_name)	$project_name = self::GetProjectName(0);
		else			$project_name = '';

		if (array_key_exists('HTTP_HOST', $_SERVER))		$host_info = $_SERVER['HTTP_HOST'];
		elseif (array_key_exists('SERVER_NAME', $_SERVER))	$host_info = $_SERVER['SERVER_NAME'];
		else $host_info = '';

		if (preg_match('/^(ssl\.)?([\w\-]+\.)?([\w\-\.]*dev\.wps\.jp)$/', $host_info, $ma))
			return 'http://ssl.'.$ma[2].$ma[3].$project_name;
		elseif ($host_info)
			return 'https://'.$host_info.$project_name;
		else
			return $project_name;
	}


	/*----------------------------------------------------------------------------
	機能概要: 非SSL環境のサーバールートまたはプロジェクトのルートのURLを取得する
	機能説明: 非SSL環境のサーバールートまたはプロジェクトのルートのURLを取得する

	引数:	int  $with_project_name  1であればプロジェクトのルート、0であればサイトルートを返す

	戻り値:	string
	----------------------------------------------------------------------------*/
	public static function GetNonSSLBaseDirectry($with_project_name = 1) {
		if ($with_project_name)	$project_name = self::GetProjectName(0);
		else			$project_name = '';

		if (array_key_exists('HTTP_HOST', $_SERVER))		$host_info = $_SERVER['HTTP_HOST'];
		elseif (array_key_exists('SERVER_NAME', $_SERVER))	$host_info = $_SERVER['SERVER_NAME'];
		else $host_info = '';

		if (preg_match('/^(ssl\.)?([\w\-\.]*dev\.wps\.jp)$/', $host_info, $ma))
			return 'http://'.$ma[2].$project_name;
		elseif ($host_info)
			return 'http://'.$host_info.$project_name;
		else
			return $project_name;
	}


	/*----------------------------------------------------------------------------
	機能概要: URLのプロトコル部とホスト名を取得
	機能説明:
		URLのプロトコル部とホスト部を返す。

	引数:
		名前 (I/O) 説明
		$ssl  (I) SSL祖接続

	戻り値:
		URLのプロトコル部とホスト部
	----------------------------------------------------------------------------*/
	public static function GetURLBase($ssl = FALSE) {
		if (preg_match('/^(ssl\.)?([\w-]+\.([\w-]+\.)?)dev\.wps\.jp$/', $_SERVER['HTTP_HOST'], $m)) {
			list(,, $user_proj) = $m;
			return $ssl?
				"http://ssl.{$user_proj}dev.wps.jp":
				"http://{$user_proj}dev.wps.jp";
		}
		$hostname = $_SERVER['HTTP_HOST'];

		return $ssl ? "https://$hostname": "http://$hostname";
	}


	/*----------------------------------------------------------------------------
	機能概要: リダイレクト
	機能説明:
		リダイレクト

	引数:
		名前 (I/O) 説明
		$uri	(I) URI
		$ssl	(I) SSL

	戻り値:
		なし
	----------------------------------------------------------------------------*/
	public static function redirect($uri, $ssl = NULL) {
		if (!preg_match('/^https?:\/\//', $uri)) {
			// 相対パス時は補完
			if (substr($uri, 0, 1) != '/') $uri = dirname($_SERVER['REQUEST_URI']).'/'.$uri;

			// Location: の引数は完全URIである必要がある。
			if (!preg_match('/^https?:\/\//', $uri)) {
				if (is_null($ssl))
					$ssl = preg_match('/\bssl\./', $_SERVER['HTTP_HOST']) != 0 || $_SERVER['SERVER_PORT'] == 443;
				$uri = self::GetURLBase($ssl).$uri;
			}
		}

		header( 'Location: ' . $uri ) ;
		exit ;
	}

	/*----------------------------------------------------------------------------
	機能概要: セキュアゾーン判定
	機能説明:
		セキュアゾーンかどうかを判定
	引数:	名前 	(I/O) 	説明
			url		(I)		string (parse_urlできる形式) 未入力時は現在のホスト
	戻り値:	bool TRUE:セキュア FALSE:非セキュア
	----------------------------------------------------------------------------*/
	public function isSecure( $url = NULL ) {

		$host = is_null( $url ) ? $_SERVER['HTTP_HOST'] : parse_url( $url, PHP_URL_HOST ) ;

		//if ( preg_match('/\bssl.demachi.wps.ne.jp$/', $host)) return TRUE;
		if (preg_match('/^(ssl\.)?([\w-]+\.([\w-]+\.)?)dev\.wps\.jp$/', $host, $m)) {
			list( , $ssl, $user_proj ) = $m ;
			return $ssl ? TRUE : FALSE ;
		}
		if (array_key_exists( 'HTTPS', $_SERVER ) && $_SERVER['HTTPS'] == 'on') {
			return TRUE ;
		}
		return FALSE ;
	}

	/*----------------------------------------------------------------------------
	機能概要: 初期化フィルタ
	機能説明:
	引数:   なし
	戻り値:	なし
	----------------------------------------------------------------------------*/
	public function initializeUser() {
	}


	/*----------------------------------------------------------------------------
	機能概要: ログインチェック
	機能説明:
		ログインしているかどうかを調べる。
		チェック内容はセッションにログイン情報があるかどうか。

	引数:	なし
	戻り値:	bool TRUE:ログイン済み(Auth,Auto) FALSE:未ログイン
	----------------------------------------------------------------------------*/
	public function isUserLogin() {
		$login = FALSE ;

		// ログインチェック
		if (array_key_exists( self::LOGIN_INFO, $_SESSION ) &&
		    is_array( $_SESSION[self::LOGIN_INFO]) &&
		    $_SESSION[self::LOGIN_INFO]) {
			$login = TRUE ;
		}

		return $login ;
	}

	/*----------------------------------------------------------------------------
	機能概要: ログイン情報取得
	機能説明:
		セッションのログイン情報を返す。
	引数:	なし
	戻り値:	array ログイン情報配列、内容はcheckUserLogin()参照
	----------------------------------------------------------------------------*/
	public function getUserLoginInfo() {
		assert( '$this->isUserLogin();'
			   .'//* "セッションにログイン情報がありません。');
		return $_SESSION[self::LOGIN_INFO];
	}

	/*----------------------------------------------------------------------------
	機能概要: ログイン情報破棄
	機能説明:
		ログイン情報を破棄する。
	引数:	名前 	(I/O) 	説明
			$uinfo	(I)		array ログイン情報配列
	戻り値:	なし
	----------------------------------------------------------------------------*/
	public function unsetUserLoginInfo() {
		$_SESSION[self::LOGIN_INFO] = array();
	}

	/*----------------------------------------------------------------------------
	機能概要: 戻り先取得
	機能説明:
		戻り先情報を返す
	引数:	$pattern_list	第一要素にパターンを格納、第二要素がTRUEなら許可パターン、
				FALSEなら不許可パターンとする
	戻り値:	初めに許可パターンにマッチしたURI
		引数を省略した場合は、最後にアクセスしたURI
	----------------------------------------------------------------------------*/
	public function getReferer(array $pattern_list = array()) {
		if (!array_key_exists(self::REFERER, $_SESSION)) $_SESSION[self::REFERER] = array();

		if (!$pattern_list) {
			if (isset($_SESSION[self::REFERER][1])) return $_SESSION[self::REFERER][1];
			else return $this->GetURLBase(FALSE).'/';
		}

		if (!is_array($pattern_list[0])) $pattern_list = array($pattern_list);
		$i = 1;
		$url = NULL;
		while (isset($_SESSION[self::REFERER][$i])) {
			foreach ($pattern_list as $pattern) {
				if (preg_match($pattern[0], $_SESSION[self::REFERER][$i])) {
					if ($pattern[1]) {
						// 許可パターンにマッチ
						return $_SESSION[self::REFERER][$i];
					}
					else {
						// 不許可パターンにマッチ
						break;
					}
				}
			}
			$i++;
		}

		return $this->GetURLBase(FALSE).'/';
	}

	/*----------------------------------------------------------------------------
	機能概要: 戻り先を保持しているか判定する
	機能説明:
	引数:	なし
	戻り値:	bool TRUE; 保持している FALSE: 保持していない
	----------------------------------------------------------------------------*/
	public function hasReferer() {
		return array_key_exists( self::REFERER, $_SESSION ) ;
	}

	/*----------------------------------------------------------------------------
	機能概要: 戻り先設定
	機能説明:
		戻り先をセッションに保存する
	引数:	名前 	(I/O) 	説明
			$uri	(I)		string parse_urlできるもの
	戻り値:	なし
	----------------------------------------------------------------------------*/
	public function setReferer($uri = NULL) {
		if (!is_null($uri)) assert( 'parse_url( $uri ); // 不正なURLです。' ) ;
		if (!array_key_exists(self::REFERER, $_SESSION)) $_SESSION[self::REFERER] = array();

		if (is_null($uri)) {
			if (array_key_exists('REQUEST_URI', $_SERVER))
				$uri = self::GetURLBase($this->isSecure()).$_SERVER['REQUEST_URI'];
			else
				return;
		}

		if (!isset($_SESSION[self::REFERER][0]) || $_SESSION[self::REFERER][0] != $uri) {
			if (array_unshift($_SESSION[self::REFERER], $uri) > self::REFERER_SAVE_CNT) {
				array_pop($_SESSION[self::REFERER]);
			}
		}
	}


	/*----------------------------------------------------------------------------
	機能概要: ログイン情報更新
	機能説明:
		ログイン情報を更新する
	引数:	なし
	戻り値:	なし
	----------------------------------------------------------------------------*/
	public function refreshLoginInfo(db $db, $update_last_login = false) {
		if ($this->isUserLogin()) {
			$this->setLoginInfo($db, $_SESSION[self::LOGIN_INFO]['member_id'], $update_last_login);
		}
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
	public function error($title, $msg, $return = null) {
		$tmpl_arr = array(
			'title'	  => $title,
			'message' => $msg,
			'return'  => $return
		);
		$temp = new HTMLTemplate('error.html') ;
		print $temp->replace($tmpl_arr) ;
		exit;
	}


	/*----------------------------------------------------------------------------
	機能概要: HTML取得・設定
	機能説明:
		テンプレートオブジェクト生成から、共通部品のデータ取得、HTML取得までを
		自動化する
	引数:	名前 		(I/O) 	説明
			$template	(I)		テンプレートファイルパス
			$values		(I)		出力用データ配列
	戻り値: string テンプレートから作成されたHTML
	----------------------------------------------------------------------------*/
	public function getHTML( $template, $values = array() ) {
		assert( 'is_string( $template ); //* テンプレートを指定してください' ) ;
		assert( 'is_array( $values ); //* 出力配列が不正です' ) ;

		$values[':HTTP_ROOT'] = self::GetURLBase().'/';
		$values[':HTTPS_ROOT'] = self::GetURLBase(true).'/';

		$this->tpl_values &= $values;
		$temp = new $this->tpl_class( $template ) ;

		return $temp->replace( $values ) ;
	}
	
	/*----------------------------------------------------------------------------
	機能概要: 電話帳リアルタイム検索用履歴保存ディレクトリの取得
	機能説明:
		電話帳リアルタイム検索用履歴保存ディレクトリを取得する
	引数:	名前 			(I/O) 	説明
			$favorite_id	(I)		電話帳リアルタイム検索お気に入り検索条件ID	
	戻り値: string 電話帳リアルタイムデータ保存ディレクトリパス
	----------------------------------------------------------------------------*/
	public function book_search_dir($favorite_id) {
		return BOOK_SEARCH_DIR . $this->id . '/' . $favorite_id;
	}
}

endif;?>
