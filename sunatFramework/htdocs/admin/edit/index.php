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
$table = 'article LEFT OUTER JOIN category ON article.category_id = category.id';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	//絞り込み検索されたら...
	// 開始年月日のチェック
	$start_flug = 0;
	if(array_key_exists('start', $_POST)){
		if(($_POST['start']['year'] != '') || ($_POST['start']['month'] != '') || ($_POST['start']['day'] != '')){
			// 年月日いずれかが入力されていればすべて必須
			$start_flug = FormField::NOT_NULL;
		}
	}
	// 終了年月日のチェック
	$end_flug = 0;
	if(array_key_exists('end', $_POST)){
		if(($_POST['end']['year'] != '') || ($_POST['end']['month'] != '') || ($_POST['end']['day'] != '')){
			// 年月日いずれかが入力されていればすべて必須
			$end_flug = FormField::NOT_NULL;
		}
	}


    //バリデーション
	$form->post->category_id = new FormFieldSelect($cate_id);
	$form->post->writer_id   = new FormFieldSelect($write_id);
	$form->post->start       = new FormFieldDateTimeArray(FormField::TRIM | FormField::MBCONV | $start_flug, true);
	$form->post->end         = new FormFieldDateTimeArray(FormField::TRIM | FormField::MBCONV | $end_flug, true);

	try{
		$ret = $form->get();
		$cur_ss['serch'] = $ret;
		$search_date = $cur_ss['serch']['values'];
		$search_cate = $search_date['category_id'];
		$search_writer = $search_date['writer_id'];
		$search_s_date = $search_date['start'];
		$search_e_date = $search_date['end'];

		//カテゴリー
		if(!empty($search_cate)){
			if($where) $where .= ' AND article.category_id = ' . $search_cate;
			else  $where = 'article.category_id = ' . $search_cate;
		}
		//投稿者
		if(!empty($search_writer)){
			if($where) $where .= ' AND article.writer_id = ' . $search_writer;
			else $where = 'article.writer_id = ' . $search_writer;
		}

		//投稿日時
		// 開始・終了期間をDB用日付文字列へ変換する
		$search_s_date = !is_null($search_s_date) ? $search_s_date->getValue(array('format' => 'Y-m-d')) : '0000-00-00';	// 未入力(null)時は最古
		$search_e_date = !is_null($search_e_date) ? $search_e_date->getValue(array('format' => 'Y-m-d')) : '9999-99-99';	// 未入力(null)時は本日
		
		if($where) $where .= " AND '" . $search_s_date . "' <= article.date AND article.date <= '" . $search_e_date ."'";
		else $where = "'" . $search_s_date . "' <= article.date AND article.date <= '" . $search_e_date ."'";

		if($search_s_date == '0000-00-00'){
			$startArray = array(
				'0' => null,
				'1' => null,
				'2' => null
			);
		}else{
			$startArray = explode('-', $search_s_date);
		}
		if($search_e_date == '9999-99-99'){
			$endArray = array(
				'0' => null,
				'1' => null,
				'2' => null
			);
		}else{
			$endArray = explode('-', $search_e_date);
		}
		$serch_date = array(
			'start_y' => $startArray[0],
			'start_m' => $startArray[1],
			'start_d' => $startArray[2],
			'end_y'   => $endArray[0],
			'end_m'   => $endArray[1],
			'end_d'   => $endArray[2]
		);
		
		if($where) $where .= ' ORDER BY article.date DESC';
		else $where = ' ORDER BY article.date DESC';

	} catch (FormCheckException $e) {
        $tmpl_arr += $e->getValues();
	}

}else{
	//全記事一覧
	$serch_date = array(
		'start_y' => null,
		'start_m' => null,
		'start_d' => null,
		'end_y'   => null,
		'end_m'   => null,
		'end_d'   => null
	);
	$table .= ' ORDER BY article.date DESC';
}

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

$temp = new HTMLTemplate('admin/edit/index.html');
echo $temp->replace($tmpl_arr);


?>