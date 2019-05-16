<?php declare(encoding = 'UTF-8');
if (!defined('FORM_FIELD_FILE_UP_CLASS_INC')): define('FORM_FIELD_FILE_UP_CLASS_INC', 1);

class FormFieldFileUp extends FormFieldSubFields {
	protected $_mimeTypeMask;
	public function __construct($flags = 0, $mimeTypeMask = '#^.*/.*$#s', $sizeLimit = NULL, Size $imageSizeLimit = NULL, Size $previewSize = NULL) {
		parent::__construct($flags);
		$this->delete   = new FormFieldBool(FormField::NOT_NULL);
		$this->current  = new FormFieldFileInfo(0, $mimeTypeMask, $sizeLimit, $imageSizeLimit, $previewSize);
		$this->file     = new FormFieldFileInfo(0, $mimeTypeMask, $sizeLimit, $imageSizeLimit, $previewSize);
		$this->_mimeTypeMask = $mimeTypeMask;
	}

	public function pre_converts(array &$results, array &$errors, array &$in, array &$files) {
		parent::pre_converts($results, $errors, $in, $files);
		var_dump($results);
		$value = $this->pre_convert($results, $files);
		if ($value instanceof ByteArray)
		$errors = $value;
		$results = $value;
	}

	public function pre_convert($value, array $file) {
		// no call parent
		if (is_null($value)) return NULL;
		if ($value instanceof ByteArray) return $value;
		if ($value['file'] instanceof ByteArray && !$value['file']->is_null()) return $value['file'];
		if ($value['delete']) return new ByteArray(NULL);
		if ($value['current'] instanceof ByteArray && !$value['current']->is_null()) return $value['current'];
		return new ByteArray(NULL);
	}

	public function check_value($value, array &$errors) {
		parent::check_value($value, $errors);
		if (is_null($value)) $errors['is_null'] = TRUE;
		if (($this->flags & FormField::NOT_NULL) && $value->is_null()) $errors['is_null'] = TRUE;
		if (!is_null($this->_mimeTypeMask) && !$value->is_null() && !preg_match($this->_mimeTypeMask, $value->mimeType)) $errors['bad_format'] = TRUE;
	}
};

endif/* FORM_FIELD_FILE_UP_CLASS_INC */?>
