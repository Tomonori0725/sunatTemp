<?php declare(encoding = 'UTF-8');
/*----------------------------------------------------------------------------
機能概要: ショップ側調整値の入力チェック
機能説明:
	棚番の入力をチェックするクラス

引数:
	名前 (I/O) 説明
	$flag 	 	(I) 必須かどうかなどのフラグ

戻り値:
	エラーがひとつでも見つかれば true 、見つからなければ false
----------------------------------------------------------------------------*/
if (!defined('FORM_FIELD_SHOPCONTROL_CLASS_INC')): define('FORM_FIELD_SHOPCONTROL_CLASS_INC', 1);

class FormFieldShopControl extends FormFieldString {

	public function __construct($flags = 0) {
		$pattern = '/^(\#?((\<|\>)?\d+|(\+|\-)0*[1-9]\d*|\*|0*([1-9]\d{0,3}|10000)\%))$/';
		parent::__construct($flags, $pattern);
	}

	public function pre_convert($value, array $file) {
		$value = parent::pre_convert($value, $file);
		if (is_string($value)) {
			$value = preg_replace('/^(\<|\>|\+|\-)?0*/s', '$1', $value);
			if ($value == '') $value = '0';
		}
		return $value;
	}
	
	public function check_value($value, array &$errors) {
		parent::check_value($value, $errors);
		if (preg_match('/^\d+$/s', $value)) {
			$n = (int)$value;
			if ((string)$n !== $value) {
				$errors['overflow'] = TRUE;
			}
		}

	}
};

endif/* FORM_FIELD_SHOPCONTROL_CLASS_INC */?>
