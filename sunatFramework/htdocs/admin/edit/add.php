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

$dateInit = new DateTime();
//$dateInit->format('Y-m-d');
$year = date('Y');
$month = date('m');
$day = date('d');
if(!empty($cur_ss['add_input']['values']['image'])){
    $imagePath = $cur_ss['add_input']['values']['image'];
}else{
    $imagePath = '';
}


$db = new mysql_db();

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
//バリデーション?
    $form = new Form;
    $form->post->title       = new FormFieldString(FormField::TRIM | FormField::NOT_NULL);
    $form->post->category_id = new FormFieldSelect($cate_id, FormField::NOT_NULL);
    $form->post->description = new FormFieldString(FormField::TRIM);
    $form->post->contents    = new FormFieldString(FormField::TRIM | FormField::NOT_NULL);
    $form->post->date        = new FormFieldDateTimeArray(FormField::NOT_NULL);
    $form->post->writer_id   = new FormFieldSelect($write_id, FormField::NOT_NULL);
    $form->post->image       = new FormFieldFile($_FILES['image'], FormField::TRIM);

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

    try {
        $ret = $form->get();

        $cur_ss['add_input'] = $ret;
        $cur_ss['add_input']['values']['image'] = $imagePath;
        $cur_ss['add_input']['in']['image'] = $imagePath;
        $cur_ss['add_input']['values']['file'] = $_FILES;
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
			'title'			=> '',
			'category_id'	=> '1',
			'description'	=> '',
			'contents'		=> '',
			'image'			=> array(),
			'date'			=> array('year' => $year, 'month' => $month, 'day' => $day),
			'writer_id'		=> $writer_list[0]['id'],
			'imgDel'		=> 'false',
        );
    }
}

//カテゴリー
$tmpl_arr += array('cate_list'	 => $cate_list);
//ライター
$tmpl_arr += array('writer_list' => $writer_list);

//画像が登録されていたら、、、
$tmpl_arr['htmlImage'] = '';
if(!empty($imagePath)){
    $cur_ss['add_input']['values']['image'] = $imagePath;
    $cur_ss['add_input']['in']['image'] = $imagePath;
    $tmpl_arr['htmlImage'] = '<img src="' . $imagePath . '" alt="' . $tmpl_arr['title'] . '"><input type="checkbox" id="ImgDel" name="imgDel" value="true"><label for="ImgDel">削除</label>';
}
if(!empty($imgDel)) $imagePath = '';

//echo $imagePath;
//var_dump($tmpl_arr);

$temp = new HTMLTemplate('admin/edit/add.html');
echo $temp->replace($tmpl_arr);


?>