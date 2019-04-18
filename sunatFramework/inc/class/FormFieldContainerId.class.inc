<?php declare(encoding = 'UTF-8');
/*----------------------------------------------------------------------------
機能概要: コンテナIDの入力チェック
機能説明:
	コンテナIDの入力をチェックするクラス

引数:
	名前 (I/O) 説明
	$flag 	 	(I) 必須かどうかなどのフラグ
	$db		(I) dbオブジェクト

戻り値:
	エラーがひとつでも見つかれば true 、見つからなければ false
----------------------------------------------------------------------------*/
if (!defined('FORM_FIELD_CONTAIENR_ID_CLASS_INC')): define('FORM_FIELD_CONTAIENR_ID_CLASS_INC', 1);

class FormFieldContainerId extends FormFieldString {
	private $db;

	public function __construct($flags = 0, db $db = NULL) {
		$this->db = $db;
		parent::__construct($flags, '/^\d{7}$/');
	}
	
	public function check_value($value, array &$errors) {
		parent::check_value($value, $errors);
		if (!$errors && !is_null($value) && $value == '00000000') $errors['bad_format'] = TRUE; 
		if (!$errors && !is_null($this->db)) {
			list($id) = $this->db->selectrow_array( "SELECT id FROM container WHERE id = ?", $value );
			if (!$errors && $id == 0) $errors['not_regist'] = TRUE;
		}
	}
};

endif/* FORM_FIELD_CONTAIENR_ID_CLASS_INC */?>
