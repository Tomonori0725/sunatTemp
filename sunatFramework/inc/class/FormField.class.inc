<?php declare(encoding = 'UTF-8');
if (!defined('FORM_FIELD_CLASS_INC')): define('FORM_FIELD_CLASS_INC', 1);

abstract class FormField implements iFormField {
	const NOT_NULL = 1;
	const MBCONV = 2;
	const MBKANA = 4;
	const MBHIRA = 8;
	const MBZENKANA = 16;
	const TRIM = 32;
	const CASE_UPPER = 64;
	const CASE_LOWER = 128;

	protected $flags;

	public function __construct($flags = 0) { $this->flags = $flags; }

	public function pre_convert($value, array $file) {
		if (is_null($value)) return NULL;
		if (!is_string($value)) return $value;
		if (extension_loaded('mbstring')) {
			$value = mb_convert_encoding($value, mb_internal_encoding(), mb_detect_order());

			if ($this->flags & FormField::MBCONV)     $value = mb_convert_kana($value, 'sna');
			if ($this->flags & FormField::MBKANA)     $value = mb_convert_kana($value, 'KC');
			if ($this->flags & FormField::MBHIRA)     $value = mb_convert_kana($value, 'Hc');
			if ($this->flags & FormField::MBZENKANA)  $value = mb_convert_kana($value, 'KV');
			if ($this->flags & FormField::CASE_UPPER) $value = mb_convert_case($value, MB_CASE_UPPER);
			if ($this->flags & FormField::CASE_LOWER) $value = mb_convert_case($value, MB_CASE_LOWER);
		}

		if ($this->flags & FormField::TRIM) {
			$value = preg_replace('/^\s*/s', '', $value);
			$value = preg_replace('/\s*$/s', '', $value);
		}

		if ($value == '') $value = NULL;

		return $value;
	}

	public function check_value($value, array &$errors) {
		if ($this->flags & FormField::NOT_NULL) {
			if (is_null($value)) $errors['is_null'] = TRUE;
		}
	}

	public function convert_value($value) {
		return $value;
	}
};

endif/* FORM_FIELD_CLASS_INC */?>
