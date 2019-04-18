<?php declare(encoding = 'UTF-8');
if (!defined('FORM_CLASS_INC')): define('FORM_CLASS_INC', 1);

class Form {
	private $post;
	private $get;

	public function __construct() {
		$this->post = new FormFields;
		$this->get  = new FormFields;
	}

	public function __get($name) {
		if ($name == 'post') return $this->post;
		if ($name == 'get')  return $this->get;
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

	private static function rebuildFiles(array $a) {
		if (!is_array($a['name'])) return $a;
		$result = array();
		foreach ($a['name'] as $i => $v) {
			$tmp = array();
			foreach (array('name', 'type', 'tmp_name', 'error', 'size') as $n) $tmp[$n] = $a[$n][$i];
			$result[$i] = self::rebuildFiles($tmp);
		}
		return $result;
	}

	public function get() {
		$dummy1 = array();
		$dummy2 = array();
		foreach ($_FILES as $i => $a) $dummy2[$i] = self::rebuildFiles($a);
		$inputs = array('get'  => &$_GET,   'post' => &$_POST);
		$files  = array('get'  => &$dummy1, 'post' => &$dummy2);
		$in = array();
		$results = array();
		$errors = array();

		foreach (array('get', 'post') as $method) {
			$this->$method->pre_converts($in, $errors, $inputs[$method], $files[$method]);
			$this->$method->check_values($in, $errors);
		}

		if (self::has_error($errors)) {
			$e = $errors;
			$errors['errors'] = $e;
			throw new FormCheckException($errors);
		}

		$results = array('values' => array(), 'in' => $errors);
		foreach (array('get', 'post') as $method)
			$this->$method->convert_values($results['values'], $in);

		return $results;
	}
};

endif/* FORM_CLASS_INC */?>
