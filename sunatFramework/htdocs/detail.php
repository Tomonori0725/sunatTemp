<?php
/*****************************************************************************
This is non public. 無断転載,無断使用を禁ず
Copyright (C) 2011. Sunatmark Co,.Ltd. All Right Reserved.

ファイル概要: 企業情報検索ユーザー管理　ログ履歴一覧

$Id$
*****************************************************************************/

require	'const.inc';
require	'error.inc';
require 'MDB2.php';
require 'phplib5.inc';
require 'sub.inc';

session_start();

//カレントセッション
$sess = new SessionCurrent('*admin.user.list.log');
$cur_ss = &$sess->vars;

$ref_sess = new SessionReference('*admin.user.list', SessionReference::MUST);
$ref_ss =& $ref_sess->vars;

$tmpl_arr = array();
$col = '';
$table = '';
$where = '';
$arrWhere = array();
$err_flg = NULL;

$db = new mysql_db();

//記事のid取得
$form = new Form;
$form->get->id = new FormFieldInt(FormField::TRIM | FormField::MBCONV | FormField::NOT_NULL);
$ret = $form->get();
$articleId = $ret['values']['id'];


$col = 'title,contents,description,article.image,date,category_name,writer.name';
$table = 'article LEFT OUTER JOIN category ON article.category_id = category.id LEFT OUTER JOIN writer ON article.writer_id = writer.id';
$where = 'article.id = '. $articleId;
$article_list = $db->select($col, $table, $where, $arrWhere);
if(empty($article_list)){
	$errorPage = "/error/404.html";
	header('Location: ' . $errorPage);
	exit();
}

//イメージをimgタグに追加
if(!empty($article_list[0]['image'])){
	$htmlImage = '<img src="' . $article_list[0]['image'] . '" alt="' . $article_list[0]['title'] . '">';
	$article_list[0]['image'] = $htmlImage;
}

$list_section = array(
	'article_list'	=> $article_list
);
$tmpl_arr += array(
	'list_section'		=> $list_section
);

$temp = new HTMLTemplate('detail.html');
echo $temp->replace($tmpl_arr);

?>