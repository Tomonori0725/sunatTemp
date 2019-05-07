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
$imagePath = '';

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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //フォームが送信されたら...
    //バリデーション
    $form->post->title       = new FormFieldString(FormField::TRIM | FormField::NOT_NULL);
    $form->post->category_id = new FormFieldSelect($cate_id, FormField::NOT_NULL);
    $form->post->description = new FormFieldString(FormField::TRIM);
    $form->post->contents    = new FormFieldString(FormField::TRIM | FormField::NOT_NULL);
    $form->post->date        = new FormFieldDateTimeArray(FormField::NOT_NULL);
    $form->post->writer_id   = new FormFieldSelect($write_id, FormField::NOT_NULL);
    $form->post->image       = new FormFieldFile($_FILES['image'], FormField::TRIM);
    $form->post->imgDel      = new FormFieldBool(FormField::TRIM);
    
    //画像をアップロード
    if(is_uploaded_file($_FILES['image']['tmp_name'])){
        $tmp = $_FILES['image']['tmp_name'];
        $fileName = date("YmdHis") . substr($_FILES['image']['name'], -4);
        $upload = "../../uploads/" . $fileName;
        if(move_uploaded_file($tmp, $upload)){
            $imagePath = "/uploads/".$fileName;
        }else{//echo 'アップ失敗';
        }
    }else{
        //アップしない
        $imagePath = $cur_ss['preImage'];
    }

	try {
		$ret = $form->get();
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
            $tmpl_arr['htmlImage'] = '<img src="' . $cur_ss['edit_input']['in']['image'] . '" alt="' . $cur_ss['edit_input']['in']['title'] . '">';
        }
        //日付
        //$date_arr = explode('-', $edit_arr['date']);
        $year = $tmpl_arr['date']['year'];
        $month = $tmpl_arr['date']['month'];
        $day = $tmpl_arr['date']['day'];
        $tmpl_arr['post_date'] = array('year' => $year, 'month' => $month, 'day' => $day);

	}else {
        //初めてのアクセス
        $form->get->id = new FormFieldInt(FormField::TRIM | FormField::MBCONV | FormField::NOT_NULL);
		try {
			$ret = $form->get();
			$id = $ret['values']['id'];
			$cur_ss['id'] = $id;
		} catch (FormCheckException $e) {
			error('アクセスエラー', '不正なアクセスです', '/admin/edit/');
		}

        $col = <<< COL
            category_id,title,description,contents,image,date,writer_id,imgDel
COL;
        $table = 'article';
        $edit_arr = $db->getRow($col, $table, 'article.id = ?', array($id));

        if (!$edit_arr) error('アクセスエラー', '不正なアクセスです', '/admin/edit/');

        //投稿日時を使いやすい型にする(dateTime→string)
        $date_arr = explode('-', $edit_arr['date']);
        $edit_arr['date'] = array(
            'year'  => $date_arr[0],
            'month' => $date_arr[1],
            'day'   => $date_arr[2]
        );
        
        //画像にimgタグをつける
        $cur_ss['preImage'] = $edit_arr['image'];
        if(!empty($edit_arr['image'])){
            $edit_arr['htmlImage'] = '<img src="' . $edit_arr['image'] . '" alt="' . $edit_arr['title'] . '">';
        }else{
            $edit_arr['htmlImage'] = '';
        }

        $tmpl_arr = $edit_arr;

    }
}

//カテゴリー
$tmpl_arr += array('cate_list'   => $cate_list);

//ライター
$tmpl_arr += array('writer_list' => $writer_list);

$temp = new HTMLTemplate('admin/edit/edit.html');
echo $temp->replace($tmpl_arr);


?>