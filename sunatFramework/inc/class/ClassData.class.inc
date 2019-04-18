<?php if (!defined('CLASS_DATA_INC')): define('CLASS_DATA_INC', 1);
/*****************************************************************************
  This is non public. 無断転載,無断使用を禁ず
  Copyright (C) 2009. SUNATAMRK Co.,Ltd. All Right Reserved.

ファイル概要:
  メーカーテーブルデータ

$Id$
*****************************************************************************/

/*----------------------------------------------------------------------------
クラス概要: クラスデータ
クラス説明:
	クラスデータを保持するクラス
----------------------------------------------------------------------------*/
class ClassData {
	private $list;

	/*----------------------------------------------------------------------------
	機能概要: クラスデータオブジェクトのコンストラクタ
	機能説明:
		クラスデータのオブジェクトのコンストラクタ
	
	引数:
		名前 (I/O) 説明
		$db      (I) データベースオブジェクト
		$classno (I) 種別配列
	
	戻り値:
		なし
	----------------------------------------------------------------------------*/
	public function __construct($db, $classno = array()) {
		
		if (count($classno)) {
			$classno_str = implode(',', $classno);
			$where = "classno IN($classno_str)";
		}
		$db->setOrder('dispseq ASC');
		$rows = $db->select("classno, id, dispseq, display, face", "classdata", $where);
		$db->setOrder('');

		$this->list = array();
		foreach ($rows as $row) {
			$this->list[$row['classno']][$row['id']] = $row;
		}

	}
	
	/*----------------------------------------------------------------------------
	機能概要: 種別データ番号列取得
	機能説明:
		種別に対するデータの番号のリストを取得
	
	引数:
		名前 (I/O) 説明
		$classno (I) 種別
		$visible_only	(I)		表示ONのみか。省略された場合ONのもののみ。
	
	戻り値:
		種別に対するデータの番号のリスト
	----------------------------------------------------------------------------*/
	function getIdList($classno, $visible_only = TRUE) {
		if ($visible_only) {
			$id_list = array();
			foreach ($this->list[$classno] as $id => $datas) {
				if ($datas['display'] == TRUE) $id_list[] = $id;
			}
			return $id_list;
		} else {
			return array_keys($this->list[$classno]);
		}
	}
	
	/*----------------------------------------------------------------------------
	機能概要: 種別データ列取得
	機能説明:
		種別に対するデータのリストを取得
	
	引数:
		名前 (I/O) 説明
		$classno (I) 種別
		$visible_only	(I)		表示ONのみか。省略された場合ONのもののみ。
	
	戻り値:
		種別に対するデータのリスト
	----------------------------------------------------------------------------*/
	function getFaceList($classno, $visible_only = TRUE) {
		$var_list = array();
		foreach ($this->list[$classno] as $id => $datas) {
			if (!$visible_only || $datas['display'] == TRUE) {
				$var_list[] = $datas['face'];
			}
		}
		return $var_list;
	}
	
	/*----------------------------------------------------------------------------
	機能概要: 種別データ番号、列取得
	機能説明:
		種別に対する値とデータのリストを取得
	
	引数:
		名前 (I/O) 説明
		$classno (I) 種別
		$visible_only	(I)		表示ONのみか。省略された場合ONのもののみ。
	
	戻り値:
		種別に対するデータのリスト
	----------------------------------------------------------------------------*/
	function getIdFaceList($classno, $visible_only = TRUE) {
		$var_list = array();
		foreach ($this->list[$classno] as $id => $datas) {
			if (!$visible_only || $datas['display'] == TRUE) {
				$var_list[] = array(
					'id'	=> $datas['id'],
					'face'	=> $datas['face']
				);
			}
		}
		return $var_list;
	}
	
	/*----------------------------------------------------------------------------
	機能概要: 種別データ取得
	機能説明:
		指定された番号より種別に対するデータを取得
	
	引数:
		名前 (I/O) 説明
		$classno (I) 種別
		$no				(I)		id
		$visible_only	(I)		表示ONに限るか。省略された場合ONに限る。
	
	戻り値:
		種別に対する全データ
	----------------------------------------------------------------------------*/
	function getFace($classno, $no, $visible_only = TRUE) {
		if (array_key_exists("$no", $this->list[$classno]) &&
			(!$visible_only || $this->list[$classno]["$no"]['display'] == TRUE))
				return $this->list[$classno]["$no"]['face'];
		else
				return FALSE;
	}
}

endif?>
