<?php declare(encoding = 'UTF-8');
if (!defined('FORM_FIELD_STRING_CLASS_INC')): define('FORM_FIELD_STRING_CLASS_INC', 1);

class FormFieldString extends FormField {
	protected $min;
	protected $max;
	protected $pattern;

	public function __construct($flags = 0, $pattern = NULL, $min = NULL, $max = NULL) {
		parent::__construct($flags);
		$this->min = $min;
		$this->max = $max;
		$this->pattern = $pattern;
	}

	public function check_value($value, array &$errors) {
		parent::check_value($value, $errors);
		if (is_null($value)) {
			// NOP
		}
		elseif (!is_string($value)) {
			$errors['not_string'] = TRUE;
		}
		else {
			$len = mb_strlen($value);
			if (is_int($this->min) && $len < $this->min) $errors['too_short'] = TRUE;
			if (is_int($this->max) && $len > $this->max) $errors['too_long']  = TRUE;
			if (is_string($this->pattern)) {
				if (extension_loaded('mbstring')) {
					$p = mb_convert_encoding($this->pattern, 'UTF-8', mb_internal_encoding());
					$v = mb_convert_encoding($value,         'UTF-8', mb_internal_encoding());
					if (!preg_match($p.'u', $v)) $errors['bad_format'] = TRUE;
				}
				else {
					if (!preg_match($this->pattern, $value)) $errors['bad_format'] = TRUE;
				}
			}
		}
	}
};

endif/* FORM_FIELD_STRING_CLASS_INC */?>
