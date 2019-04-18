<?php declare(encoding = 'UTF-8');
if (!defined('HTML_TEMPLATE_INC')): define('HTML_TEMPLATE_INC', 1);

class HTMLTemplate extends TemplateBase {
	protected function filter($value, array $options) {
		$value = parent::filter($value, $options);

		if (!array_key_exists('direct', $options) || !$options['direct'])
			$value = htmlspecialchars($value);

		if (array_key_exists('lf', $options))
			$value = preg_replace('/(\r?\n)/s', $options['lf'].'$1', $value);

		return $value;
	}
	
	public function getSelf($name, $content) {
		return new self($name, $content);
	}
	
};

endif/* HTML_TEMPLATE_INC */?>
