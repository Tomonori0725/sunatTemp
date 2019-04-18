<?php declare(encoding = 'UTF-8');
if (!defined('FORM_FIELD_BOOL_CLASS_INC')): define('FORM_FIELD_BOOL_CLASS_INC', 1);

class FormFieldBool extends FormField {
	public function check_value($value, array &$errors) {
		// NOP Override
	}

	public function convert_value($value) {
		return (bool)$value;
	}
};

endif/* FORM_FIELD_BOOL_CLASS_INC */?>
