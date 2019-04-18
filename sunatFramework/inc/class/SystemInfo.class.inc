<?php if (!defined('SYSTEM_INFO_INC')): define('SYSTEM_INFO_INC', 1);
/*****************************************************************************
  This is non public. 無断転載,無断使用を禁ず
  Copyright (C) 2009. SUNATAMRK Co.,Ltd. All Right Reserved.

ファイル概要:
  システム情報テーブルデータ

$Id$
*****************************************************************************/
$_SystemInfo = NULL;

/*----------------------------------------------------------------------------
クラス概要: システム情報
クラス説明:
	システム情報を保持するクラス
----------------------------------------------------------------------------*/
class SystemInfo {

	var $list;

	/*----------------------------------------------------------------------------
	機能概要: コンストラクタ
	機能説明:
	引数:
		名前 (I/O) 説明
		$db      (I) データベースオブジェクト
	戻り値: なし
	----------------------------------------------------------------------------*/	
	function __construct($reload = NULL) {
		global $_SystemInfo;
		$list = array();
		if (!is_null($reload) || is_null($_SystemInfo)) {
			$db = new mysql_db;
			/*
			$sql = <<<EOSQL
SELECT	name, type, value, subject, index 
FROM	system_info
EOSQL;
			$res = $db->selectall_hash($sql) ;
		*/
			
			$res = $db->select("name, type, value, subject, idx", "system_info") ;
			foreach ($res as $row) {
				$val = $row['value'];
				if (!is_null($val)) {
					switch (strtoupper($row['type'])) {
						case 'INT':
						case 'INTEGER':
							$val = (int)$val;
							break;
						case 'TEXT':
						case 'CHAR':
						case 'VARCHAR':
							$val = (string)$val;
							break;
						case 'BOOL':
						case 'BOOLEAN':
							if (strtoupper($val) == 'TRUE')
								$val = true;
							elseif (strtoupper($val) == 'FALSE')
								$val = false;
							else
								$val = (bool)$val;
							break;
						/*
						case 'TIMESTAMP':
						case 'INTERVAL':
						*/
					}
				}
				
				if (is_null($row['idx']))
					$this->list[$row['name']] = $val;
				else
					$this->list[$row['name']][$row['idx']] = $val;
			}
			$_SystemInfo = $this->list;
			
		}
		else {
			$this->list = $_SystemInfo;
		}		
	}
	
	/*----------------------------------------------------------------------------
	機能概要: value取得
	機能説明:
	引数:
		名前 (I/O) 説明
		$name	(I) 取得するname
		$index	(I) 取得するindex。省略可。省略した場合でindexを持っているものは配列を返す
	戻り値: 指定されたname、indexに対応するvalueまたはvalueのリスト
	----------------------------------------------------------------------------*/	
	function getValue($name, $index = NULL) {
		if (!array_key_exists($name, $this->list)) return NULL;
		elseif (is_null($index)) return $this->list[$name];
		elseif (array_key_exists($index, $this->list[$name])) return $this->list[$name][$index];
		else return NULL;  
	}
	
}

endif?>
