<?php declare(encoding = 'UTF-8');
if (!defined('SESSION_CURRENT_CLASS_INC')): define('SESSION_CURRENT_CLASS_INC', 1);

/* ---------------------------------------------------------------
カレントセッションクラス

--------------------------------------------------------------- */
class SessionCurrent extends Session {
	/*----------------------------------------------------------------------------
	機能概要: コンストラクタ
	機能説明:
	引数:	カレントファンクションの名前
	戻り値:	なし
	----------------------------------------------------------------------------*/
	function SessionCurrent($cur_func) {
		parent::Session($cur_func);
		parent::clearSessionCascade($cur_func);
		if (!array_key_exists($this->cur_func, $_SESSION)) $_SESSION[$this->cur_func] = array();
		if (is_null($this->vars)) $this->vars = $_SESSION[$this->cur_func];
	}
};

endif/* SESSION_CURRENT_CLASS_INC */?>
