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
$sess = new SessionCurrent('*admin.user.list.index');
$cur_ss = &$sess->vars;

$ref_sess = new SessionReference('*admin.user.list', SessionReference::INIT);
$ref_ss =& $ref_sess->vars;


$tmpl_arr = array();
$search_writer = '';
$search_cate = '';
$serch_date = array(
	'start' => date("Y-m-d"),
	'end' => null
);

$where = '';
$arrWhere = array();

$db = new mysql_db();
$form = new Form;

//カテゴリー
$col = 'id,category_name';
$table = 'category';
$cate_list = $db->select($col, $table, $where, $arrWhere);
foreach($cate_list as $cate){$cate_id[] = $cate['id'];}

//作者
$col = 'id,name';
$table = 'writer';
$writer_list = $db->select($col, $table, $where, $arrWhere);
foreach($writer_list as $write){$write_id[] = $write['id'];}


$col = 'article.id,category_name,title,date';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	//絞り込み検索されたら...
    //バリデーション
	$form->post->category_id = new FormFieldSelect($cate_id);
	$form->post->writer_id   = new FormFieldSelect($write_id);
	try{
		$ret = $form->get();
		$cur_ss['serch'] = $ret;
		$search_date = $cur_ss['serch']['values'];
		$search_cate = $search_date['category_id'];
		$search_writer = $search_date['writer_id'];
		
		if(!empty($search_cate)){
			if($where) $where .= ' AND article.category_id = ' . $search_cate;
			else  $where = 'article.category_id = ' . $search_cate;
		}
		if(!empty($search_writer)){
			if($where) $where .= ' AND article.writer_id = ' . $search_writer;
			else $where = 'article.writer_id = ' . $search_writer;
		}
		if($where) $where .= ' ORDER BY article.date DESC';

	} catch (FormCheckException $e) {
        $tmpl_arr += $e->getValues();
	}

}else{
	//全記事一覧
}
$table = 'article LEFT OUTER JOIN category ON article.category_id = category.id';
if(empty($where)) $table .= ' ORDER BY article.date DESC';
$article_list = $db->select($col, $table, $where, $arrWhere);

$list_section = array(
	'article_list'	=> $article_list
);
$tmpl_arr += array(
	'list_section'		=> $list_section
);
//カテゴリー一覧
$tmpl_arr += array('cate_list'   => $cate_list);
//ライター一覧
$tmpl_arr += array('writer_list' => $writer_list);

$tmpl_arr['search_cate'] = $search_cate;
$tmpl_arr['search_writer'] = $search_writer;

$tmpl_arr += array('serch_date'   => $serch_date);

var_dump($tmpl_arr);

$temp = new HTMLTemplate('admin/edit/index.html');
echo $temp->replace($tmpl_arr);


?>