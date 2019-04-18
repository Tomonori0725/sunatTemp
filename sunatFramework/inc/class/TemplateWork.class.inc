<?php declare(encoding = 'UTF-8');
if (!defined('TEMPLATE_WORK_CLASS_INC')): define('TEMPLATE_WORK_CLASS_INC', 1);

class TemplateWork {
	private $template;
	private $values;

	public function __construct(TemplateBase $template, array &$values) {
		$this->template = $template;
		$this->values   = &$values;
	}

	public function replace($m) {
		$n = count($m) - 1;
		switch ($n) {
		case 1: return $this->template->replaceValue($this->values, $m[0], $m[1]);
		case 2: return $this->template->replaceValue($this->values, $m[0], $m[1], $m[2]);
		case 3: return $this->template->replaceSection($this->values, $m[1], $m[2], $m[3]);
		}
		trigger_error('unknown patter');
	}
};

endif/* TEMPLATE_WORK_CLASS_INC */?>
