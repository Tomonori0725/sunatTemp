<?php declare(encoding = 'UTF-8');
/*----------------------------------------------------------------------------
機能概要:
	アップロードファイルのチェックを行う
機能説明:

引数:

戻り値:
	エラーがひとつでも見つかれば true 、見つからなければ false
----------------------------------------------------------------------------*/
if (!defined('FORM_FIELD_FILE_CLASS_INC')): define('FORM_FIELD_FILE_CLASS_INC', 1);

class FormIsFieldFile extends FormField {

	protected $is_image;

	public function __construct($is_image, $flags = 0) {
		parent::__construct($flags);
		$this->is_image = $is_image;
	}

	public function check_value($value, array &$errors) {
		//画像があるかどうか
		if ($this->is_image & FormField::NOT_NULL) {
			$errors['is_null'] = TRUE;
			return;
		}
	}
};

endif/* FORM_FIELD_FILE_CLASS_INC */?>
