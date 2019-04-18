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

if (array_key_exists('add_input', $cur_ss)) $sql_arr = $cur_ss['add_input']['values'];
else redirect("/admin/");

$sql_arr['date'] = $sql_arr['date']->date;

$arrUser = array(
	'category_id' => $sql_arr['category_id'],
	'title'       => $sql_arr['title'],
	'description' => $sql_arr['description'],
	'contents'    => $sql_arr['contents'],
	'image'       => $sql_arr['image'],
	'date'        => $sql_arr['date'],
	'writer_id'   => $sql_arr['writer_id'],
	'imgDel'      => false,
);

$db = new mysql_db();
$db->begin();
$db->insert('article', $arrUser);
$db->commit();
unset($cur_ss['add_input']);

$temp = new HTMLTemplate('admin/edit/add_finish.html');
echo $temp->replace();

?>