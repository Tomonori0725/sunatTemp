<?php declare(encoding = 'UTF-8');
/*----------------------------------------------------------------------------
機能概要: データベースの特定テーブルの特定カラムに、同じ値を持つものがないかチェックする
機能説明:
	データベースの特定テーブルの特定カラムに、同じ値を持つものがないかチェックする

引数:
	名前 (I/O) 説明
	$flag 	 	(I) 必須かどうかなどのフラグ
	$db		(I) dbオブジェクト

戻り値:
	エラーがひとつでも見つかれば true 、見つからなければ false
----------------------------------------------------------------------------*/
if (!defined('FORM_FIELD_STRING_DB_DUPLICATE_CLASS_INC')): define('FORM_FIELD_STRING_DB_DUPLICATE_CLASS_INC', 1);

class FormFieldStringDbDuplicate extends FormFieldString {
	
	protected $db;
	protected $table;
	protected $field;
	protected $add_cond;
	
	public function __construct($flags = 0, $pattern = NULL, $min = NULL, $max = NULL, db $db, $table, $field, array $add_cond = array()) {
		parent::__construct($flags, $pattern, $min, $max);
		$this->db			= $db;
		$this->table		= $table;
		$this->field		= $field;
		$this->add_cond		= $add_cond;
	}
	
	public function check_value($value, array &$errors) {
		parent::check_value($value, $errors);
		if (!$errors) {
			$sql = "SELECT COUNT(*) AS cnt FROM ".$this->table." WHERE ".$this->field." = ?";
			$params = array($value);
			foreach ($this->add_cond as $cond_info) {
				$sql .= " AND ".$cond_info[0]." ".$cond_info[1]." ?";
				$params[] = $cond_info[2];
			}
			list($cnt) = $this->db->selectrow_array($sql, $params);
			if ($cnt > 0) $errors['duplicate'] = TRUE;
		}
	}
};

endif/* FORM_FIELD_STRING_DB_DUPLICATE_CLASS_INC */?>
