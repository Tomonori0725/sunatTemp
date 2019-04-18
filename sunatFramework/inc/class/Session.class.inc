<?php declare(encoding = 'UTF-8');
if (!defined('SESSION_CLASS_INC')): define('SESSION_CLASS_INC', 1);

$session_started = false;

/* ---------------------------------------------------------------
セッションクラス

--------------------------------------------------------------- */
class Session {

	protected $cur_func;
	protected $protect_sess_names = array();
	public $vars;
	const LOGIN_SESS_REGEX = '/^login_info/';
	const DEFAULT_SESSION = true; // true:started  false:unstarted
	
	/*----------------------------------------------------------------------------
	機能概要: コンストラクタ
	機能説明:
	引数:	カレントファンクションの名前
	戻り値:	なし
	----------------------------------------------------------------------------*/
	public function Session($cur_func) {
		global $session_started;
		$session_started = self::DEFAULT_SESSION;
		if (!$session_started) {
			session_start();
			$session_started = true;
		}
		$this->cur_func = $cur_func;
		if (array_key_exists($this->cur_func, $_SESSION))
			$this->vars = $_SESSION[$this->cur_func];
		else
			$this->vars = null;
		
		$this->protect_sess_names = array(
		);
	}
	
	/*----------------------------------------------------------------------------
	機能概要: 自セッション破棄
	機能説明: オブジェクト自身が参照するセッションの破棄
		
	引数:	なし
	戻り値:	なし
	----------------------------------------------------------------------------*/
	public function clearSessionThis() {
		if (array_key_exists($this->cur_func, $_SESSION)) unset($_SESSION[$this->cur_func]);
		$this->vars = null;
	}
	
	/*----------------------------------------------------------------------------
	機能概要: 自セッション書き込み
	機能説明: オブジェクト自身が参照するセッションの書き込み
		
	引数:	なし
	戻り値:	なし
	----------------------------------------------------------------------------*/
	public function writeSessionThis() {
		if (is_null($this->vars))
			unset($_SESSION[$this->cur_func]);
		else
			$_SESSION[$this->cur_func] = $this->vars;
	}
	
	/*----------------------------------------------------------------------------
	機能概要: デストラクタ
	機能説明: オブジェクト自身が参照するセッションの書き込み
		
	引数:	なし
	戻り値:	なし
	----------------------------------------------------------------------------*/
	public function __destruct() {
		$this->writeSessionThis();
	}
	
	/*----------------------------------------------------------------------------
	機能概要: セッションカスケード破棄
	機能説明: 指定された機能に対して共存させないセッションをカスケードで削除
		　スタティックコール可能
	引数:	機能名
	戻り値:	なし
	----------------------------------------------------------------------------*/
	public function clearSessionCascade($node_func_name) {
		global $session_started;
		$cur_func_layer = explode('.', $node_func_name);
		$cur_depth = count($cur_func_layer); // 何世代目か
		
		if (!$session_started) {
			session_start();
			logoutcheck_start();
			$session_started = true;
		}
		foreach (array_keys($_SESSION) as $comp_func) {
			if ($comp_func == $node_func_name ||
			    preg_match(self::LOGIN_SESS_REGEX, $comp_func) ||
			    in_array($comp_func, $this->protect_sess_names)) continue;
			
			$comp_func_layer = explode('.', $comp_func);
			$comp_depth = count($comp_func_layer); // 何世代目か
			
			$same_depth = 0; // 何世代目まで同じか
			foreach ($comp_func_layer as $depth => $func_name) {
				if (array_key_exists($depth, $cur_func_layer) && $func_name == $cur_func_layer[$depth]) {
					$same_depth++;
				} else {
					break;
				}
			}
			
			if ($same_depth == $cur_depth) {
				// 自分の子孫→消す
				unset($_SESSION[$comp_func]);
			} elseif ($same_depth == ($cur_depth - 1) && $comp_depth > $same_depth) {
				// 兄弟もしくは兄弟の子孫→兄弟に「*」が付いていなければ消す
				if (substr($comp_func_layer[($cur_depth - 1)], 0, 1) != '*') {
					unset($_SESSION[$comp_func]);
				}
			}
		}
	}
};

endif/* SESSION_CLASS_INC */?>
