<?php declare(encoding = 'UTF-8');
if (!defined('FORM_FIELD_FILE_UP_CLASS_INC')): define('FORM_FIELD_FILE_UP_CLASS_INC', 1);

class FormFieldFileUp extends FormFieldSubFields {
	protected $_mimeTypeMask;
	public function __construct($flags = 0, $tempDir = NULL, $mimeTypeMask = '#^.*/.*$#s', $sizeLimit = NULL, Size $imageSizeLimit = NULL, Size $previewSize = NULL) {
		parent::__construct($flags);
		/*
		$this->delete   = new FormFieldBool(FormField::NOT_NULL);
		$this->current  = new FormFieldFileInfo(0, $mimeTypeMask, $sizeLimit, $imageSizeLimit, $previewSize);
		$this->file     = new FormFieldFileInfo(0, $mimeTypeMask, $sizeLimit, $imageSizeLimit, $previewSize);
		*/
        $this->up        = new FormFieldFileInfo(0, $tempDir, $mimeTypeMask, $sizeLimit, $imageSizeLimit, $previewSize);
        $this->delete    = new FormFieldBool(FormField::NOT_NULL);
        $this->file      = new FormFieldString(FormField::MBCONV | FormField::TRIM);
        $this->current   = new FormFieldString(FormField::MBCONV | FormField::TRIM, '/^(https?|ftp)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)([^\/]+)$/u');
        $this->name      = new FormFieldString(FormField::MBCONV | FormField::TRIM);
        $this->path      = new FormFieldString(FormField::MBCONV | FormField::TRIM);
        $this->exist     = new FormFieldBool(FormField::NOT_NULL);
		$this->_mimeTypeMask = $mimeTypeMask;
	}

    public function pre_converts(array &$results, array &$errors, array &$in, array &$files)
    {

        parent::pre_converts($results, $errors, $in, $files);
        $value = $this->pre_convert($results, $files);
        $errors = $value;
        $results = $value;
    }

    public function pre_convert($value, array $file)
    {
        if (!is_array($value)) {
            return NULL;
        }
        if ($value['up']) {
            $result = array(
                'up'      => $value['up'],
                'delete' => false,
                'file'    => $value['up']['path'],
                'current' => $value['current'],
                'name'   => $value['up']['name'],
                'path'   => $value['up']['path'],
                'exist'  => true
            );
        } else if ($value['delete']) {
            $result = array(
                'up'      => null,
                'delete' => true,
                'file'    => null,
                'current' => $value['current'],
                'name'   => null,
                'path'   => null,
                'exist'  => false
            );
        } else if ($value['file'] && $value['file'] != '{file:not found}') {
            if ($value['name']) $name = $value['name'];
            else $name = urldecode(basename($value['file']));
            $result = array(
                'up'      => null,
                'delete' => false,
                'file'    => $value['file'],
                'current' => $value['current'],
                'name'   => $name,
                'path'   => $value['file'],
                'exist'  => true
            );
        } else if ($value['current']) {
            $result = array(
                'up'      => null,
                'delete' => false,
                'file'    => null,
                'current' => $value['current'],
                'name'   => urldecode(basename($value['current'])),
                'path'   => $value['current'],
                'exist'  => true
            );
        } else {
            $result = array(
                'up'      => null,
                'delete' => false,
                'file'    => null,
                'current' => null,
                'name'   => null,
                'path'   => null,
                'exist'  => false
            );
        }
        return $result;
    }

    public function check_value($value, array &$errors)
    {
        parent::check_value($value, $errors);
        if (is_null($value)) {
            $errors['is_null'] = TRUE;
            return;
        }
        if (($this->flags & FormField::NOT_NULL) && (!$value['file'] && !is_array($value['up']))) {
            $errors['is_null'] = TRUE;
            return;
        }

        //拡張子がCSVの場合はスルー
        if (preg_match('/\.csv$/', $value['up']['temp_name'])) {
            return;
        }

        if (!is_null($this->_mimeTypeMask) && is_array($value['up']) && !preg_match($this->_mimeTypeMask, $value['up']['mimeType'])) {
            $errors['bad_format'] = TRUE;
            return;
        }
        /*if (!is_null($this->_mimeTypeMask) && is_array($value['up']) && $this->_sizeLimit < $value['up']['size']) {
            $errors['too_long'] = TRUE;
            var_dump($this->_sizeLimit);
            echo($this->_sizeLimit . '<' . $value['up']['size']);
            return;
        }*/
        /*if (!is_null($this->_allowExtMask) && is_array($value['up']) && !preg_match($this->_allowExtMask, $value['up']['name'])) {
            $errors['bad_ext'] = TRUE;
            return;
        }*/
    }

};

endif/* FORM_FIELD_FILE_UP_CLASS_INC */?>
