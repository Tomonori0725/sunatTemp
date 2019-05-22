<?php
/*****************************************************************************
This is non public. 無断転載,無断使用を禁ず
Copyright (C) 2011. Sunatmark Co,.Ltd. All Right Reserved.

ファイル概要: ブログ　ブログ編集　投稿者編集　投稿者一覧

$Id$
*****************************************************************************/

require	'const.inc';
require	'error.inc';
require 'MDB2.php';
require 'phplib5.inc';
require 'sub.inc';

session_start();

//カレントセッション
$sess = new SessionCurrent('*admin.user.list.index');
$cur_ss = &$sess->vars;

$ref_sess = new SessionReference('*admin.user.list', SessionReference::INIT);
$ref_ss =& $ref_sess->vars;


$tmpl_arr = array();
$search_writer = '';
$search_cate = '';

$where = '';
$arrWhere = array();

$db = new mysql_db();
$form = new Form;


//投稿者
$col = 'id,name';
$table = 'writer';
$writer_list = $db->select($col, $table, $where, $arrWhere);

//記事があれば、削除させない
$col = 'count(*)';
$table = 'article LEFT OUTER JOIN writer ON article.writer_id = writer.id';
$writer_array = array();

foreach($writer_list as $writer){
	$where = 'article.writer_id = ' . $writer['id'];
	$column_count = $db->select($col, $table, $where, $arrWhere);
	$count = $column_count[0]['count(*)'];
	if($count == '0'){
		$writer['del_link'] = '<a href="/admin/writer/delete.php?id=' . $writer['id'] . '">削除</a>';
	}else{
		$writer['del_link'] = '<span>削除</span>';
	}
	$writer_array[] = $writer;
}

$list_section = array(
	'writer_list'	=> $writer_array
);
$tmpl_arr += array(
	'list_section'		=> $list_section
);

$temp = new HTMLTemplate('admin/writer/index.html');
echo $temp->replace($tmpl_arr);


?>