<?php declare(encoding = 'utf-8');
if (!defined('FORM_FIELD_FILE_INFO_CLASS_INC')):define('FORM_FIELD_FILE_INFO_CLASS_INC', 1);

class FormFieldFileInfo extends FormField {
	private $sizeLimite = NULL;
	private $mimeTypeMask = NULL;
	private $imageSizeLimit = NULL;
	private $previewSize = NULL;

	public function __construct($flags = 0, $mimeTypeMask = '#^.*/.*$#s', $sizeLimit = NULL, Size $imageSizeLimit = NULL, Size $previewSize = NULL) {
		parent::__construct($flags);
		$this->mimeTypeMask   = $mimeTypeMask;
		$this->sizeLimit      = $sizeLimit;
		$this->imageSizeLimit = $imageSizeLimit;
		$this->previewSize    = $previewSize;
	}

	protected static function is_fileinfo(array $file) {
		foreach (array('name', 'type', 'tmp_name', 'error', 'size') as $n)
			if (!array_key_exists($n, $file)) return FALSE;
		return TRUE;
	}

	public function pre_convert($value, array $file) {
		if (is_array($file) && array_key_exists('error', $file) && $file['error'] == UPLOAD_ERR_OK && is_uploaded_file($file['tmp_name']) && $file['size'] > 0) {
			$value = new ByteArray(file_get_contents($file['tmp_name']), $file['name'], $this->previewSize);
		}
		else {
			$value = parent::pre_convert($value, $file);
			if (is_null($value)) $value = new ByteArray(NULL, NULL, $this->previewSize);
			elseif (preg_match('/^data:/', $value)) $value = new ByteArray($value, NULL, $this->previewSize);
		}
		if ($value instanceof ByteArray && !$value->is_null()
		&&  preg_match('#^image/#', $value->mimeType) && !is_null($this->imageSizeLimit))
			$value = $value->resizeImage($this->imageSizeLimit);
		return $value;
	}

	public function check_value($value, array &$errors) {
		parent::check_value($value, $errors);
		if (is_null($value)) return;
		if ($value instanceof ByteArray) {
			if (($this->flags & FormField::NOT_NULL) && $value->is_null()) $errors['is_null'] = TRUE;
			if (!is_null($this->sizeLimit) && $this->sizeLimit < $value->size) $errors['too_long'] = TRUE;
			if (!is_null($this->mimeTypeMask) && !$value->is_null() && !preg_match($this->mimeTypeMask, $value->mimeType)) $errors['bad_format'] = TRUE;
		}
		else {
			$errors['bad_fomat'] = TRUE;
		}
	}
};

endif/* FORM_FIELD_FILE_INFO_CLASS_INC */?>
