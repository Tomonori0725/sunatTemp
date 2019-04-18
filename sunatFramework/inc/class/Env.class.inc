<?php declare(encoding = 'UTF-8');
if (!defined('ENV_CLASS_INC')): define('ENV_CLASS_INC', 1);

class Env implements Iterator {
	static private $ENV = NULL;

	static private function parse_ini_file($filename, $process_sections) {
		$paths = explode(PATH_SEPARATOR, get_include_path());
		foreach ($paths as $path) {
			$fn = "$path/$filename";
			if (file_exists($fn))
				return parse_ini_file($fn, $process_sections);
		}
		return array();
	}

	static private function getenv() {
		$env = self::parse_ini_file('default_env.ini', TRUE);
		$in  = self::parse_ini_file('env.ini', TRUE);
		foreach ($env as $section_name => &$section) {
			if (array_key_exists($section_name, $in))
				foreach ($section as $key_name => &$value)
					if (array_key_exists($key_name, $in[$section_name]))
						$value = $in[$section_name][$key_name];
			$section = (object)$section;
		}
		self::$ENV = $env;
	}

	private $env;

	public function __construct() {
		if (is_null(self::$ENV)) self::getenv();
		$this->env = self::$ENV;
	}

	public function __get($name) {
		return array_key_exists($name, $this->env)? $this->env[$name]: NULL;
	}

	public function rewind() { reset($this->env); }
	public function current() { return current($this->env); }
	public function key() { return key($this->env); }
	public function next() { return next($this->env); }
	public function valid() { return $this->current() !== FALSE; }
};

endif/* ENV_INC */?>
