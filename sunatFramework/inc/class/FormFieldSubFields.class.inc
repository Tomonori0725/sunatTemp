<?php declare(encoding = 'UTF-8');
if (!defined('FORM_FIELD_SUB_FIELDS_CLASS_INC')): define('FORM_FIELD_SUB_FIELDS_CLASS_INC', 1);

class FormFieldSubFields extends FormFields implements iFormField {
	const NULL_COMPACT = 256;
	protected $flags;

	public function __construct($flags = 0) {
		parent::__construct();
		$this->flags = $flags;
	}

	protected function is_null(array $values) {
		$is_null = TRUE;
		foreach ($this->fields as $field => $type)
			if (!is_null($values[$field])) $is_null = FALSE;
		return $is_null;
	}

	public function pre_convert($value, array $file) {
		if (is_null($value)) return NULL;
		if (!is_array($value)) $value = array();
		if (!($this->flags & FormField::NOT_NULL) && ($this->flags & self::NULL_COMPACT) && self::is_null($value)) $value = NULL;
		return $value;
	}

	public function check_value($value, array &$errors) {
		if ($this->flags & FormField::NOT_NULL) {
			if ((is_array($value) && self::is_null($value)) || is_null($value)) $errors['is_null'] = TRUE;
		}
		else {
			if (is_null($value)) $errors = array();
		}
	}

	public function convert_value($value) {
		return $value;
	}
};

endif/* FORM_FIELD_SUB_FIELDS_CLASS_INC */?>
