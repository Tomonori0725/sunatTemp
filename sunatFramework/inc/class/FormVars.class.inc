<?php declare(encoding = 'UTF-8');
if (!defined('FORM_VARS_CLASS_INC')): define('FORM_VARS_CLASS_INC', 1);

class FormVars extends Form {
	private $vars;
	protected $value;
	
	public function __construct() {
		$this->vars = new FormFields;
		$this->value = array();
	}

	public function __get($name) {
		if ($name == 'vars') return $this->vars;
		trigger_error("Form::__get(): known Property Name '$name'.", E_USER_ERROR);
	}

	public function __set($name, iFormField $type) {
		$this->checks[$name] = $type;
	}

	private function has_error(array &$errors) {
		foreach ($errors as $name => $sub) {
			if (preg_match('/:error$/s', $name)) return TRUE;
			if (is_array($sub) && self::has_error($sub)) return TRUE;
		}
		return FALSE;
	}
	
	public function set_value(&$value) {
		$this->value = $value;
	}

	public function get() {
		$dummy = array();
		$inputs = array('vars' => &$this->value);
		$files  = array('vars' => &$dummy);
		$in = array();
		$results = array();
		$errors = array();

		foreach (array('vars') as $method) {
			$this->$method->pre_converts($in, $errors, $inputs[$method], $files[$method]);
			$this->$method->check_values($in, $errors);
		}

		if (self::has_error($errors)) {
			$e = $errors;
			$errors['errors'] = $e;
			throw new FormCheckException($errors);
		}

		$results = array('values' => array(), 'in' => $errors);
		foreach (array('vars') as $method)
			$this->$method->convert_values($results['values'], $in);

		return $results;
	}
};

endif/* FORM_VARS_CLASS_INC */?>
