<?php declare(encoding = 'UTF-8');
if (!defined('FORM_FIELD_SELECT_CLASS_INC')): define('FORM_FIELD_SELECT_CLASS_INC', 1);

class FormFieldSelect extends FormField {
	protected $list;
	public function __construct(array $list, $flags = 0) {
		parent::__construct($flags);
		$this->list = $list;
	}
	public function check_value($value, array &$errors) {
		parent::check_value($value, $errors);
		if (is_null($value)) {
			// NOP
		}
		else {
			if (!in_array($value, $this->list)) {
				$errors['not_found'] = TRUE;
			}
		}
	}
	public function convert_value($value) {
		if (is_null($value)) return NULL;
		return preg_match('/^\d+$/s', $value)? (int)$value: $value;
	}
};

endif/* FORM_FIELD_SELECT_CLASS_INC */?>
