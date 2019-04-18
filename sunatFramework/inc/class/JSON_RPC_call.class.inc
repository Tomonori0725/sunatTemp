<?php if (!defined('JSON_RPC_CALL_CLASS_INC')) { define('JSON_RPC_CALL_CLASS_INC', 1);
/*****************************************************************************
This is non public. 無断転載,無断使用を禁ず
Copyright (C) 2009. Sunatmark Co,.Ltd. All Right Reserved.

ファイル概要: JSON_RPCリクエストクラス

$Id$
*****************************************************************************/

/*----------------------------------------------------------------------------
クラス概要: JSON_RPCコールクラス
クラス説明:
	PHPでJSON_RPCリクエストを行うためのクラス
----------------------------------------------------------------------------*/
class JSON_RPC_call extends ErrorReporting {
	
	protected $call_id;
	protected $requests;
	protected $success_callbacks;
	protected $error_callbacks;
	protected $last_requested_host;
	
	/*----------------------------------------------------------------------------
	機能概要: コンストラクタ
	機能説明:
	引数:
		なし
	戻り値:
		なし
	----------------------------------------------------------------------------*/
	public function __construct() {
		$this->init();
	}

	/*----------------------------------------------------------------------------
	機能概要: 初期化する
	機能説明:
	引数:
		なし
	戻り値:
		なし
	----------------------------------------------------------------------------*/
	public function init() {
		$this->json_rpc_call_id = 0;
		$this->requests = array();
		$this->success_callbacks = array();
		$this->exception_callbacks = array();
		$this->last_requested_host = null;
	}
	
	/*----------------------------------------------------------------------------
	機能概要: 関数呼び出しの設定を追加する
	機能説明: 呼び出す関数への引数を、引数に並べた形で関数呼び出しの設定を追加する
	
	引数:
		名前 (I/O) 説明
		$function		(I) コールする関数
		$success_callback	(I) 処理成功時のcallback関数
		$exception_callback	(I) 処理失敗時のcallback関数
		[,$args]...		(I) 引数
			
	戻り値:
		なし
	----------------------------------------------------------------------------*/
	public function add() {
		$args = func_get_args();
		$function = array_shift($args);
		$success_callback = array_shift($args);
		$exception_callback = array_shift($args);
		$params = null;
		if (count($args) > 0) $params = $args;
		$id = $this->set_request($function, $params, false);
		$this->success_callbacks[$id] = $success_callback;
		$this->exception_callbacks[$id] = $exception_callback;
	}
	
	
	/*----------------------------------------------------------------------------
	機能概要: 関数呼び出しの設定を追加する
	機能説明: 呼び出す関数への引数を、配列にした形で関数呼び出しの設定を追加する
	
	引数:
		名前 (I/O) 説明
		$function		(I) コールする関数
		$success_callback	(I) 処理成功時のcallback関数
		$exception_callback	(I) 処理失敗時のcallback関数
		[,$args]		(I) 引数（配列/省略可）
			
	戻り値:
		なし
	----------------------------------------------------------------------------*/
	public function add_array($function, $success_callback, $exception_callback, $params = null) {
		$id = $this->set_request($function, $params, false);
		$this->success_callbacks[$id] = $success_callback;
		$this->exception_callbacks[$id] = $exception_callback;
	}
	
	
	/*----------------------------------------------------------------------------
	機能概要: notificationとしての関数呼び出しの設定を追加する
	機能説明: 呼び出す関数への引数を、引数に並べた形で関数呼び出しの設定を追加する
	
	引数:
		名前 (I/O) 説明
		$function	(I) コールする関数
		[,$args]...	(I) 引数
			
	戻り値:
		なし
	----------------------------------------------------------------------------*/
	public function add_notification() {
		$args = func_get_args();
		$function = array_shift($args);
		$params = null;
		if (count($args) > 0) $params = $args;
		$this->set_request($function, $params, true);
	}
	
	
	/*----------------------------------------------------------------------------
	機能概要: notificationとしての関数呼び出しの設定を追加する
	機能説明: 呼び出す関数への引数を、配列にした形で関数呼び出しの設定を追加する
	
	引数:
		名前 (I/O) 説明
		$function	(I) コールする関数
		[,$args]	(I) 引数（配列/省略可）
			
	戻り値:
		なし
	----------------------------------------------------------------------------*/
	public function add_notification_array($function, $params = null) {
		$this->set_request($function, $params, true);
	}
	
	
	/*----------------------------------------------------------------------------
	機能概要: 呼び出しりストを追加する
	機能説明:
	
	引数:
		名前 (I/O) 説明
		$function	(I) ホスト
		$params		(I) コールする関数
		$notification	(I) 引数の配列
			
	戻り値:
		$id	idがセットされた場合にはid、NOTIFICATIONの場合はNULL
		
	----------------------------------------------------------------------------*/
	protected function set_request($function, $params, $notification) {
		$request_param_arr = array(
			'jsonrpc'	=> '2.0',
			'method'	=> $function
		);
		if ($notification) {
			$id = NULL;
		}
		else {
			$id = ++$this->call_id;
			$request_param_arr['id'] = $id;
		}
		if (!is_null($params)) $request_param_arr['params'] = $params;
		$this->requests[] = $request_param_arr;
		return $id;
	}
	
	/*----------------------------------------------------------------------------
	機能概要: JSON-RPCリクエストを行う
	機能説明: JSON-RPCリクエストを行う
	
	引数:
		名前 (I/O) 説明
		$host		(I) リクエスト先ホスト
		$socket		(I) リクエスト先ソケット
		$resource	(I) リクエスト先リソース
		$context_opts	(I) コンテキストストリームのオプション
		$debug		(I) デバッグフラグ TRUEならレスポンスをそのまま出力する
		
	戻り値:
		サーバからのレスポンスボディ
		
	----------------------------------------------------------------------------*/
	public function call($host, $socket, $resource, array $context_opts = array(), $debug = false) {
		$this->last_requested_host = $host;
		if (count($this->requests) == 1)
			$request_string = json_encode($this->requests[0]);
		else
			$request_string = json_encode($this->requests);
		
		$headers = array(
			'Host: '.$host,
			'Content-Type: application/json-rpc',
			'Accept: application/json-rpc, application/json, application/jsonrequest',
			'Content-Length: '.strlen($request_string)
		);
		
		$opts = array(
			'http'=>array(
				'method'=>"POST",
				'header'=> "Content-Type: application/json-rpc\r\n
Accept: application/json-rpc, application/json, application/jsonrequest\r\n
Content-Length: ".strlen($request_string)
			)
		);
		$context = stream_context_create($opts);
		foreach ($context_opts as $opt) {
			stream_context_set_option($context, $opt[0], $opt[1], $opt[2]);
		}
		
		$fp = @stream_socket_client($socket, $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);
		$ret = '';
		if (!$fp) {
			trigger_error('ホスティングサーバに接続できませんでした。', E_USER_ERROR);
			exit;
		}
		else {
			fwrite($fp, "POST $resource HTTP/1.1\r\n");
			foreach ($headers as $header) {
				fputs($fp, $header."\r\n");
			}
			fwrite($fp, "\r\n");
			fwrite($fp, $request_string);
			$rets = array();
			while (!feof($fp)) {
				$ret .= fgets($fp, 4096);
			}
			fclose($fp);
			if ($debug) echo $ret;
		}

		$ret_arr = preg_split('/\r\n?\r\n?/', $ret, 2);
		if (count($ret_arr) < 2)
			trigger_error('サブシステムから正常なデータが返されませんでした。'.print_r($ret_arr, true), E_USER_ERROR);
		list($header, $body) = $ret_arr;
		$result = json_decode($body);
		
		// 結果処理用のcallback関数を呼ぶ
		if (is_array($result)) {
			foreach ($result as $res) $this->call_callback($res);
		}
		elseif ($result instanceof stdClass) {
			$this->call_callback($result);
		}
		
		return $result;
	}
	
	/*----------------------------------------------------------------------------
	機能概要: callback関数を呼ぶ
	機能説明: callback関数を呼ぶ
	引数:
		$res	(I) RPC1件に対する結果を格納した標準クラス
	戻り値:
		なし
	----------------------------------------------------------------------------*/
	function call_callback(stdClass $res) {
		if (property_exists($res, 'result')) {
			$cb = $this->success_callbacks[$res->id];
			if (!is_null($cb)) call_user_func($cb, $res->id, $res->result);
		}
		elseif (property_exists($res, 'error')) {
			$cb = $this->exception_callbacks[$res->id];
			if (!is_null($cb)) call_user_func($cb, $res->id, $res->error);
		}
	}
	
	/*----------------------------------------------------------------------------
	機能概要: プロパティ requests の内容を返す
	機能説明: プロパティ requests の内容を返す
	引数:
		なし
	戻り値:
		プロパティ requests の内容
	----------------------------------------------------------------------------*/
	public function get_requests() {
		return $this->requests;
	}

	/*----------------------------------------------------------------------------
	機能概要: プロパティ last_requested_host の内容を返す
	機能説明: プロパティ last_requested_host の内容を返す
	引数:
		なし
	戻り値:
		プロパティ last_requested_host の内容
	----------------------------------------------------------------------------*/
	public function get_last_requested_host() {
		return $this->last_requested_host;
	}
		
	/*----------------------------------------------------------------------------
	機能概要: エラー処理
	機能説明: エラー処理
	
	引数:
		名前 (I/O) 説明
		$errinfo	(I) エラー情報を格納した配列
		
	戻り値:
		なし
		
	----------------------------------------------------------------------------*/
	protected function errorAction(array $errinfo) {
		echo("ERROR!");
		var_dump($errinfo);
	}
	
}

}?>
