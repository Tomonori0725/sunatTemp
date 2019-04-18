<?php declare(encoding = 'UTF-8');
if (!defined('FORM_FIELD_INT_DB_EXISTS_CLASS_INC')): define('FORM_FIELD_INT_DB_EXISTS_CLASS_INC', 1);

class FormFieldIntDbExists extends FormFieldInt {
	protected $db;
	protected $table;
	protected $field;

	public function __construct($flags = 0, db $db, $table, $field, $min = NULL, $max = NULL) {
		parent::__construct($flags, $min, $max);
		$this->db = $db;
		$this->table = $table;
		$this->field = $field;
	}

	public function check_value($value, array &$errors) {
		parent::check_value($value, $errors);
		if (!$errors && !is_null($value)) {
			list($count) = $this->db->selectrow_array("SELECT COUNT(*) FROM $this->table WHERE $this->field = ?;", (int)$value);
			if ($count == 0) $errors['not_exists'] = TRUE;
		}
	}
};

endif/* FORM_FIELD_INT_DB_EXISTS_CLASS_INC */?>
