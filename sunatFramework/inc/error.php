<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>内部エラー</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body>

<p>内部エラーが発生しました。</p>
<p>以下は補足情報です。</p>
<p>

<?php
switch ($errno) {
	case E_WARNING:      { ?>WARNING<?php }     break;
	case E_NOTICE:       { ?>NOTICE<?php }      break;
	case E_USER_ERROR:   { ?>USER_ERROR<?php }  break;
	case E_USER_WARNING: { ?>USER_WARING<?php } break;
	case E_USER_NOTICE:  { ?>USER_NOTICE<?php } break;
	default:             { ?>unknown<?php }     break;
}
?>
<br>
[<?php echo($errstr); ?>]
<?php echo($errfile); ?>(<?php echo($errline); ?>)
</p>

<?php if ($errcontext) var_dump($errcontext); ?>

<table border="1" cellpadding="0" cellspacing="0">
<caption>
デバックトレース
</caption>
<tr><th>位置</th><th>関数</th><th>引数リスト</th></tr>

<?php

$bt = debug_backtrace();
array_shift($bt);
array_shift($bt);

  foreach ($bt as $info) {
	$function = array_key_exists('function', $info)? $info['function']: '';
	$line     = array_key_exists('line', $info)? $info['line']: '';
	$file     = array_key_exists('file', $info)? $info['file']: '';
	$args     = array_key_exists('args', $info)? $info['args']: NULL;

?>

<tr><td><?php echo($file); ?>(<?php echo($line); ?>)</td>
<td><?php echo($function); ?></td><td>
<?
if (isset($args)) {
?>
<table border="1" cellpadding="0" cellspacing="0">
<?php	for ($i = 0; $i < count($args); $i++) {?> <tr><td><?php echo($i); ?></td><td><?php var_dump($args[$i])?></td></tr> <?	} ?>
</table>
<?php
}

?>

<?php
}

?>
</TR></table>

<br>
<table border="1" cellpadding="0" cellspacing="0">
<caption>
$_SERVER
</caption>
<tr><td><?php if (isset($_SERVER)) { var_dump($_SERVER); } ?></td>
</table>

<br>
<table border="1" cellpadding="0" cellspacing="0">
<caption>
$_SESSION
</caption>
<tr><td><?php if (isset($_SESSION)) { var_dump($_SESSION); } ?></td>
</table>



</html>
<?php
$err_setting = load_error_ini();

if ($err_setting['mail_send']) {
	// 内部エラー画面をメールする
	$body = ob_get_contents();
	//$body = mb_strimwidth($body, 0, 2 * 1024 * 1024, "...（省略されました）"); // 長すぎるものは丸める
	$body = mb_convert_encoding($body, 'ISO-2022-JP', 'UTF-8');
	$from = mb_encode_mimeheader('<'.$err_setting['mail_from'].'>');
	$to   = mb_encode_mimeheader('<'.$err_setting['mail_to'].'>');
	$subject = mb_encode_mimeheader($err_setting['project_name']."内部エラー通知");
	$headers = <<<HEADERS
Return-Path: $from
From: $from
Content-Type: text/html; charset="iso-2022-jp"
Content-Transfer-Encoding: 7bit
HEADERS;

	mail($to, $subject, $body, $headers);
}

if (!$err_setting['display_debug_trace']) {
	// デバッグトレースは捨てて、デザインテンプレートを表示する
	while (ob_get_level()) ob_end_clean();
	readfile('internal_error.html', true);
}
?>
