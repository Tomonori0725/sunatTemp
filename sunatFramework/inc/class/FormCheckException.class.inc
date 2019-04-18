<?php declare(encoding = 'UTF-8');
if (!defined('FORM_CHECK_EXCEPTION_CLASS_INC')): define('FORM_CHECK_EXCEPTION_CLASS_INC', 1);

class FormCheckException extends Exception {
	private $values;

	public function __construct(array $errors) {
		parent::__construct('Invalid Form Data');
		$this->values = $errors;
	}

	public function getValues() { return $this->values; }
};

endif/* FORM_CHECK_EXCEPTION_CLASS_INC */?>
