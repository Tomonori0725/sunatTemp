<?php declare(encoding = 'UTF-8');
if (!defined('FORM_FIELD_DATE_TIME_ARRAY_EX_CLASS_INC')): define('FORM_FIELD_DATE_TIME_ARRAY_EX_CLASS_INC', 1);

class FormFieldDateTimeArray_Ex extends FormFieldDateTimeArray {
	private $flags2;
	public function __construct($flags = 0, $flags2 = 0) {
		$this->flags2 = $flags2;
		parent::__construct($flags);
	}

	public function check_value($value, array &$errors) {
		parent::check_value($value, $errors);
		
		//年・月・日で既にエラーがあれば以降のエラーチェックを行わない
		if ($errors) return $errors;
		
		$i = 0;
		foreach ( $value as $key => $val ) {
			if (!$val == 0) $i++;
		}

		if ($this->flags & FormField::NOT_NULL) {
			if ($i == 0) {
				$errors['is_null'] = TRUE;
				return ;
			}
		}

		$h = $value['hour'];
		$m = $value['minute'];

		if ($i != 0) {
			if (!preg_match('/^[0-9]{1,2}|^24$/', $h) || preg_match('/^24$/', $h) || !preg_match('/^[0-9]{1,2}$/', $m)) {
				$errors['bad_format'] = TRUE;
			}
			else if (strtotime($h . ':' . $m) === FALSE) {
				$errors['bad_format'] = TRUE;
			}
		}

		return $errors;
	}

	public function convert_value($value) {
		if (implode('-', $value) != "----") {
			$value['year']		= trim(mb_convert_kana($value['year'], 'sna'));
			$value['month']		= trim(mb_convert_kana($value['month'], 'sna'));
			$value['day']		= trim(mb_convert_kana($value['day'], 'sna'));
			$value['hour']		= trim(mb_convert_kana($value['hour'], 'sna'));
			$value['minute']	= trim(mb_convert_kana($value['minute'], 'sna'));
			$datetime_str = $value['year'] . "-" . $value['month'] . "-" . $value['day'] . " " . $value['hour'] . ":" . $value['minute'];
			if ($this->flags2) return is_null($value)? NULL: new Timestamp($datetime_str);
			else return is_null($value)? NULL: new DateTime($datetime_str);
		}
		else return NULL;
	}
};

endif/* FORM_FIELD_DATE_TIME_ARRAY_EX_CLASS_INC */?>
