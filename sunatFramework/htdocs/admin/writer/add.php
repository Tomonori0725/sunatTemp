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
require 'sub_app.inc';

session_start();

$sess = new SessionCurrent('*admin.user.list.article');
$cur_ss = &$sess->vars;

$ref_sess = new SessionReference('*admin.user.list', SessionReference::INIT);
$ref_ss =& $ref_sess->vars;

$tmpl_arr = array();
$where = '';
$arrWhere = array();

$err_flg = NULL;

$db = new mysql_db();
$table = 'writer';

$uploadDir    = dirname(dirname(__FILE__)) . '/../uploads/';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
//フォームが送信されたら...
//バリデーション?
    $form = new Form;
    $form->post->name       = new FormFieldNameDbDuplicate(FormField::TRIM | FormField::NOT_NULL, $db, $table, 'name');
    $form->post->profile    = new FormFieldString(FormField::TRIM | FormField::NOT_NULL);
    $form->post->image      = new FormFieldFileUp(FormField::NOT_NULL, $uploadDir, '/image/');

    try {
        $ret = $form->get();
        //TODO アップロードファイルが変更された場合、前のアップロードファイルを削除(unlink)する
        if(!empty($tmpl_arr['image']['up'])){
            if (array_key_exists('add_input', $cur_ss)) {
                $prev_path = $cur_ss['add_input']['values']['image']['path'];
                $current_path = $ret['values']['image']['path'];

                if ($prev_path != $current_path) {
                    @unlink($prev_path);
                }
            }
        }

        $tmpl_arr = $ret['in'];
        
        $cur_ss['add_input'] = $ret;
        //画像のパスを保持する
        if(!empty($tmpl_arr['image']['up'])){
            $cur_ss['filepass'] = '/uploads/' . $tmpl_arr['image']['up']['temp_name'];
        }

		redirect('add_confirm.php');
	} catch (FormCheckException $e) {
        $tmpl_arr += $e->getValues();
        //画像のパスを保持する
        if(!empty($tmpl_arr['image']['up'])){
            $cur_ss['filepass'] = '/uploads/' . $tmpl_arr['image']['up']['temp_name'];
        }
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
			'image'		=> array("current" => null, "up" => null, "delete" => false, "exist" => false, "name" => null, "temp_name" => null, "path" => null)
        );
    }
}

if(!empty($tmpl_arr['image']['up']['temp_name'])){
    $file_pass = $tmpl_arr['image']['up']['temp_name'];
}


if(!empty($tmpl_arr['image']['up']) || !empty($tmpl_arr['image']['file'])){
    $tmpl_arr['htmlImage'] = '<img src="' . $cur_ss['filepass'] . '" alt="' . $tmpl_arr['name'] . '">';
}else{
    $tmpl_arr['htmlImage'] = '';
}

//var_dump($tmpl_arr);
//var_dump($cur_ss);

$temp = new HTMLTemplate('admin/writer/add.html');
echo $temp->replace($tmpl_arr);


?>