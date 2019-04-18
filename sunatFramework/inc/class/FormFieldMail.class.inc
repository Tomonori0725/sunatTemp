<?php declare(encoding = 'UTF-8');
if (!defined('FORM_FIELD_MAIL_CLASS_INC')): define('FORM_FIELD_MAIL_CLASS_INC', 1);

class FormFieldMail extends FormField {
	public function check_value($value, array &$errors) {
		parent::check_value($value, $errors);
		if (is_null($value)) {
			// NOP
		}
		elseif (!is_string($value)) {
			$errors['not_date'] = TRUE;
		}
		else {
			if (!preg_match('/^([\w!#$%&\'"*+\/=?^`{|}~#()<>[\]:;@,. -]+)@([a-z0-9][a-z0-9-.]+[a-z])$/is', $value, $m)) {
				$errors['bad_format'] = TRUE;
			}
		}
	}
};

endif/* FORM_FIELD_MAIL_CLASS_INC */?>
