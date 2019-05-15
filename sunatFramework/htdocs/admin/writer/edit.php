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
$form = new Form;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //フォームが送信されたら...
    //バリデーション
    $form->post->name       = new FormFieldString(FormField::TRIM | FormField::NOT_NULL);
    $form->post->profile    = new FormFieldString(FormField::TRIM | FormField::NOT_NULL);

    //画像をアップロード
    if(is_uploaded_file($_FILES['image']['tmp_name'])){
        $tmp = $_FILES['image']['tmp_name'];
        $fileName = date("YmdHis") . substr($_FILES['image']['name'], -4);
        $upload = "../../uploads/".$fileName;
        if(move_uploaded_file($tmp, $upload)){
            $imagePath = "/uploads/".$fileName;
        }else{//echo 'アップ失敗';
        }
    }else{
        //アップしない
        $imagePath = $cur_ss['preImage'];
    }

    if(!$imagePath){
        $form->post->image = new FormFieldFile($_FILES['image'], FormField::TRIM | FormField::NOT_NULL);
    }
    
	try {
        $ret = $form->get(); //エラーがあるかどうかもここで...
        $cur_ss['edit_input'] = $ret;
        if(!empty($imagePath)){
            $cur_ss['edit_input']['values']['image'] = $imagePath;
            $cur_ss['edit_input']['in']['image'] = $imagePath;
        }
		redirect('edit_confirm.php');
		
	} catch (FormCheckException $e) {
		$id = $cur_ss['id'];
        $tmpl_arr += $e->getValues();
    }

}else{
    //2回目post以外でのアクセス
    if (array_key_exists('edit_input', $cur_ss)) {
        $tmpl_arr = $cur_ss['edit_input']['in'];
        $id = $cur_ss['id'];
        //画像を引き継ぎ
        if(!empty($cur_ss['edit_input']['in']['image'])){
            $tmpl_arr['htmlImage'] = '<img src="' . $cur_ss['edit_input']['in']['image'] . '" alt="' . $cur_ss['edit_input']['in']['name'] . '">';
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
        $cur_ss['preImage'] = $edit_arr['image'];
        if(!empty($edit_arr['image'])){
            $edit_arr['htmlImage'] = '<img src="' . $edit_arr['image'] . '" alt="' . $edit_arr['name'] . '">';
        }else{
            $edit_arr['htmlImage'] = '';
        }

        $tmpl_arr = $edit_arr;
    }
}

$temp = new HTMLTemplate('admin/writer/edit.html');
echo $temp->replace($tmpl_arr);


?>