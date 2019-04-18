<?php declare(encoding = 'UTF-8');
if (!defined('FORM_FIELD_DATE_TIME_ARRAY_CLASS_INC')): define('FORM_FIELD_DATE_TIME_ARRAY_CLASS_INC', 1);

class FormFieldDateTimeArray extends FormField {
	private $flags2;
	public function __construct($flags = 0, $flags2 = 0) {
		$this->flags2 = $flags2;
		parent::__construct($flags);
	}

	public function check_value($value, array &$errors) {
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

		$y = trim(mb_convert_kana($value['year'], 'sna'));
		$m = trim(mb_convert_kana($value['month'], 'sna'));
		$d = trim(mb_convert_kana($value['day'], 'sna'));

		if ($i == 0) {
			if (preg_match('/^[0]$/', $y) && preg_match('/^[0]$/', $m) && preg_match('/^[0]$/', $d)) {
				$errors['bad_format'] = TRUE;
			}
		}
		else {
			if (!preg_match('/^[0-9]{4}$/', $y) || !preg_match('/^[0-9]{1,2}$/', $m) || !preg_match('/^[0-9]{1,2}$/', $d)) {
				$errors['bad_format'] = TRUE;
			}
			else if (checkdate($m, $d, $y) === FALSE) {
				$errors['bad_format'] = TRUE;
			}
		}
		return $errors;
	}

	public function convert_value($value) {
		//if (implode('-', $value) != "--") return is_null($value)? NULL: new Timestamp(implode('-', $value));
		if (implode('-', $value) != "--") {
			$value['year'] = trim(mb_convert_kana($value['year'], 'sna'));
			$value['month'] = trim(mb_convert_kana($value['month'], 'sna'));
			$value['day'] = trim(mb_convert_kana($value['day'], 'sna'));
			if ($this->flags2) return is_null($value)? NULL: new Timestamp(implode('-', $value));
			else return is_null($value)? NULL: new DateTime(implode('-', $value));
		}
		else return NULL;
	}
};

endif/* FORM_FIELD_DATE_TIME_ARRAY_CLASS_INC */?>
