<?php
/*****************************************************************************
This is non public. 無断転載,無断使用を禁ず
Copyright (C) 2011. Sunatmark Co,.Ltd. All Right Reserved.

ファイル概要: 企業情報検索ユーザー管理　ユーザー新規登録　確認

$Id$
*****************************************************************************/


require	'const.inc';
require	'error.inc';
require 'MDB2.php';
require 'phplib5.inc';
require 'sub.inc';

session_start();

//カレントセッション
$sess = new SessionCurrent('*admin.user.list.article');
$cur_ss = &$sess->vars;

$ref_sess = new SessionReference('*admin.user.list', SessionReference::MUST);
$ref_ss =& $ref_sess->vars;

if ($_SERVER['REQUEST_METHOD'] == 'POST') error('アクセスエラー', '不正なアクセスです', '/admin/edit');

if (array_key_exists('add_input', $cur_ss)){
	$sql_arr = $cur_ss['add_input']['values'];
	$sql_arr['filepass'] = $cur_ss['filepass'];
} 
else redirect("/admin/");

$arrWriter = array(
	'name'       => $sql_arr['name'],
	'profile'    => $sql_arr['profile'],
	'image'      => $sql_arr['filepass'],
	'imgDel'     => false
);

$db = new mysql_db();
$db->begin();
$db->insert('writer', $arrWriter);
$db->commit();
unset($cur_ss['add_input']);

$temp = new HTMLTemplate('admin/writer/add_finish.html');
echo $temp->replace();

?>