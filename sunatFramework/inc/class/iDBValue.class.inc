<?php declare(encoding = 'UTF-8');
if (!defined('I_DB_VALUE_CLASS_INC')): define('I_DB_VALUE_CLASS_INC', 1);

interface iDBValue {
	public function getDBString(db $db);
};

endif/* I_DB_VALUE_CLASS_INC */?>
