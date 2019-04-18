<?php declare(encoding = 'utf-8');
if (!defined('TIMESTAMP_CLASS_INC')): define('TIMESTAMP_CLASS_INC', 1);

class Timestamp implements iTemplateValues, iTemplateValue, iDBValue {
	private $time;

	const DEFAULT_FORMAT = 'Y/m/d H:i:s';

	public function __construct($in = 'now') {
		if (is_int($in)) $in = date('c', $in);
		elseif (is_object($in) && ($in instanceof DateTime || $in instanceof Timestamp)) {
			$in = $in->format('c');
		}
		if (is_string($in)) $this->time = date_create($in);
		else trigger_error('Timestampへ変換出来ないデータが指定された。', E_USER_ERROR);
	}

	public function format($format) { return date_format($this->time, $format); }
	public function modify($modify) { return date_modify($this->time, $modify); }

	public function getValue(array $options) {
		$format = self::DEFAULT_FORMAT;
		if (array_key_exists('format', $options)) $format = $options['format'];
		return $this->format($format);
	}

	public function getValues() {
		$result = array('year' => 'Y', 'month' => 'm', 'day' => 'd', 'hour' => 'h', 'minute' => 'i', 'second' => 's');
		foreach ($result as &$v) $v = $this->format($v);
		return $result;
	}

	public function getDBString(db $db) {
		return '\''.$this->format('c').'\'::TIMESTAMP';
	}

	public function __sleep() {
		$this->time = $this->format('c');
		return array('time');
	}

	public function __wakeup() {
		if (is_string($this->time)) $this->__construct($this->time);
	}
};

endif/* TIMESTAMP_CLASS_INC */?>
