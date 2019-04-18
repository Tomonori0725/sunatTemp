<?php declare(encoding = 'UTF-8');
if (!defined('FORM_FIELD_DATE_TIME_CLASS_INC')): define('FORM_FIELD_DATE_TIME_CLASS_INC', 1);

class FormFieldDateTime extends FormField {
	public function check_value($value) {
		$errors = parent::check_value($value);
		if (is_null($value)) {
			// NOP
		}
		elseif (!is_string($value)) {
			$errors['not_date'] = TRUE;
		}
		else {
			if (strtotime(preg_replace('/[.\s]/s', '-', $value)) === FALSE) {
				$errors['bad_format'] = TRUE;
			}
		}
		return $errors;
	}

	public function convert_value($value) {
		return is_null($value)? NULL: new DateTime(preg_replace('/[.\s]/s', '-', $value));
	}
};

endif/* FORM_FIELD_DATE_TIME_CLASS_INC */?>
