<?php declare(encoding = 'UTF-8');
if (!defined('FORM_FIELDS_SEQ_ARRAY_CLASS_INC')): define('FORM_FIELDS_SEQ_ARRAY_CLASS_INC',1 );

class FormFieldsSeqArray implements iFormFields, iFormField {
	protected $type;
	protected $min;
	protected $max;

	public function __construct(iFormField $type, $min = NULL, $max = NULL) {
		$this->type = $type;
		$this->min = $min;
		$this->max = $max;
	}
	
	public function pre_converts(array &$results, array &$errors, array &$in, array &$files) {
		if (!is_array($in)) return;
		$values = array_values($in);

		foreach ($in as $idx => $value) {
			$file  = array_key_exists($idx, $files)? $files[$idx]: array();
			if ($this->type instanceof iFormFields) {
				if (is_array($value)) $tmp = &$value; else $tmp = array();
				$errors[$idx]  = array();
				$results[$idx] = array();
				$this->type->pre_converts($results[$idx], $errors[$idx], $tmp, $file);
				$results[$idx] = $this->type->pre_convert($results[$idx], $file);
			}
			else {
				$errors[$idx]  = $value;
				$results[$idx] = $this->type->pre_convert($value, $file);
			}
		}
	}

	public function pre_convert($value, array $file) {
		return $value;
	}

	public function check_values(array &$in, array &$errors) {
		foreach ($in as $idx => &$value) {
			if ($this->type instanceof iFormFields && is_array($value)) $this->type->check_values($value, $errors[$idx]);
			$error = array();
			$this->type->check_value($value, $error);
			if ($error) {
				if (is_array($errors[$idx]) && count($errors[$idx]) && array_keys($errors[$idx]) !== range(0, count($errors[$idx]) - 1))
					$errors[$idx]['error'] = $error;
				$errors[$idx] = $error;
			}
		}
	}

	public function check_value($value, array &$errors) { 
		if (!is_null($this->min) && $this->min > count($value)) $errors['min'] = TRUE;
		if (!is_null($this->max) && $this->max < count($value)) $errors['max'] = TRUE;
	}

	public function convert_values(array &$results, array &$in) {
		foreach ($in as $idx => &$value) {
			if ($this->type instanceof iFormFields && is_array($value)) {
				$results[$idx] = array();
				$this->type->convert_values($results[$idx], $value);
				$results[$idx] = $this->type->convert_value($results[$idx]);
			}
			else {
				$results[$idx] = $this->type->convert_value($value);
			}
		}
	}

	public function convert_value($value) { return $value; }
};

endif/* FORM_FIELDS_SEQ_ARRAY_CLASS_INC */?>
