<?php declare(encoding = 'UTF-8');
if (!defined('FORM_VARS_OBJECT_CLASS_INC')): define('FORM_VARS_OBJECT_CLASS_INC', 1);

class FormVarsObject extends FormVars {
	public function get_from_vars(&$obj) {
		$vars = (array)$obj;
		$results = parent::get_from_vars($vars);
		$obj = (object)$vars;
		return $results;
	}
};

endif/* FORM_VARS_OBJECT_CLASS_INC */?>
