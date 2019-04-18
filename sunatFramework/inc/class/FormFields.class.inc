<?php declare(encoding = 'UTF-8');
if (!defined('FORM_FIELDS_CLASS_INC')): define('FORM_FIELDS_CLASS_INC', 1);

class FormFields implements Iterator, iFormFields {
	protected $fields;

	public function __construct() { $this->fields = array(); }

	public function pre_converts(array &$results, array &$errors, array &$in, array &$files) {
		foreach ($this->fields as $field => $type) {
			$value = array_key_exists($field, $in)? $in[$field]: NULL;
			$file  = array_key_exists($field, $files)? $files[$field]: array();
			if ($type instanceof iFormFields) {
				if (is_array($value)) $tmp = &$value; else $tmp = array();
				$errors[$field]  = array();
				$results[$field] = array();
				$type->pre_converts($results[$field], $errors[$field], $tmp, $file);
				$results[$field] = $type->pre_convert($results[$field], $file);
			}
			else {
				$errors[$field]  = $value;
				$results[$field] = $type->pre_convert($value, $file);
			}
		}
	}

	public function check_values(array &$in, array &$errors) {
		foreach ($this->fields as $field => $type) {
			$value = &$in[$field];
			if ($type instanceof iFormFields && is_array($value)) $type->check_values($value, $errors[$field]);
			$error = array();
			$type->check_value($value, $error);
			if ($error) {
				if (is_array($errors[$field]) && count($errors[$field]) && array_keys($errors[$field]) !== range(0, count($errors[$field]) - 1))
					$errors[$field]['error'] = $error;
				$errors["$field:error"] = $error;
			}
		}
	}

	public function convert_values(array &$results, array &$in) {
		foreach ($this->fields as $field => $type) {
			$value = &$in[$field];
			if ($type instanceof iFormFields && is_array($value)) {
				$results[$field] = array();
				$type->convert_values($results[$field], $value);
				$results[$field] = $type->convert_value($results[$field]);
			}
			else {
				$results[$field] = $type->convert_value($value);
			}
		}
	}

	public function __set($name, iFormField $type) { $this->fields[$name] = $type; } 
	public function __get($name)   { return array_key_exists($name, $this->fields)? $this->fields[$name]: FALSE; }
	public function __isset($name) { return array_key_exists($name, $this->fields); }
	public function __unset($name) { if (array_key_exists($name, $this->fields)) unset($this->fields[$name]); }

	// Iterator
	public function rewind()  { reset($this->fields); }
	public function current() { return current($this->fields); }
	public function key()     { return key($this->fields); }
	public function next()    { return next($this->fields); }
	public function valid()   { return current($this->fields) !== FALSE; }
};

endif/* FORM_FIELDS_CLASS_INC */?>
