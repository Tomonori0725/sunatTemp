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


$col = 'title,contents,description,article.image,date,category_name,writer.name,writer.image AS prof_image,category.slug';
$table = 'article LEFT OUTER JOIN category ON article.category_id = category.id LEFT OUTER JOIN writer ON article.writer_id = writer.id';
$where = 'article.id = '. $articleId;
$article_list = $db->select($col, $table, $where, $arrWhere);
$article_list = $article_list[0];
if(empty($article_list)){
	$errorPage = "/error/404.html";
	header('Location: ' . $errorPage);
	exit();
}

//イメージをimgタグに追加
if(!empty($article_list['image'])){
	$htmlImage = '<img src="' . $article_list['image'] . '" alt="' . $article_list['title'] . '">';
	$article_list['image'] = $htmlImage;
}
//プロフィールイメージをimgタグに追加
if(!empty($article_list['prof_image'])){
	$profImage = '<img src="' . $article_list['prof_image'] . '" alt="' . $article_list['name'] . '">';
	$article_list['prof_image'] = $profImage;
}

//年月日
$date_arr = explode('-', $article_list['date']);
$article_list['date'] = array(
	'year'  => $date_arr[0],
	'month' => $date_arr[1],
	'day'   => $date_arr[2]
);


$list_section = array(
	'article_list'	=> $article_list
);
$tmpl_arr += array(
	'list_section'		=> $list_section
);

//var_dump($tmpl_arr);

$temp = new HTMLTemplate('detail.html');
echo $temp->replace($tmpl_arr);

?>