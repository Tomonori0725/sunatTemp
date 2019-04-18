<?php if (!defined('JSON_RPC_MAIN_CLASS_INC')): define('JSON_RPC_MAIN_CLASS_INC', 1);
/*****************************************************************************
  This is non public. 無断転載,無断使用を禁ず
  Copyright (C) 2009. SUNATAMRK Co.,Ltd. All Right Reserved.

ファイル概要:
  メインシステム用 JSON_RPCサーバクラス

$Id: JSON_RPC_main.class.inc,v 1.1 2010/06/24 06:45:13 nishioka Exp $
*****************************************************************************/

/*----------------------------------------------------------------------------
クラス概要: メインシステム用JSON_RPCサーバ
クラス説明:
	メインシステム用JSON_RPCサーバ
	メインシステムのインターフェイスであるWEBページ間とのやり取りをするサーバ
----------------------------------------------------------------------------*/
class JSON_RPC_main extends JSON_RPC {
	
	/*----------------------------------------------------------------------------
	機能概要: コンストラクタ
	機能説明:
	引数:
		なし
	戻り値:
		なし
	----------------------------------------------------------------------------*/
	public function __construct() {
		if ($_SERVER['REQUEST_METHOD'] == 'GET') {
			$funcs = get_defined_functions();
			$me = $_SERVER['PHP_SELF'];
			header('HTTP/1.1 200 OK');
			header('Content-Type: text/javascript; charset=UTF-8');
			foreach ($funcs['user'] as $function_name) {
				if (preg_match('/^rpc_(.*)$/', $function_name, $m)) {
					list(, $name) = $m;
					echo "function $name\$() { return JSON.RPC.call_async('$me', '$name', arguments); }\r\n";
					echo "function $name\$\$() { return JSON.RPC.direct_call_async('$me', '$name', arguments); }\r\n";
				}
			}
			exit;
		}
		elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
			parent::__construct();
		}
		
	}

	/*----------------------------------------------------------------------------
	機能概要: プロシージャ1件を実行する
	機能説明: プロシージャ1件を実行する
	引数:
		$call_obj	(I) メソッド名、idなどをメンバとしたstdClass
				    (あえてプロトタイプ宣言は行わない)
	戻り値:
		JSON-RPC形式での成功または失敗のレスポンス
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
		elseif (!function_exists('rpc_'.$call_obj->method)) {
			return json_rpc_error(-32601, $call_obj);
		}
		
		$params = array();
		if (property_exists($call_obj, 'params')) {
			$params = $call_obj->params;
		}
		
		$func_ret = call_user_func_array('rpc_'.$call_obj->method, $params);
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

}

endif?>
