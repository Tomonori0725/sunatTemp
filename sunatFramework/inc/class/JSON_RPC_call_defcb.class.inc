<?php if (!defined('JSON_RPC_CALL_DEFCB_CLASS_INC')) { define('JSON_RPC_CALL_DEFCB_CLASS_INC', 1);
/*****************************************************************************
This is non public. 無断転載,無断使用を禁ず
Copyright (C) 2009. Sunatmark Co,.Ltd. All Right Reserved.

ファイル概要: JSON_RPCリクエスト結果標準コールバッククラス

$Id$
*****************************************************************************/

/*----------------------------------------------------------------------------
機能概要: JSON_RPCクライアントのデフォルトコールバッククラス
機能説明:
	JSON_RPCクライアントのデフォルトコールバッククラス
	成功または失敗の情報をバッファリングし、エラーがあった場合に
	メール通知等の処理を行う
----------------------------------------------------------------------------*/
class JSON_RPC_call_defcb {
	
	protected $jr;
	protected $results;
	protected $has_error;
	
	/*----------------------------------------------------------------------------
	機能概要: コンストラクタ
	機能説明:
	引数:
		$jr		(I) 
		$disp_error	(I) 

	戻り値:
	----------------------------------------------------------------------------*/
	public function __construct($jr) {
		$this->jr = $jr;
		$this->results = array();
		$this->has_error = false;
	}

	/*----------------------------------------------------------------------------
	機能概要: 処理成功の情報をバッファする
	機能説明: 処理成功の情報をバッファする
	引数:
		名前 (I/O) 説明
		$id	(I) ID
		$result	(I) 結果（戻り値）
	戻り値:
		なし
	----------------------------------------------------------------------------*/
	public function success($id, $result) {
		$this->results[$id] = array('status' => 1, 'result' => $result);
	}
	public function s($id, $result) {
		$this->success($id, $result);
	}
	
	/*----------------------------------------------------------------------------
	機能概要: 処理失敗の情報をバッファする
	機能説明: 処理失敗の情報をバッファする
	引数:
		名前 (I/O) 説明
		$id	(I) ID
		$result	(I) 結果（戻り値）
	戻り値:
		なし
	----------------------------------------------------------------------------*/
	public function exception($id, $error) {
		$this->results[$id] = array('status' => 0, 'error' => $error);
		$this->has_error = true;
	}
	public function e($id, $error) {
		$this->exception($id, $error);
	}
	
	/*----------------------------------------------------------------------------
	機能概要: 結果を出力する
	機能説明: 結果を出力する
	引数:
		なし
	戻り値:
		なし
	----------------------------------------------------------------------------*/
	public function report() {
		$requests	= $this->jr->get_requests();
		$hostname	= $this->jr->get_last_requested_host();
		if ($this->has_error) {
			$env = new Env;
			$conf = $env->rpc_error;
			if (is_null($conf)) return;
			
			while (@ob_end_clean());
			ob_start('mb_output_handler');
			
			$_GLOBALS['JSON_RPC_RESULT'] = $this->results;
			$_GLOBALS['JSON_RPC_REQUEST'] = $requests;
			
			// 画面表示
			echo <<<DEBUG
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>JSON-RPC 内部エラー</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body>

<p>JSON-RPCによるサブシステム側処理の実行中に内部エラーが発生しました。</p>
<p>以下は補足情報です。</p>

<table border="1">
<tr>
  <td>呼び出し元ホスト</td>
  <td>{$_SERVER['SERVER_NAME']}</td>
</tr>
<tr>
  <td>呼び出し元スクリプト名</td>
  <td>{$_SERVER['SCRIPT_NAME']}</td>
</tr>
<tr>
  <td>呼び出し先ホスト</td>
  <td>$hostname</td>
</tr>
</table>
<br />

<table border="1">
<tr>
  <td>ID</td>
  <td>関数名</td>
  <td>引数</td>
  <td>実行結果</td>
  <td>戻り値</td>
</tr>

DEBUG;

$status_arr = array(-1 => '', 0 => '失敗', 1 => '成功');
foreach ($_GLOBALS['JSON_RPC_REQUEST'] as $call_func) {
	if (array_key_exists($call_func['id'], $_GLOBALS['JSON_RPC_RESULT']))
		$result = $_GLOBALS['JSON_RPC_RESULT'][$call_func['id']];
	else
		$result = array('status' => -1, 'result' => null);
	
  echo "
    <tr>
      <td>{$call_func['id']}</td>
      <td>{$call_func['method']}</td>
      <td>";
      if (array_key_exists('params', $call_func)) var_dump($call_func['params']);
  echo "
      &nbsp;</td>
      <td>{$status_arr[$result['status']]}</td>
      <td>";
      
      if ($result['status'] == 0) {
      	echo $result['error']->code;
	echo '<br />';
	echo $result['error']->message;
      }
      elseif ($result['status'] == 1) {
      	var_dump($result['result']);
      }
      
  echo "
      </td>
    </tr>
  ";
}
			echo <<<DEBUG
</table>
<br />
DEBUG;
			echo('<p>$_SERVER</p>');
			var_dump($_SERVER);
			echo('<p>$_GLOBALS</p>');
			var_dump($_GLOBALS);
			echo <<<DEBUG
<br />
</body>
</html>

DEBUG;
			
			// メール送信
			if ($conf->mailto && $conf->mailfrom && $conf->mailtitle) {
				mb_send_mail(
					$conf->mailto,
					$conf->mailtitle,
					ob_get_contents(),
					"From: ".$conf->mailfrom."\nContent-Type: text/html;charset=ISO-2022-JP"
				);
			}
			
			// 画面出力
			if ($conf->display_error == 1) {
				while (@ob_end_clean());
				readfile('internal_error.html', true);
				exit;
			}
			elseif ($conf->display_error == 2) {
				ob_end_flush();
				exit;
			}
			else {
				while (@ob_end_clean());
			}
		}
	}

	/*----------------------------------------------------------------------------
	機能概要: エラーがあったか
	機能説明: エラーがあったか
	引数:
		なし
	戻り値:
		$this->has_error
	----------------------------------------------------------------------------*/
	public function has_error() {
		return $this->has_error;
	}
	

}

}?>
