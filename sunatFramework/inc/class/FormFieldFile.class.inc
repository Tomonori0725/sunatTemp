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

class FormFieldFile extends FormField {

	protected $ary_file;

	public function __construct(array $ary_file, $flags = 0) {
		parent::__construct($flags);
		$this->ary_file = $ary_file;
	}

	public function check_value($value, array &$errors) {
		//アップロードされたかチェック
		if (!is_uploaded_file($this->ary_file['tmp_name'])) {
			if ($this->flags & FormField::NOT_NULL) {
				$errors['is_null'] = TRUE;
				return;
			}
		} else {
			//MIMEタイプ
			switch ($this->ary_file['type']) {
				case 'image/jpg':
				case 'image/jpeg':
				case 'image/pjpeg':
					if ($this->ary_file['size'] > 0) {
						return;	//OK
					}
			}
			$errors['bad_format'] = TRUE;
		}
	}
};

endif/* FORM_FIELD_FILE_CLASS_INC */?>
