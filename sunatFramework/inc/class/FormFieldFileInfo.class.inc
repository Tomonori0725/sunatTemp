<?php declare(encoding = 'utf-8');
if (!defined('FORM_FIELD_FILE_INFO_CLASS_INC')):define('FORM_FIELD_FILE_INFO_CLASS_INC', 1);

class FormFieldFileInfo extends FormField {
    private $tempDir = NULL;
	private $sizeLimit = NULL;
	private $mimeTypeMask = NULL;
	private $imageSizeLimit = NULL;
	private $previewSize = NULL;

	public function __construct($flags = 0, $tempDir, $mimeTypeMask = '#^.*/.*$#s', $sizeLimit = NULL, Size $imageSizeLimit = NULL, Size $previewSize = NULL) {
		parent::__construct($flags);
        $this->tempDir        = $tempDir;
		$this->mimeTypeMask   = $mimeTypeMask;
		$this->sizeLimit      = $sizeLimit;
		$this->imageSizeLimit = $imageSizeLimit;
		$this->previewSize    = $previewSize;
	}

    protected static function is_fileinfo(array $file)
    {
        foreach (array('name', 'type', 'tmp_name', 'error', 'size') as $n) {
            if (!array_key_exists($n, $file)) {
                return FALSE;
            }
        }
        return TRUE;
    }

    public function pre_convert($value, array $file)
    {
        if (is_array($file) && array_key_exists('error', $file) && $file['error'] == UPLOAD_ERR_OK && is_uploaded_file($file['tmp_name']) && $file['size'] > 0) {
            $tempDir = $this->tempDir;
            if (preg_match('/^.*(\.\w*)$/', $file['name'], $ma)) $ext = $ma[1];
            else $ext = '';
            $temp_name = date("Ymd-his") . $ext;
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0777, true);
                chmod($tempDir, 0777);
            }
            move_uploaded_file($file['tmp_name'], $tempDir . $temp_name);
            $value = array(
                'name'     => $file['name'],
                'temp_name'  => $temp_name,
                'path'     => $tempDir . $temp_name,
                'mimeType' => $file['type'],
                'size'     => $file['size']
            );
        } else {
            $value = parent::pre_convert($value, $file);
            if (is_null($value)) {
                $value = NULL;
            } else if (preg_match('/^data:/', $value)) {
                $value = new ByteArray($value, NULL, $this->previewSize);
            }
        }
        if ($value instanceof ByteArray && !$value->is_null()
            && preg_match('#^image/#', $value->mimeType) && !is_null($this->imageSizeLimit)) {
            $value = $value->resizeImage($this->imageSizeLimit);
        }
        return $value;
    }

    public function check_value($value, array &$errors)
    {
        parent::check_value($value, $errors);
        if (is_null($value)) {
            return;
        }
        if (is_array($value)) {
            if (($this->flags & FormField::NOT_NULL) && is_null($value)) {
                $errors['is_null'] = TRUE;
            }
            if (!is_null($this->sizeLimit) && $this->sizeLimit < $value['size']) {
                $errors['too_long'] = TRUE;
            }

            //拡張子がCSVの場合はスルー
            if (preg_match('/\.csv$/', $value['temp_name'])) {
                return;
            }

            if (!is_null($this->mimeTypeMask) && !is_null($value) && !preg_match($this->mimeTypeMask, $value['mimeType'])) {
                $errors['bad_format'] = TRUE;
            }
        } else {
            $errors['bad_fomat'] = TRUE;
        }
    }
};

endif/* FORM_FIELD_FILE_INFO_CLASS_INC */?>
