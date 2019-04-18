<?php declare(encoding = 'UTF-8');
if (!defined('MOBILE_HTML_TEMPLATE_INC')): define('MOBILE_HTML_TEMPLATE_INC', 1);
require_once 'mobile.inc';

class MobileHTMLTemplate extends HTMLTemplate {
	public static function get_emoji_list() {
		include_once('emoji_list.inc');
		$emoji_buf = get_emoji_list();
		$emoji_list = array();
		foreach ($emoji_buf as $code_arr) {
			$emoji_list[] = array(
				's' => $code_arr[0],
				'i' => $code_arr[1],
				'e' => $code_arr[2]
			);
		}
		return $emoji_list;
	}
	
	public function __construct($name, $content = NULL) {
		$emoji_list = $this->get_emoji_list();
		$mobile = new Mobile(Mobile::get_ua()) ;
		
		if (is_null($content)) {
			$content = file_get_contents($name, TRUE);
			
			// SoftBankなら絵文字を変換
			if ($mobile->mobile_info['carrier'] == 'vodafone') {
				foreach ($emoji_list as $emoji) {
					if (is_null($emoji['i'])) continue;
					$content = str_replace($emoji['i'], $emoji['s'], $content);
				}
			}
			
			if (extension_loaded('mbstring'))
				$content = mb_convert_encoding($content, mb_internal_encoding(), 'cp932');
		}
		$this->name = $name;
		$this->content = $content;
	}

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
			// Softbankの絵文字のWEBコードと衝突するため、{}無しは禁止
			array(
				'/(?<!\\\\)\${(!?)([\w:.-]+)((?:[^{}]|(?R))*)}/s',
			),
			array($this, 'replaceValue'),
			$content);
	}

	protected function filter($value, array $options) {
		$value = parent::filter($value, $options);
		
		if (!array_key_exists('wide', $options) || !$options['wide'])
			$value = mb_convert_kana($value, 'k');
		
		return $value;
	}
};

endif/* MOBILE_HTML_TEMPLATE_INC */?>
