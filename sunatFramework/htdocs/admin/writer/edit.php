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

$sess = new SessionCurrent('*admin.user.list.edit');
$cur_ss = &$sess->vars;

$ref_sess = new SessionReference('*admin.user.list', SessionReference::INIT);
$ref_ss =& $ref_sess->vars;

$tmpl_arr = array();
$where = '';
$arrWhere = array();

$imagePath = '';

$db = new mysql_db();
$table = 'writer';
$form = new Form;
$uploadDir    = dirname(dirname(__FILE__)) . '/../uploads/';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //フォームが送信されたら...
    //バリデーション
    $form->post->name       = new FormFieldNameDbDuplicate(FormField::TRIM | FormField::NOT_NULL, $db, $table, 'name', array('id', $cur_ss['id']));
    $form->post->profile    = new FormFieldString(FormField::TRIM | FormField::NOT_NULL);
    $form->post->image      = new FormFieldFileUp(FormField::TRIM, $uploadDir, '/image/');
    
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

        $cur_ss['edit_input'] = $ret;
        //画像のパスを保持する
        if(!empty($tmpl_arr['image']['up'])){
            $cur_ss['filepass'] = '/uploads/' . $tmpl_arr['image']['up']['temp_name'];
        }
		redirect('edit_confirm.php');
		
	} catch (FormCheckException $e) {
		$id = $cur_ss['id'];
        $tmpl_arr += $e->getValues();
        //画像のパスを保持する
        if(!empty($tmpl_arr['image']['up'])){
            $cur_ss['filepass'] = '/uploads/' . $tmpl_arr['image']['up']['temp_name'];
        }
    }

}else{
    //2回目post以外でのアクセス
    if (array_key_exists('edit_input', $cur_ss)) {
        $tmpl_arr = $cur_ss['edit_input']['in'];
        $id = $cur_ss['id'];
        //画像を引き継ぎ
        if(!empty($cur_ss['filepass'])){
            $tmpl_arr['htmlImage'] = '<img src="' . $cur_ss['filepass'] . '" alt="' . $cur_ss['edit_input']['in']['name'] . '">';
        }

	}else {
        //初めてのアクセス
        $form->get->id = new FormFieldInt(FormField::TRIM | FormField::MBCONV | FormField::NOT_NULL);
		try {
			$ret = $form->get();
			$id = $ret['values']['id'];
			$cur_ss['id'] = $id;
		} catch (FormCheckException $e) {
			error('アクセスエラー', '不正なアクセスです1', '/admin/edit/');
		}

        $col = <<< COL
            name,profile,image
COL;
        $table = 'writer';
        $edit_arr = $db->getRow($col, $table, 'id = ?', array($id));

        if (!$edit_arr){
            $listPage = "/admin/writer/";
            header('Location: ' . $listPage);
            exit();
        }
        
        
        //画像にimgタグをつける
        $cur_ss['filepass'] = $edit_arr['image'];
        $cur_ss['preImage'] = $edit_arr['image'];
        $tmpl_arr = $edit_arr;
        $tmpl_arr['image'] = array("current" => null, "up" => null, "delete" => false, "exist" => false, "name" => null, "temp_name" => null, "path" => null);
    }
}

if(!empty($cur_ss['filepass'])){
    $tmpl_arr['htmlImage'] = '<img src="' . $cur_ss['filepass'] . '" alt="' . $tmpl_arr['name'] . '">';
}else{
    $tmpl_arr['htmlImage'] = '';
}

//var_dump($tmpl_arr);
//var_dump($cur_ss);

$temp = new HTMLTemplate('admin/writer/edit.html');
echo $temp->replace($tmpl_arr);


?>