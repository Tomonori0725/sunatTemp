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
$sess = new SessionCurrent('*admin.user.list.edit');
$cur_ss = &$sess->vars;

$ref_sess = new SessionReference('*admin.user.list', SessionReference::MUST);
$ref_ss =& $ref_sess->vars;

$newImage = '';
$arrWhere = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') error('アクセスエラー', '不正なアクセスです', '/admin/edit');

if (array_key_exists('edit_input', $cur_ss)){
	$sql_arr = $cur_ss['edit_input']['values'];
	$sql_arr['filepass'] = $cur_ss['filepass'];
}
else redirect("/admin/writer/");

//削除にチェックが入っていたら
$newImage = $sql_arr['image'];

$arrArticle = array(
	'name'    => $sql_arr['name'],
	'profile' => $sql_arr['profile'],
	'image'   => $sql_arr['filepass'],
	'imgDel'  => false
);

$db = new mysql_db();
$columnId = $cur_ss['id'];
$where = 'id = ' . $columnId;

$db->begin();
$db->update('writer', $arrArticle, $where, $arrWhere);
$db->commit();
unset($cur_ss['edit_input']);

$tmpl_arr = array('id' => $columnId);
$temp = new HTMLTemplate('admin/writer/edit_finish.html');
echo $temp->replace($tmpl_arr);

?>