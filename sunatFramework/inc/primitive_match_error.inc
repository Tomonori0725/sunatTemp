<?php if (!defined('PRIMITIVE_MATCH_ERROR_INC')): define('PRIMITIVE_MATCH_ERROR_INC', 1);

$_primitive_match_error_data = $data;
$_primitive_match_error_data .= "\r\n\r\n";
$_primitive_match_error_data .= print_r($_SERVER, true);
$_primitive_match_error_data .= "\r\n\r\n";
$_primitive_match_error_data .= print_r($_REQUEST, true);

file_put_contents(dirname(dirname(__FILE__)).'/error_log/primitive_match_error/'.date('YmdHis').'.txt', $_primitive_match_error_data);

unset($_primitive_match_error_data);

endif?>
