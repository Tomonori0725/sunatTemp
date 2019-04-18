<?php declare(encoding = 'UTF-8');
if (!defined('FORM_FIELD_INT_CLASS_INC')): define('FORM_FIELD_INT_CLASS_INC',1 );

class FormFieldInt extends FormField {
	protected $min;
	protected $max;

	public function __construct($flags = 0, $min = NULL, $max = NULL) {
		parent::__construct($flags);
		$this->min = $min;
		$this->max = $max;
	}

	public function pre_convert($value, array $file) {
		$value = parent::pre_convert($value, $file);
		if (is_string($value)) {
			$value = preg_replace('/^0*/s', '', $value);
			if ($value == '') $value = '0';
		}
		return $value;
	}

	public function check_value($value, array &$errors) {
		parent::check_value($value, $errors);
		if (is_null($value)) {
		}
		elseif (!is_string($value) || !preg_match('/^\d+$/s', $value)) {
			$errors['not_int'] = TRUE;
		}
		else {
			$n = (int)$value;
			if ((string)$n !== $value) {
				$errors['overflow'] = TRUE;
			}
			else {
				if (is_int($this->min) && $n < $this->min) $errors['too_small'] = TRUE;
				if (is_int($this->max) && $n > $this->max) $errors['too_large'] = TRUE;
			}
		}
	}

	public function convert_value($value) {
		return is_null($value)? NULL: (int)$value;
	}
};

endif/* FORM_FIELD_INT_CLASS_INC */?>
