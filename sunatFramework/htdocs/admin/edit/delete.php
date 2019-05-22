<?php
/*****************************************************************************
This is non public. 無断転載,無断使用を禁ず
Copyright (C) 2011. Sunatmark Co,.Ltd. All Right Reserved.

ファイル概要: ブログ　ブログ編集　記事削除

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
$form->get->id = new FormFieldInt(FormField::TRIM | FormField::MBCONV | FormField::NOT_NULL);
try {
	$ret = $form->get();
    $id = $ret['values']['id'];
	$cur_ss['id'] = $id;
} catch (FormCheckException $e) {
	error('アクセスエラー', '不正なアクセスです', '/admin/edit/');
}

$col = <<< COL
	category.category_name,article.title,article.description,article.contents,article.image,article.date,writer.name
COL;
$table = 'article LEFT OUTER JOIN category ON article.category_id = category.id LEFT OUTER JOIN writer ON article.writer_id = writer.id';
$where = 'article.id = ' . $id;
$arrWhere = array();
$delete_arr = $db->getRow($col, $table, $where, $arrWhere);
if (!$delete_arr) error('アクセスエラー', '不正なアクセスです', '/admin/edit/');

if(!empty($delete_arr['image'])){
    $delete_arr['image'] = '<img src="' . $delete_arr['image'] .'" alt="' . $delete_arr['title'] .'">';
}else{
    $delete_arr['image'] = '画像は設定せれていません。';
}

$tmpl_arr = $delete_arr;

$temp = new HTMLTemplate('admin/edit/delete.html');
echo $temp->replace($tmpl_arr);

?>