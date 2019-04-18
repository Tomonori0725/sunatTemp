<?php declare(encoding = 'UTF-8');
/*----------------------------------------------------------------------------
機能概要: 商品コードの入力チェック
機能説明:

引数:
	名前 (I/O) 説明
	$flag 	 	(I) 必須かどうかなどのフラグ
	$db 	 	(I) dbオブジェクト
	$add_cond		(I) 追加検索条件
	$exists		(I) 重複時エラーかどうかのフラグ

戻り値:
	エラーがひとつでも見つかれば true 、見つからなければ false
----------------------------------------------------------------------------*/
if (!defined('FORM_FIELD_GOODSCODE_CLASS_INC')): define('FORM_FIELD_GOODSCODE_CLASS_INC', 1);

class FormFieldGoodsCode extends FormFieldStringDbDuplicate {
	private $exists;

	public function __construct($flags = 0, db $db, array $add_cond = array(), $exists = FALSE) {
		//$pattern = '/^D\d\-\d{6}$/';
		$table = 'goods_code';
		$field = 'code';
		$this->exists = $exists;
		parent::__construct($flags, PRODUCT_CODE_FORMAT_MATCH, NULL, NULL, $db, $table, $field, $add_cond);
	}

	public function check_value($value, array &$errors) {
		parent::check_value($value, $errors);
		
		if ($this->exists) {
			if ($errors) {
				if (array_key_exists('duplicate', $errors)) unset($errors['duplicate']);
			}
			else {
				$errors['not_regist'] = 1;
			}
		}
	}
};

endif/* FORM_FIELD_GOODSCODE_CLASS_INC */?>
