<?php declare(encoding = 'UTF-8');
if (!defined('TEMPLATE_BASE_CLASS_INC')): define('TEMPLATE_BASE_CLASS_INC', 1);

class TemplateBase {
	protected $name;
	protected $content;

	protected $values;
	protected $hisotry;
	protected $path;

	public function __construct($name, $content = NULL) {
		if (is_null($content)) {
			$content = file_get_contents($name, TRUE);
			if (extension_loaded('mbstring'))
				$content = mb_convert_encoding($content, mb_internal_encoding(), mb_detect_order());
		}
		$this->name = $name;
		$this->content = $content;
	}

	protected function filter($value, array $options) {
		if (array_key_exists('printf', $options))
			$value = sprintf($options['printf'], $value);
		if (array_key_exists('number_format', $options) && is_numeric($value))
			$value = number_format($value);
		if (array_key_exists('mask', $options))
			$value = str_pad("", strlen($value), "*");
			
		return $value;
	}

	protected function getPath($name) {
		$path = explode('.', $name);
		if ($path[0] != '') $path = array_merge($this->path, $path);
		else array_shift($path);
		return $path;
	}

	protected function push($name) {
		$this->history[] = $this->path;
		$this->path = $this->getPath($name);
	}

	protected function pop() {
		$this->path = array_pop($this->history);
	}

	//{{{ SECTION
	protected function getValues($name) {
		$path = $this->getPath($name);
		$values = &$this->values;
		foreach ($path as $n) {
			if ($values instanceof iTemplateValues) $values = $values->getValues();
			if (!is_array($values) || !array_key_exists($n, $values)) return NULL;
			$values = &$values[$n];
		}
		return $values;
	}

	protected function replaceSection($mark, &$values, $content) {
		if ($mark == '!') return $values? '': $this->replaceContent($content);
		if (is_array($values)) {
			$n = count($values);
			if ($n == 0) return '';
			$keys = array_keys($values);
			if ($keys !== range(0, $n - 1)) return $this->replaceContent($content);
			$result = '';
			foreach ($keys as $i) {
				$this->push($i);
				$result .= $this->replaceContent($content);
				$this->pop();
			}
			return $result;
		}
		return $values? $this->replaceContent($content): '';
	}

	protected static function findSection($content, $offset = NULL) {
		if (preg_match('#<!--(!?)([\w:.-]+)-->#s', $content, $m, PREG_OFFSET_CAPTURE, $offset)) {
			$section = new stdClass;
			list($all, $mark, $name) = $m;
			$section->offset = $all[1];
			$section->mark = $mark[0];
			$section->name = $name[0];

			$begin  = $all[1] + strlen($all[0]);
			$offset = $begin;
			$nest = 0;
			while (preg_match('#<!--([!/]?)'.preg_quote($section->name, '#').'-->#s', $content, $m, PREG_OFFSET_CAPTURE, $offset)) {
				list($all, $mark) = $m;
				if ($mark[0] == '/') {
					if ($nest == 0) {
						$section->length = $all[1] + strlen($all[0]) - $section->offset;
						$section->content = substr($content, $begin, $all[1] - $begin);
						return $section;
					}
					$nest--;
				}
				else {
					$nest++;
				}
				$offset = $all[1] + strlen($all[0]);
			}
		}
		return false;
	}

	//}}}

	//{{{ VALUE
	private function getValue($name) {
		$value = "{{$name}:not found}";
		$path = $this->getPath($name);
		$name = $path[count($path) - 1];
		$values = &$this->values;
		foreach ($path as $n) {
			if ($values instanceof iTemplateValues) $values = $values->getValues();
			if (!is_array($values)) break;
			if (count($values) && array_keys($values) === range(0, count($values) -1)) foreach ($values as $i => &$v) if (!array_key_exists('_', $v)) $v['_'] = $i;
			if (array_key_exists($name, $values)) $value = $values[$name];
			if (!array_key_exists($n, $values)) break;
			$values = &$values[$n];
		}
		return $value;
	}

