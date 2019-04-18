<?php declare(encoding = 'UTF-8');
if (!defined('I_FORM_FIELD_CLASS_INC')): define('I_FORM_FIELD_CLASS_INC', 1);

interface iFormField {
	public function pre_convert($value, array $file);
	public function check_value($value, array &$errors);
	public function convert_value($value);
};

endif/* I_FORM_FIELD_CLASS_INC */?>
