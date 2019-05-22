<?php
/*****************************************************************************
This is non public. 無断転載,無断使用を禁ず
Copyright (C) 2011. Sunatmark Co,.Ltd. All Right Reserved.

ファイル概要: ブログ　ブログ編集　新規作成　確認画面

$Id$
*****************************************************************************/

require	'const.inc';
require	'error.inc';
require 'MDB2.php';
require 'phplib5.inc';
require 'sub.inc';

session_start();

//カレントセッション
$sess = new SessionCurrent('*admin.user.list.article');
$cur_ss = &$sess->vars;

$ref_sess = new SessionReference('*admin.user.list', SessionReference::MUST);
$ref_ss =& $ref_sess->vars;

$db = new mysql_db();
$where = '';
$arrWhere = array();

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


if ($_SERVER['REQUEST_METHOD'] == 'POST') error('アクセスエラー', '不正なアクセスです1', '/admin/edit');
if (array_key_exists('add_input', $cur_ss)) {
    $tmpl_arr = $cur_ss['add_input']['values'];
    if (!empty($cur_ss['add_input']['values']['image'])) {
        $file_image = '<img src="' . $cur_ss['add_input']['values']['image'] . '" alt="' . $cur_ss['add_input']['values']['title'] . '">';
    }else{
        $file_image = '';
    }
    $tmpl_arr += array('file_image' => $file_image);
}
else redirect("/admin/edit/");

//カテゴリー
$tmpl_arr += array('cate_list'   => $cate_list);

//ライター
$tmpl_arr += array('writer_list' => $writer_list);

//var_dump($tmpl_arr);

$temp = new HTMLTemplate('admin/edit/add_confirm.html');
echo $temp->replace($tmpl_arr);


?>