	public function replaceValue($m) {
		list($all, $mark, $name, $ext) = $m;
		$value = $this->getValue($name);
		$type = gettype($value);
		if (!is_null($value)
		&&  !is_scalar($value)
		&&  !is_object($value)
		&&  !($value instanceof iTemplateValue))
			return "{{$name}:bad data type $type}";

		$options = array();
		if ($ext != '') {
			$ext = $this->replaceContent($ext);
			if (preg_match('/^(?:(={1,3}|!={0,2}|<=?|>=?)(.*))?(?:(?<!\\\\)\\?(.*))(?:(?<!\\\\):(.*))$/', $ext, $m)) {
				list(, $op, $var, $true, $false) = $m;
				if (is_int($value) && is_numeric($var)) $var = (int)$var;
				elseif (is_float($value) && is_numeric($var)) $var = (float)$var;
				switch ($op) {
				case '':    return $value? $true: $false;
				case '=':
				case '==':  return $value ==  $var? $true: $false;
				case '===': return $value === $var? $true: $false;
				case '!':
				case '!=':  return $value !=  $var? $true: $false;
				case '!==': return $value !== $var? $true: $false;
				case '<':   return $vlaue <   $var? $true: $false;
				case '<=':  return $value <=  $var? $true: $false;
				case '>':   return $value >   $var? $true: $false;
				case '>=':  return $value >=  $var? $true: $false;
				default: return "{{$name}:bad expression}";
				}
			}
			elseif (preg_match('/^#(?:([\w:-]+)(?:\s*=(?:\s*"(?:[^"]|(?<=\\\\)")*"|\S*))?\s*)*$/s', $ext, $ms)) {
				preg_match_all('/([\w:-]+)(?:\s*=(?:\s*"((?:[^"]|(?<=\\\\)")*)"|(\S*)))?/s', $ext, $ms, PREG_SET_ORDER);
				foreach ($ms as $m) {
					switch (count($m)) {
					case 2: $options[$m[1]] = TRUE;  break;
					case 3; $options[$m[1]] = $m[2]; break;
					case 4; $options[$m[1]] = $m[3]; break;
					default: return "{{$name}:bad option format}";
					}
				}
			}
			else
				return "{{$name}:unkown extention format '$ext'}";
		}

		if ($value instanceof DateTime) {
			$value = $value->format(array_key_exists('format', $options)? $options['format']: 'Y/m/d H:i:s');
		}

		elseif ($value instanceof iTemplateValue)
			$value = $value->getValue($options);

		return $this->filter($value, $options);
	}

	//}}}

	protected function replaceContent($content) {
		while ($section = self::findSection($content)) {
			$values = $this->getValues($section->name);
			$this->push($section->name);
			$content = substr_replace($content,
				$this->replaceSection($section->mark, $values, $section->content), 
				$section->offset, $section->length);
			$this->pop();
		}
		return preg_replace_callback(
			array(
				'/(?<!\\\\)\$()([\w:.-]+)()/s',
				'/(?<!\\\\)\${(!?)([\w:.-]+)((?:[^{}]|(?R))*)}/s',
			),
			array($this, 'replaceValue'),
			$content);
	}

	public function replace(array &$values = array(TRUE)) {
		$this->values = &$values;
		$this->history = array();
		$this->path = array();
		return $this->replaceContent($this->content);
	}

	public function getSection($path) {
		if (is_string($path)) $path = explode('.', $path);
		if (!is_array($path)) trigger_error('引数が文字列または配列ではない', E_USER_ERROR);

		if (count($path) == 1) {
			@list($name, $n) = explode('#', $path[0]);
			if (is_null($n)) $n = 0;
			elseif (preg_match('/^\d+$/si', $n)) $n = (int)$n;
			else trigger_error("セクションの位置指定が数値ではない $name#$n", E_USER_ERROR);

			$offset = 0;
			$i = 0;
			while ($section = self::findSection($this->content, $offset)) {
				if ($section->name == $name) {
//					if ($i == $n) return new self($name, $section->content);
					if ($i == $n) return $this->getSelf($name, $section->content);
					$i++;
				}
				$offset = $section->offset + $section->length;
			}
			return FALSE;
		}

		$template = $this;
		while ($path) {
			$name = array_shift($path);
			$template = $template->getSection($name);
			if (!$template) return FALSE;
		}
		return $template;
	}
	
	
	public function getSelf($name, $content) {
		return new self($name, $content);
	}
};

endif/* TEMPLATE_BASE_CLASS_INC */?>
