<?php declare(encoding = 'utf-8');
if (!defined('I_FORM_FIELDS_CLASS_INC')):define('I_FORM_FIELDS_CLASS_INC', 1);

interface iFormFields {
	public function pre_converts(array &$results, array &$errors, array &$in, array &$files);
	public function check_values(array &$results, array &$errors);
	public function convert_values(array &$results, array &$in);
}

endif/* I_FORM_FIELDS_CLASS_INC */?>
