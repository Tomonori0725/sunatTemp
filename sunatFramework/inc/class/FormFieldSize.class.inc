<?php declare(encoding = 'utf-8');
if (!defined('FORM_FIELD_SIZE_CLASS_INC')): define('FORM_FIELD_SIZE_CLASS_INC', 1);

class FormFieldSize extends FormFieldString {
	public function __construct($flag = 0) {
		parent::__construct($flag, '/^\d+x\d+$/s');
	}

	public function convert_value($value) {
		$value = parent::convert_value($value);
		$value = is_null($value)? NULL: new Size(array_map(function($a) { return (int)$a; }, explode('x', $value)));
		return $value;
	}
};

endif/* FORM_FIELD_SIZE_CLASS_INC */?>
