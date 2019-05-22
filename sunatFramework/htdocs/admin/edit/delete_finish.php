<?php
/*****************************************************************************
This is non public. 無断転載,無断使用を禁ず
Copyright (C) 2011. Sunatmark Co,.Ltd. All Right Reserved.

ファイル概要: ブログ　ブログ編集　記事削除　完了画面

$Id$
*****************************************************************************/

require	'const.inc';
require	'error.inc';
require 'MDB2.php';
require 'phplib5.inc';
require 'sub.inc';

session_start();

//カレントセッション
$sess = new SessionCurrent('*admin.user.list.delete');
$cur_ss = &$sess->vars;

$ref_sess = new SessionReference('*admin.user.list', SessionReference::MUST);
$ref_ss =& $ref_sess->vars;

$tmpl_arr = array();

$db = new mysql_db();
$form = new Form;

if ($_SERVER['REQUEST_METHOD'] == 'POST') error('アクセスエラー', '不正なアクセスです', '/admin/edit/');
if (array_key_exists('id', $cur_ss)) $id = $cur_ss['id'];
else redirect("/admin/edit/");

$db = new mysql_db();

$db->begin();
$db->delete('article', 'id = ?', array($id));
$db->commit();

$cur_ss = array();

$temp = new HTMLTemplate('admin/edit/delete_finish.html');
echo $temp->replace($tmpl_arr);

?>