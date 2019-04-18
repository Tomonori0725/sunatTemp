<?php declare(encoding = 'UTF-8');
/*----------------------------------------------------------------------------
機能概要:
	CSVダウンロードパスワードチェック
機能説明:

引数:

戻り値:
	エラーがひとつでも見つかれば true 、見つからなければ false
----------------------------------------------------------------------------*/
if (!defined('FORM_FIELD_CSV_DOWNLOAD_PASSWORD_CLASS_INC')): define('FORM_FIELD_CSV_DOWNLOAD_PASSWORD_CLASS_INC', 1);

class FormFieldCsvDownLoadPassword extends FormField {
	
	protected $db;
	protected $user_id;

	public function __construct($flags = 0, $db, $user_id) {
		$this->db		= $db;
		$this->user_id	= $user_id;
	}
	
	public function check_value($value, array &$errors) {
		parent::check_value($value, $errors);
		if (!$errors) {

			$csv_password = $this->db->get('csv_password', 'user', 'id = ?', array($this->user_id));
			if ($csv_password != $value) {
                $errors['bad_format'] = TRUE;
			}
		}
	}
};

endif/* FORM_FIELD_CSV_DOWNLOAD_PASSWORD_CLASS_INC */?>
