<?php if (!defined('JSON_RPC_CLASS_INC')): define('JSON_RPC_CLASS_INC', 1);
/*****************************************************************************
  This is non public. 無断転載,無断使用を禁ず
  Copyright (C) 2009. SUNATAMRK Co.,Ltd. All Right Reserved.

ファイル概要:
  JSON_RPCサーバとその関連クラス、サブルーチン

$Id$
*****************************************************************************/

$json_rpc_execute_notification = false;
$json_rpc_current_proc_id = null;

/*----------------------------------------------------------------------------
機能概要: JSON_RPCサーバ
機能説明: 汎用のPHP JSON_RPC2.0サーバ
----------------------------------------------------------------------------*/
class JSON_RPC {

	protected $commands;
	protected $return_when_error;

	/*----------------------------------------------------------------------------
	機能概要: コンストラクタ
	機能説明:
	引数:
		なし
	戻り値:
		なし
	----------------------------------------------------------------------------*/
	public function __construct($return_when_error = false) {
		$this->return_when_error = $return_when_error;

		// リクエストチェック・リクエストボディ受け取り
		$accept_content_type = array(
			'application/json-rpc', 'application/json', 'application/jsonrequest'
		);
		$bad_content_type = true;
		if (array_key_exists('CONTENT_TYPE', $_SERVER)) {
			foreach ($accept_content_type as $type) {
				if (strpos($_SERVER['CONTENT_TYPE'], $type) !== false) {
					$bad_content_type = false;
					break;
				}
			}
		}
		if ($bad_content_type) $this->json_rpc_response(null, 415);
		if (!array_key_exists('CONTENT_LENGTH', $_SERVER) || !$_SERVER['CONTENT_LENGTH'])
			$this->json_rpc_response(null, 411);
		if (!array_key_exists('HTTP_ACCEPT', $_SERVER) || !$_SERVER['HTTP_ACCEPT'])
			$this->json_rpc_response(null, 400);

		// content取得、decode
		$in_msg = file_get_contents("php://input");
		$this->commands = json_decode($in_msg);

		if (is_null($this->commands) || (!is_array($this->commands) && get_class($this->commands) !== 'stdClass')) {
			// パースエラー
			$ret = array(
				'jsonrpc'	=> '2.0',
				'error'		=> array(
					'code'		=> -32700,
					'message'	=> 'Parse error.'
				),
				'id'		=> null
			);
			$this->json_rpc_response($ret, 500);
		}

	}


	/*----------------------------------------------------------------------------
	機能概要: リクエスト処理
	機能説明:
		リクエストを受けて、リクエスト内容のチェックや、
		プロシージャ処理の呼び出し、レスポンス処理の呼び出しを行う
	引数:
		なし
	戻り値:
		JSON-RPCのレスポンス
	----------------------------------------------------------------------------*/
	public function proc() {
		// プロシージャコール
		if (!is_array($this->commands) && get_class($this->commands) === 'stdClass') {
			// 単一
			$content = null;
			$status = null;
			try {
				$func_ret = $this->json_rpc_handler($this->commands);
			}
			catch (JsonRpcException $e) {
				$func_ret = $e->getContents();
			}
			if (!is_null($func_ret[0])) $content = $func_ret[0];
			$status = $func_ret[1];

		}
		else {
			// バッチ
			$content = array();
			$status_arr = array();
			foreach ($this->commands as $call_obj) {
				$err = false;
				try {
					$func_ret = $this->json_rpc_handler($call_obj);
				}
				catch (JsonRpcException $e) {
					$func_ret = $e->getContents();
					$err = true;
				}
				if (!is_null($func_ret[0])) $content[] = $func_ret[0];
				$status_arr[$func_ret[1]] = 1;

				// エラー時Returnのフラグがたっていたら、LOOPを抜ける
				if ($this->return_when_error && $err) break;
			}

			// ステータスを取得
			if (count($status_arr) == 1)
				$status = array_shift($status_arr);
			elseif (count($status_arr) == 2 && array_key_exists(204, $status_arr)) {
				// 204(No Content) と何かなら、No Contentではないので、「何か」を
				unset($status_arr[204]);
				$status = array_shift($status_arr);
			}
			else
				$status = 207;

			if (count($content) == 0) $content = null;

		}

		// レスポンス
		$this->json_rpc_response($content, $status);
	}


