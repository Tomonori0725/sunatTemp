<?php declare(encoding = 'utf-8');
if (!defined('BACKEND_SERVER_CLASS_INC')): define('BACKEND_SERVER_CLASS_INC', 1);
/*****************************************************************************
  This is non public. 無断転載,無断使用を禁ず
  Copyright (C) 2009. SUNATMARK Co.,Ltd. All Right Reserved.

ファイル概要:
 在庫管理システム バックエンドサーバーライブラリ

$Id$
*****************************************************************************/

class BackendServer {

	const CACHE_BACKEND_SERVER_LIST = '/cache/backend_server_list.dat';


	//private static $_instance		= NULL ;
	private $user_backend_server	= NULL ;
	private $server_group			= NULL ;
	private $server_id				= NULL ;
	private $backend_server			= NULL ;
	private static $server_list		= array() ;
	
	

	/*----------------------------------------------------------------------------
	機能概要: コンストラクタ
	機能説明:
	引数:	なし
	戻り値:	なし
	----------------------------------------------------------------------------*/
	public function __construct($user_backend_server = NULL) {
		$this->user_backend_server = $user_backend_server;
		$this->setServerList();
	}

	/*----------------------------------------------------------------------------
	機能概要: インスタンス取得
	機能説明:
	引数:	なし
	戻り値:	BackendServerクラスのオブジェクト
	----------------------------------------------------------------------------*/
	/*
	protected static function getSelf() {
		return new self();
	}
	public static function getInstance() {
		if (is_null(self::$_instance)) {
			self::$_instance = static::getSelf();
		}
		return self::$_instance ;
	}
	*/

	/*----------------------------------------------------------------------------
	機能概要: バックエンドサーバーリストの設定
	機能説明:
		戻り先をセッションに保存する
	引数:	名前 	(I/O) 	説明

	戻り値:	なし
	----------------------------------------------------------------------------*/
	private function setServerList() {
		$chash = dirname(dirname(dirname(__FILE__))) . self::CACHE_BACKEND_SERVER_LIST;
		if (file_exists($chash)) {
			// キャッシュが見つかった
			$data = file_get_contents($chash);
			$data = unserialize($data);
		}
		else {
			// キャッシュが無い
			$db = new mysql_db();
			$cols = 'id, name, proxy_script_url, use_random, status, server_group';
			$from = 'backend_server';
			$rows = $db->select($cols, $from);
			$data = array(
				'use_random_id_list' => array(),
				'server_group_list'	 => array(),
				'server_list'		 => array()
			);
			foreach ($rows as $row) {
				$data['server_list'][$row['id']] = $row;
				if ($row['use_random'] == 1 && $row['status'] == 1) {
					$data['use_random_id_list'][] = $row['id'];

					$data['server_group_list'][$row['server_group']]['use_random_id_list'][] = $row['id'];

				}
				
			}
			if (!file_put_contents($chash, serialize($data))) {
				trigger_error('バックエンドサーバのキャッシュファイル作成に失敗しました。', E_USER_ERROR);
			}
		}
		
		$this->server_list = $data;
	}


	/*----------------------------------------------------------------------------
	機能概要: バックエンドサーバーグループの取得
	機能説明:
		
	引数:	名前 	(I/O) 	説明

	戻り値:	$backend_server_group
	----------------------------------------------------------------------------*/
	public function get_backend_server_group() {
		$backend_server_group = array();
		if (!is_null($this->user_backend_server) &&
			array_key_exists($this->user_backend_server, $this->server_list['server_list']) &&
			$this->server_list['server_list'][$this->user_backend_server]['status'] == 1) {
				$backend_server = $this->server_list['server_list'][$this->user_backend_server];
				$backend_server_group[] = $backend_server;
		}
		else {
			if (is_null($this->server_group)) {
				$this->server_group = array_rand($this->server_list['server_group_list']);
			}
		
			$backend_server_group = $this->server_list['server_group_list'][$this->server_group]['use_random_id_list'];
			
		}
		return $backend_server_group;
	
	}


	/*----------------------------------------------------------------------------
	機能概要: バックエンドサーバーの取得
	機能説明:
		戻り先をセッションに保存する
	引数:	名前 	(I/O) 	説明

	戻り値:	$backend_server
	----------------------------------------------------------------------------*/
	public function get_backend_server($backend_server_group) {
	
		if (count($backend_server_group) == 1) {
            $server_group = $backend_server_group[0];
		}
		else {
			$server_group_key = array_rand($backend_server_group);
			$server_group = $backend_server_group[$server_group_key];
		}

        $backend_server = $this->server_list['server_list'][$server_group];
		return $backend_server;
	
	}
	

}

endif;?>
