<?php
/*****************************************************************************
This is non public. 無断転載,無断使用を禁ず
Copyright (C) 2011. Sunatmark Co,.Ltd. All Right Reserved.

ファイル概要: ブログ　ブログ編集　投稿者編集　新規作成　確認画面

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


if ($_SERVER['REQUEST_METHOD'] == 'POST') error('アクセスエラー', '不正なアクセスです1', '/admin/writer');
if (array_key_exists('add_input', $cur_ss)) {
    $tmpl_arr = $cur_ss['add_input']['values'];
    if (!empty($cur_ss['add_input']['values']['image'])) {
        $file_image = '<img src="' . $cur_ss['filepass'] . '" alt="' . $cur_ss['add_input']['values']['name'] . '">';
    }else{
        $file_image = '';
    }
    $tmpl_arr += array('file_image' => $file_image);
}
else redirect("/admin/writer/");

$temp = new HTMLTemplate('admin/writer/add_confirm.html');
echo $temp->replace($tmpl_arr);


?>