<?php declare(encoding = 'UTF-8');
if (!defined('FORM_FIELD_SEQ_DATE_ARRAY_CLASS_INC')): define('FORM_FIELD_SEQ_DATE_ARRAY_CLASS_INC', 1);

class FormFieldSeqDateArray extends FormFieldDateTimeArray {
	private $ary_ymd;
	
	public function __construct($flags = 0, $flags2 = 0) {
		$this->ary_ymd = array();
		parent::__construct($flags, $flags2);
	}

	public function check_value($value, array &$errors) {
		if (is_null($value)) {
			return;
		}

		if (array_key_exists('year', $value)) {
			$this->ary_ymd['year'] = $value['year'];
		}
		if (array_key_exists('month', $value)) {
			$this->ary_ymd['month'] = $value['month'];
		}
		if (array_key_exists('day', $value)) {
			$this->ary_ymd['day'] = $value['day'];
		}
		if (count($this->ary_ymd) == 3) {
			parent::check_value($this->ary_ymd, $errors);
			$this->ary_ymd = array();
		}
	}

	public function convert_value($value) {
		if (is_null($value)) {
			return;
		}

		if (array_key_exists('year', $value)) {
			$this->ary_ymd['year'] = $value['year'];
		}
		if (array_key_exists('month', $value)) {
			$this->ary_ymd['month'] = $value['month'];
		}
		if (array_key_exists('day', $value)) {
			$this->ary_ymd['day'] = $value['day'];
		}
		if (count($this->ary_ymd) == 3) {
			$ret = parent::convert_value($this->ary_ymd);
			$this->ary_ymd = array();
			return $ret;
		}
	}
};

endif/* FORM_FIELD_SEQ_DATE_ARRAY_CLASS_INC */?>
