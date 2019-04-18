<?php declare(encoding = 'UTF-8');
if (!defined('BYTE_ARRAY_CLASS_INC')): define('BYTE_ARRAY_CLASS_INC', 1);

class ByteArray implements iTemplateValues, iTemplateValue, iDBValue {
	private $_contents;
	private $_mimeType;
	private $_imageSize;
	private $_filename;
	private $_previewSize;

	public static function detectMimeType($data) {
		if (is_null($data)) return NULL;
		$finfo = new finfo(FILEINFO_MIME_TYPE);
		return $finfo->buffer($data);
	}

	public function __construct($contents = NULL, $filename = NULL, Size $previewSize = NULL) {
		if ($contents instanceof ByteArrayKey) {
			$this->contents  = $contents->_contents;
			if (is_null($filename))    $this->_filename    = $contents->_filename;
			if (is_null($previewSize)) $this->_previewSize = $contents->_previewSize;
		}
		else {
			if (preg_match('/^data:/', $contents)) $this->dataUrl  = $contents;
			else                                   $this->contents = $contents;
			$this->_filename    = $filename;
			$this->_previewSize = $previewSize;
		}
	}

	public function __get($n) {
		if ($n == 'base64')   return base64_encode($this->_contents);
		if ($n == 'md5')      return md5($this->_contents);
		if ($n == 'mimeType') return $this->_mimeType;
		if ($n == 'contents') return $this->_contents;
		if ($n == 'dataUrl')  return 'data:' . $this->mimeType . ';base64,' . $this->base64;
		if ($n == 'filename') return $this->_filename;
		if ($n == 'size')     return strlen($this->_contents);
		if ($n == 'imageSize' && $this->is_image()) return $this->_imageSize;
		if ($n == 'previewSize') return $this->_previewSize;
		trigger_error("property $n is not found", E_USER_ERROR);
	}

	public function __set($n, $v) {
		if ($n == 'contents') {
			if (is_string($v) && strlen($v) === 0) $v = NULL;
			if (is_null($v)) {
				$this->_contents  = NULL;
				$this->_mimeType  = NULL;
				$this->_imageSize = NULL;
			}
			else {
				$this->_contents  = $v instanceof ByteArray? $v->_contents: $v;
				$this->_mimeType  = self::detectMimeType($this->_contents);
				$this->_imageSize = preg_match('#^image/#s', $this->mimeType) && ($size = getImageSize($this->dataUrl))? new Size($size): NULL;
			}
		}
		elseif ($n == 'base64') {
			$this->contents = base64_decode($v);
		}
		elseif ($n == 'dataUrl') {
			if (preg_match('/^data:/s', $v)) {
				$this->contents = file_get_contents($v);
			}
			else {
				trigger_error('Invalid scheme name.', E_USER_ERROR);
			}
		}
		elseif ($n == 'filename') {
			if (!is_string($v)) trigger_error('Invalid argument', E_USER_ERROR);
			$this->_filename = $v;
		}
		elseif ($n == 'previewSize') {
			if (!($v instanceof Size) && !is_null($v)) trigger_error('Invalid argument', E_USER_ERROR);
			$this->_previewSize = $v;
		}
		else
			trigger_error("property $n is not found", E_USER_ERROR);
	}

	public function __isset($n) {
		$a = array('base64', 'md5', 'contents', 'mimeType', 'dataUrl', 'filename', 'size', 'previewSize');
		if ($this->is_image()) array_push($a, 'imageSize');
		return array_key_exists($n, $a);
	}

	public function __toString() {
		return $this->_contents;
	}

	public function putContents($filename, $use_include_path = FALSE, $context = NULL) {
		file_put_contents($filename, $this->_contents, $use_include_path? FILE_USE_INCLUDE_PATH: 0, $context);
	}

	public function getContnets($filename, $use_include_path = FALSE, $context = NULL) {
		$this->_contents = file_get_contents($filename, $use_include_path, $context);
		$this->_mimeType = self::detectMimeType($this->_contents);
		$this->_filename = $filename;
	}

	public function put() {
		header('Content-Type: ' . $this->_mimeType);
		echo $this->_contents;
	}

	public function getValue(array $options) {
		if (array_key_exists('base64', $options))  return $this->base64;
		if (array_key_exists('dataurl', $options)) return $this->dataUrl;
		return '(binary)';
	}

	public function getValues() {
		$result = array();
		if (is_null($this->_contents)) {
			$result['current'] = NULL;
		}
		else {
			if ($this->is_image()) {
				$result['image'] = array('image' => $this, 'width' => $this->_imageSize->width, 'height' => $this->_imageSize->height);
				if (!is_null($this->_previewSize)) {
					$previewSize = $this->_imageSize->computeResize($this->_previewSize);
					$result['image']['preview'] = array(
						'image'       => $this->resizeImage($previewSize),
						'width'       => $previewSize->width,
						'height'      => $previewSize->height,
						'previewSize' => $previewSize,
					);
				}
			}
			$result['current'] = $this->dataUrl;
		}
		return $result;
	}

	public function getDBString(db $db) {
		return '\'' . $db->escapeByteA($this->_contents) . '\'::BYTEA';
	}

	public function is_null() { return is_null($this->_contents); }

	public function is_image() { return !is_null($this->_imageSize); }

	public function resizeImage(Size $sizeLimit) {
		if (is_null($this->_contents)) return new ByteArray(NULL, $this->_filename, $this->_previewSize);
		$img = new Imagick($this->dataUrl);
		$size = $this->_imageSize->computeResize($sizeLimit);
		$img->scaleImage($size->width, $size->height, TRUE);
		return new ByteArray($img->getImagesBlob(), $this->_filename, $this->_previewSize);
	}
};

endif/* BYTE_ARRAY_CLASS_INC */?>
