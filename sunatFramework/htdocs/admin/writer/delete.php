<?php
/*****************************************************************************
This is non public. 無断転載,無断使用を禁ず
Copyright (C) 2011. Sunatmark Co,.Ltd. All Right Reserved.

ファイル概要: 企業情報検索ユーザー管理　ユーザー管理一覧

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

if ($_SERVER['REQUEST_METHOD'] == 'POST') error('アクセスエラー1', '不正なアクセスです1', '/admin/edit/');
$form->get->id = new FormFieldInt(FormField::TRIM | FormField::MBCONV | FormField::NOT_NULL);
try {
	$ret = $form->get();
    $id = $ret['values']['id'];
	$cur_ss['id'] = $id;
} catch (FormCheckException $e) {
	error('アクセスエラー2', '不正なアクセスです2', '/admin/writer/');
}

$col = <<< COL
	name,profile,image
COL;
$table = 'writer';
$where = 'id = ' . $id;
$arrWhere = array();
$delete_arr = $db->getRow($col, $table, $where, $arrWhere);
if (!$delete_arr) redirect('/admin/writer/');

if(!empty($delete_arr['image'])){
    $delete_arr['image'] = '<img src="' . $delete_arr['image'] .'" alt="' . $delete_arr['name'] .'">';
}else{
    $delete_arr['image'] = '画像は設定せれていません。';
}

$tmpl_arr = $delete_arr;

$temp = new HTMLTemplate('admin/writer/delete.html');
echo $temp->replace($tmpl_arr);

?>