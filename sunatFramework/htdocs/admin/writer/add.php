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

$sess = new SessionCurrent('*admin.user.list.article');
$cur_ss = &$sess->vars;

$ref_sess = new SessionReference('*admin.user.list', SessionReference::INIT);
$ref_ss =& $ref_sess->vars;

$tmpl_arr = array();
$where = '';
$arrWhere = array();

if(!empty($cur_ss['add_input']['values']['image'])){
    $imagePath = $cur_ss['add_input']['values']['image'];
}else{
    $imagePath = '';
}

$db = new mysql_db();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
//フォームが送信されたら...
//バリデーション?
    $form = new Form;
    $form->post->name       = new FormFieldString(FormField::TRIM | FormField::NOT_NULL);
    $form->post->profile    = new FormFieldString(FormField::TRIM | FormField::NOT_NULL);
    $form->post->image      = new FormFieldFile($_FILES['image'], FormField::TRIM | FormField::NOT_NULL);

    try {
        $ret = $form->get();
        
        if(is_uploaded_file($_FILES['image']['tmp_name'])){
            $tmp = $_FILES['image']['tmp_name'];
            $fileName = date("YmdHis") . substr($_FILES['image']['name'], -4);
            $upload = "../../uploads/" . $fileName;
            if(move_uploaded_file($tmp, $upload)){
                $imagePath = "/uploads/".$fileName;
            }else{
                //echo 'アップ失敗';
            }
        }else{
            //echo 'そもそもファイルきてない';
        }

        $cur_ss['add_input'] = $ret;
        $cur_ss['add_input']['values']['image'] = $imagePath;
        $cur_ss['add_input']['in']['image'] = $imagePath;
		redirect('add_confirm.php');
	} catch (FormCheckException $e) {
		$tmpl_arr += $e->getValues();
	}
}else{
    //戻るなどの処理で来た時
    if (array_key_exists('add_input', $cur_ss)) {
        $tmpl_arr = $cur_ss['add_input']['in'];
	}
    else {
        //初期値
        $tmpl_arr = array(
			'name'	    => '',
			'profile'	=> '',
			'image'		=> ''
        );
    }
}

$temp = new HTMLTemplate('admin/writer/add.html');
echo $temp->replace($tmpl_arr);


?>