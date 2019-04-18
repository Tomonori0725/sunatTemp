<?php declare(encoding = 'utf-8');
if (!defined('SIZE_CLASS_INC')): define('SIZE_CLASS_INC', 1);

class Size implements iTemplateValues, iTemplateValue {
	protected $_width;
	protected $_height;

	public function __construct() {
		$args = func_get_args();
		$n = count($args);
		if ($n == 0) {
			$this->_width  = 0;
			$this->_height = 0;
		}
		elseif ($n == 1 && $args[0] instanceof Size) {
			$this->_width  = $args[0]->_width;
			$this->_height = $args[0]->_height;
		}
		elseif ($n == 1 && $args[0] instanceof stdClass && isset($args[0]->width) && isset($args[0]->height)) {
			$this->_width  = $args[0]->width;
			$this->_height = $args[0]->height;
		}
		elseif ($n == 1 && is_array($args[0]) && array_key_exists('width',  $args[0]) && is_numeric($args[0]['height']) && array_key_exists('height', $args[0]) && is_numeric($args[0]['width'])) {
			$this->_width  = $arg['width'];
			$this->_height = $arg['height'];
		}
		elseif ($n == 1 && is_array($args[0]) && count($args[0]) >= 2 && is_numeric($args[0][0]) && is_numeric($args[0][1])) {
			list($this->_width, $this->_height) = $args[0];
		}
		elseif ($n == 1 && is_string($args[0]) && preg_match('/^(\d+)x(\d+)$/si', $args[0], $m)) {
			$this->_width  = (int)$m[1];
			$this->_height = (int)$m[2];
		}
		elseif ($n == 2 && is_numeric($args[0]) && is_numeric($args[1])) {
			list($this->_width, $this->_height) = $args;
		}
		else
			trigger_error('Invalied Arguments', E_USER_ERROR);
	}

	public function __set($n, $v) {
		if (is_numeric($v)) trigger_error('Invalied arguments', E_USER_ERROR);
		    if ($n == 'width')  $this->_width  = $v;
		elseif ($n == 'height') $this->_height = $v;
		else
			trigger_error("Invalid Property $n", E_USER_ERROR);
	}
	public function __get($n) {
		    if ($n == 'width')  return $this->_width;
		elseif ($n == 'height') return $this->_height;
		else
			trigger_error("Invalid Property $n", E_USER_ERROR);
	}

	public function computeResize(Size $limit) {
		if ($this->_width  < $limit->_width
		&&  $this->_height < $limit->_height)
			return new Size($this);

		$dw = $this->_width  / $limit->_width;
		$dh = $this->_height / $limit->_height;

		if ($dw == $dh) return new Size($limit);

		return $dw > $dh?
			new Size($limit->_width, floor($this->_height * $limit->_width / $this->_width)):
			new Size(floor($this->_width * $limit->_height / $this->_height ), $limit->_height);
	}

	function getValue(array $options) {
		return sprintf('%dx%d', $this->_width, $this->_height);
	}

	function getValues() {
		return array('width' => $this->_width, 'height' => $this->_height);
	}

	function __toString() {
		return sprintf('%dx%d', $this->_width, $this->_height);
	}
};

endif/* SIZE_CLASS_INC */?>
