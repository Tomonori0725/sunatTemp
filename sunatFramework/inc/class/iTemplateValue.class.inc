<?php declare(encoding = 'UTF-8');
if (!defined('I_TEMPLATE_VALUE_CLASS_INC')): define('I_TEMPLATE_VALUE_CLASS_INC', 1);

interface iTemplateValue {
	public function getValue(array $options);
};

endif/* I_TEMPLATE_VALUE_CLASS_INC */?>
