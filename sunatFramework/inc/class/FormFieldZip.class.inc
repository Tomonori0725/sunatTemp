<?php declare(encoding = 'UTF-8');
if (!defined('FORM_FIELD_ZIP_CLASS_INC')): define('FORM_FIELD_ZIP_CLASS_INC', 1);

class FormFieldZip extends FormFieldString {
	const HYPHENATION = 1; // ハイフンで区切る
	protected $option;

	public function __construct($option = 0, $flags = 0) {
		parent::__construct($flags, '/^\d{3}-?\d{4}$/s');
		$this->option = $option;
	}

	public function convert_value($value) {
		if (is_null($value)) return NULL;
		preg_match('/^(\d{3})-?(\d{4})$/s', $value, $m);
		return ($this->option & self::HYPHENATION)? $m[1].'-'.$m[2]: $m[1].$m[2];
	}
};

endif/* FORM_FIELD_ZIP_CLASS_INC */?>
