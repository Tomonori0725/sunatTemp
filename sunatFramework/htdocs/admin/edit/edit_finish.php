<?php
/*****************************************************************************
This is non public. 無断転載,無断使用を禁ず
Copyright (C) 2011. Sunatmark Co,.Ltd. All Right Reserved.

ファイル概要: ブログ　ブログ編集　記事編集　完了画面

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

$ref_sess = new SessionReference('*admin.user.list', SessionReference::INIT);
$ref_ss =& $ref_sess->vars;

$newImage = '';
$arrWhere = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') error('アクセスエラー', '不正なアクセスです', '/admin/edit');

if (array_key_exists('edit_input', $cur_ss)) $sql_arr = $cur_ss['edit_input']['values'];
else redirect("/admin/edit/");

$sql_arr['date'] = $sql_arr['date']->date;

//削除にチェックが入っていたら
if($sql_arr['imgDel']){
	$newImage = '';
}else{
	$newImage = $sql_arr['image'];
}

$arrArticle = array(
	'category_id' => $sql_arr['category_id'],
	'title'       => $sql_arr['title'],
	'description' => $sql_arr['description'],
	'contents'    => $sql_arr['contents'],
	'image'       => $newImage,
	'date'        => $sql_arr['date'],
	'writer_id'   => $sql_arr['writer_id'],
	'imgDel'      => false,
);

$db = new mysql_db();
$columnId = $cur_ss['id'];
$where = 'id = ' . $columnId;

$db->begin();
$db->update('article', $arrArticle, $where, $arrWhere);
$db->commit();
unset($cur_ss['edit_input']);

$tmpl_arr = array('id' => $columnId);
$temp = new HTMLTemplate('admin/edit/edit_finish.html');
echo $temp->replace($tmpl_arr);

?>