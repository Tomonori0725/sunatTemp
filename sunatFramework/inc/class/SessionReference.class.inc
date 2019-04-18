<?php declare(encoding = 'UTF-8');
if (!defined('SESSION_REFERENCE_CLASS_INC')): define('SESSION_REFERENCE_CLASS_INC', 1);

/* ---------------------------------------------------------------
参照セッションクラス

--------------------------------------------------------------- */
class SessionReference extends Session {

	const MUST  = 1;
	const INIT  = 2;
	const IDENT = 3;
	
	/*----------------------------------------------------------------------------
	機能概要: コンストラクタ
	機能説明:
	引数:	カレントファンクションの名前
	戻り値:	なし
	----------------------------------------------------------------------------*/
	function SessionReference($cur_func, $construct_mode) {
		parent::Session($cur_func);
		if (!array_key_exists($cur_func, $_SESSION)) {
			if ($construct_mode == self::MUST)
				trigger_error('セッションが存在しません', E_USER_ERROR);
			elseif ($construct_mode == self::INIT)
				$_SESSION[$cur_func] = array();
		}
	}
};

endif/* SESSION_REFERENCE_CLASS_INC */?>
