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
$sess = new SessionCurrent('*admin.user.list');
$cur_ss = &$sess->vars;

$ref_sess = new SessionReference('*admin.user.list', SessionReference::INIT);
$ref_ss =& $ref_sess->vars;


$tmpl_arr = array();

$where = '';
$arrWhere = array();

$db = new mysql_db();

$col = 'article.id,category_name,title,date';
$table = 'article LEFT OUTER JOIN category ON article.category_id = category.id';
$article_list = $db->select($col, $table, $where, $arrWhere);

$list_section = array(
	'article_list'	=> $article_list
);
$tmpl_arr += array(
	'list_section'		=> $list_section
);

$temp = new HTMLTemplate('admin/index.html');
echo $temp->replace($tmpl_arr);


?>