	/*----------------------------------------------------------------------------
	機能概要: プロシージャ1件を実行する
	機能説明: プロシージャ1件を実行する
	引数:
		$call_obj	(I) メソッド名、idなどをメンバとしたstdClass
				    (あえてプロトタイプ宣言は行わない)
	戻り値:
		なし
	----------------------------------------------------------------------------*/
	protected function json_rpc_handler($call_obj) {
		global $json_rpc_current_proc_id, $json_rpc_execute_notification;

		// エラーハンドラ用に、Global変数に現在実行中のidを格納する
		if (property_exists($call_obj, 'id')) {
			$json_rpc_current_proc_id = $call_obj->id;
			$json_rpc_execute_notification = false;
		}
		else {
			$json_rpc_current_proc_id = null;
			$json_rpc_execute_notification = true;
		}

		if (get_class($call_obj) !== 'stdClass') {
			return json_rpc_error(-32600, $call_obj);
		}
		elseif (!property_exists($call_obj, 'method') || !is_string($call_obj->method)) {
			return json_rpc_error(-32600, $call_obj);
		}
		elseif (!function_exists($call_obj->method)) {
			return json_rpc_error(-32601, $call_obj);
		}

		$params = array();
		if (property_exists($call_obj, 'params')) {
			$params = $call_obj->params;
		}

		$func_ret = call_user_func_array($call_obj->method, $params);
		if (property_exists($call_obj, 'id')) {
			return array(
				array(
					'jsonrpc'	=> '2.0',
					'result'	=> $func_ret,
					'id'		=> $call_obj->id
				),
				200
			);
		}
		else {
			// Notification
			return array(null, 204);
		}

	}


	/*----------------------------------------------------------------------------
	機能概要: レスポンス処理
	機能説明:
		処理結果からレスポンスヘッダを生成し、
		本文にJSON形式にした処理結果を出力する
	引数:
		$content	(I) 処理結果の配列またはstdClass
		$status		(I) ステータス
	戻り値:
		なし（標準出力を行う）
	----------------------------------------------------------------------------*/
	protected function json_rpc_response($content, $status) {
		$status_header = '';
		if ($status == 200)
			$status_header = 'HTTP/1.1 200 OK';
		elseif ($status == 204)
			$status_header = 'HTTP/1.1 204 No Content';
		elseif ($status == 207)
			$status_header = 'HTTP/1.1 207 Multi-Status';
		elseif ($status == 400)
			$status_header = 'HTTP/1.1 400 Bad Request';
		elseif ($status == 404)
			$status_header = 'HTTP/1.1 404 Not Found';
		elseif ($status == 411)
			$status_header = 'HTTP/1.1 411 Length Required';
		elseif ($status == 415)
			$status_header = 'HTTP/1.1 415 Unsupported Media Type';
		elseif ($status == 500)
			$status_header = 'HTTP/1.1 500 Internal Server Error';

		header('Content-type: application/json');
		header($status_header);
		if (!is_null($content)) echo json_encode($content);
		exit;
	}

}


/*----------------------------------------------------------------------------
機能概要: JSON_RPC例外
機能説明: JSON_RPC例外クラス
----------------------------------------------------------------------------*/
class JsonRpcException extends Exception {
	protected $proc_id;
	protected $is_notification;

	public function __construct($proc_id, $is_notification) {
		$this->proc_id = $proc_id;
		$this->is_notification = $is_notification;
		parent::__construct();
	}

	public function getContents() {
		if ($this->is_notification) {
			// notification
			$conctent = null;
			$status = 204;
		}
		else {
			$conctent = array(
				'jsonrpc'	=> '2.0',
				'error'		=> array(
					'code'		=> -32603,
					'message'	=> 'Internal error.'
				),
				'id' => $this->proc_id
			);
			$status = 500;
		}
		return array($conctent, $status);
	}
}


/*----------------------------------------------------------------------------
機能概要: JSON_RPCのリクエストエラー処理
機能説明: JSON_RPCのリクエストエラーを処理する
引数:
	$status		(I) エラーステータス
	$call_obj	(I) メソッド名などを格納したstdClassオブジェクト
戻り値:
	エラー配列（JSON形式ではない）とHTTPステータスの配列
----------------------------------------------------------------------------*/
function json_rpc_error($status, stdClass $call_obj) {
	$errs = array(
		-32600 => array(400, 'Invalid Request.'),
		-32601 => array(404, 'Procedure not found.'),
	);

	$ret_id = null;
	if ($status != -32600) $ret_id = $call_obj->id;

	$ret = null;
	$http_status = null;
	if (property_exists($call_obj, 'id')) {
		$ret = array(
			'jsonrpc'	=> '2.0',
			'error'		=> array(
				'code'		=> $status,
				'message'	=> $errs[$status][1]
			),
			'id' => $ret_id
		);
		$http_status = $errs[$status][1];
	}

	return array($ret, $http_status);

}


/*----------------------------------------------------------------------------
機能概要: 内部エラーハンドラ
機能説明: JSON-RPCの処理中に発生した内部エラーをハンドリングする
引数:
	set_error_handler によるcallback定義に準じる
戻り値:
	なし
----------------------------------------------------------------------------*/
function json_rpc_internal_error($errno, $errstr, $errfile, $errline, $errcontext) {
	global $json_rpc_current_proc_id, $json_rpc_execute_notification;
	if (error_reporting() == 0) return;
	if ($errno == E_DEPRECATED || $errno == E_STRICT) return;

	while (@ob_end_clean());
	ob_start('mb_output_handler');
	include('error.php');

	while (@ob_end_clean());
	throw new JsonRpcException($json_rpc_current_proc_id, $json_rpc_execute_notification);
}

error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
set_error_handler('json_rpc_internal_error');


endif?>
