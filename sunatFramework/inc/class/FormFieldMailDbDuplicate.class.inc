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
if (!defined('FORM_FIELD_MAIL_DB_DUPLICATE_CLASS_INC')): define('FORM_FIELD_MAIL_DB_DUPLICATE_CLASS_INC', 1);

class FormFieldMailDbDuplicate extends FormFieldMail {
	
	protected $db;
	protected $table;
	protected $field;
	protected $add_cond;
	
	public function __construct($flags = 0, mysql_db $db, $table, $field, array $add_cond = array()) {
		parent::__construct($flags);
		$this->db			= $db;
		$this->table		= $table;
		$this->field		= $field;
		$this->add_cond		= $add_cond;
	}
	
	public function check_value($value, array &$errors) {
		parent::check_value($value, $errors);
		if (!$errors) {
			if ($this->add_cond) {
				$where = "{$this->field} = ? AND {$this->add_cond[0]} != ?";
				$arr_val = array($value, $this->add_cond[1]);
			}
			else {
				$where = "{$this->field} = ?";
				$arr_val = array($value);
			}
			
			$res = $this->db->getCol($this->field, $this->table, $where, $arr_val);
			if (count($res) > 0) $errors['duplicate'] = TRUE;
		}
	}
};

endif/* FORM_FIELD_MAIL_DB_DUPLICATE_CLASS_INC */?>
