<?php declare(encoding = 'utf-8');
if (!defined('LIST_CLUSTER_CLASS_INC')): define('LIST_CLUSTER_CLASS_INC', 1);
/*****************************************************************************
  This is non public. 無断転載,無断使用を禁ず
  Copyright (C) 2009. SUNATMARK Co.,Ltd. All Right Reserved.

ファイル概要:
 リストクラスター基底クラス

$Id$
*****************************************************************************/

class ListCluster {

    protected $mode;
    protected $mode_str;
    protected $root_path;
    protected $saved_dir;
    protected $list_dir;
    protected $temp_dir;
    protected $arrColumn;
    protected $arrSubColumn;
    protected $arrMasterColumn;

    const COUNT_FILE = 'count_file';
    const KEY_LIST = 'key_list';
    const MASTER = 'master';
    const RE_MASTER = 're_master';

    /*----------------------------------------------------------------------------
    機能概要: コンストラクタ
    機能説明:
    引数:	なし
    戻り値:	なし
    ----------------------------------------------------------------------------*/
    public function __construct() {
        $this->root_path = dirname(dirname(dirname(__FILE__)));
        $this->saved_dir = $this->root_path . '/saved';
        $this->list_dir = $this->saved_dir . '/' . $this->mode_str;
        //$this->temp_dir = $this->list_dir . '/temp';

    }

    /*----------------------------------------------------------------------------
    機能概要: インスタンス取得
    機能説明:
    引数:	なし
    戻り値:	ListClusterクラスのオブジェクト
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

    /**
     * 機能概要： 保存済みリスト一覧の取得
     * 機能説明：
     *      保存済みリスト一覧を取得する
     *
     * @param mysql_db $db
     * @param $user_id
     * @return array|null
     */
    protected function getSaveList(mysql_db $db, $user_id) {

        if ($this->mode == 1) {
            $col = 'core_search_result_save';
        } else {
            $col = 'book_search_result_save';
        }
        $search_result_save = $db->get($col, 'user', 'id = ?', array($user_id));


        $col = <<< EOF
            favorite_search_terms.id, subject, core_book, total_count, tel_count, mail_count, list_terms, qid,
            CASE 
                WHEN last_execute_datetime = '0000-00-00 00:00:00' THEN TIME_TO_SEC(DATEDIFF(NOW(), create_datetime))
                ELSE TIME_TO_SEC(DATEDIFF(NOW(), last_execute_datetime))
            END AS elapsed,
            favorite_search_terms.create_datetime,
            favorite_search_terms.last_execute_datetime,
            CASE 
                WHEN favorite_search_terms.execute_datetime = '0000-00-00 00:00:00' THEN ''
                ELSE favorite_search_terms.execute_datetime
            END AS execute_datetime,
            (SELECT execute_status FROM auto_search_que WHERE favorite_search_terms = favorite_search_terms.id AND auto_search_que.del_flg = 0 AND auto_search_que.start_datetime IS NOT NULL LIMIT 1 OFFSET 0) AS execute_status
EOF;

        $table = 'favorite_search_terms';
        $where = 'user = ? AND core_book = ?';
        $arrList = $db->select($col, $table, $where, array($user_id, $this->mode));

        foreach ($arrList as $key => $item) {
            $list_terms = json_decode($item['list_terms'], true);
            if (is_array($list_terms)) {
                if (!$search_result_save) {
                    $list_terms['saved'] = 0;
                }
                $item['list_terms'] = $list_terms;
                $arrList[$key] = $item;
            }

            $item['master_exists'] = $this->checkMaster($user_id, $item['qid']);
            $arrList[$key] = $item;
        }

        $col = 'COUNT(favorite_search_terms.id) AS list_count, SUM(total_count) AS target_count';
        $arrCount = $db->getRow($col, $table, $where, array($user_id, $this->mode));


        //コア・ワイド合計ターゲット件数の取得
        $col = 'user.search_history_save_number - SUM(COALESCE(total_count, 0)) AS rest_target_count';
        $table = 'user LEFT JOIN favorite_search_terms ON user.id = favorite_search_terms.user';
        $rest_target_count = $db->get($col, $table, 'user.id = ?', array($user_id));
        if ($rest_target_count < 0) {
            $arrCount['rest_target_count'] = 0;
        } else {
            $arrCount['rest_target_count'] = $rest_target_count;
        }

        return array($arrList, $arrCount);
    }


    public function make_temp_dir($user_id, $qid) {
        $user_dir = $this->list_dir . '/' . $user_id;
        if (!is_dir($user_dir)) {
            mkdir($user_dir, 0777);
            chmod($user_dir, 0777);
        }

        $temp_dir = $user_dir . '/' . $qid;
        if (!is_dir($temp_dir)) {
            mkdir($temp_dir, 0777);
            chmod($temp_dir, 0777);
        }
    }


    /**
     * 保存マスタがあるかチェック
     *
     * @param $user_id
     * @param $qid
     * @return int
     */
    public function checkMaster($user_id, $qid) {
        $status = 0;

        if ($this->check_temp_dir($user_id, $qid)) {
            if (file_exists($this->temp_dir . '/' . self::MASTER)) {
                $status = 1;
            }
        }

        return $status;
    }

    /**
     * 再検索保存マスタがあるかチェック
     *
     * @param $user_id
     * @param $qid
     * @return int
     */
    public function checkReMaster($user_id, $qid) {
        $status = 0;

        if ($this->check_temp_dir($user_id, $qid)) {
            if (file_exists($this->temp_dir . '/' . 'que')) {
                if (file_exists($this->temp_dir . '/' . self::RE_MASTER)) {
                    $status = 1;
                } else {
                    $status = 2;
                }
            }
        }

        return $status;
    }


    /**
     * マスタリストの保存
     *
     * @param $qid
     * @param $subject
     * @return int
     */
    public function saveMaster($qid, $subject) {
        $env = new Env();

        $login_info  = $_SESSION['login_info'];
        $user_id     = $login_info['id'];
        $user_member = $login_info['user_member'];
        if ($this->mode == 1) {
            $search_result_save = $login_info['core_search_result_save'];
            $arrCond = $_SESSION['q'][$qid]['arrCond'];
        } else {
            $search_result_save = $login_info['book_search_result_save'];
            $arrCond = $_SESSION['bq'][$qid]['arrCond'];
        }
        $db = new mysql_db();
        $table = 'favorite_search_terms';
        $regist_cnt = $db->count($table, 'user = ?', array($user_id));

        if ($regist_cnt <= $env->search->regist_limit) {
            $length = strlen($subject);
            if ($length == 0 || $length > 80) {
                return -2;	//文字数エラー
            }
        } else {
            //return -1;	//100件存在したため保存不可
        }

        $saved = 0;
        if ($search_result_save == 1) {
            $saved = 1;
        }

        $list_terms = array(
            'saved' => $saved
        );

        $arrSqlVal = array(
            'user'               => $user_id,
            'core_book'          => $this->mode,
            'subject'            => $subject,
            'search_terms'       => json_encode($arrCond),
            'list_terms'         => json_encode($list_terms),
            'create_user_member' => $user_member,
            'update_user_member' => $user_member,
            'qid'                => $qid
        );

        $arrCount = array(
            'total_count' => 0,
            'tel_count' => 0,
            'mail_count' => 0
        );
        if ($saved) {
            $arrCount = $this->get_master_total($user_id, $qid);
        }
        if ($arrCount) {
            $arrSqlVal += array(
                'total_count'  => $arrCount['total_count'],
                'tel_count'    => $arrCount['tel_count'],
                'mail_count'   => $arrCount['mail_count'],
            );
        }

        //検索条件登録件数の取得
        $table = 'favorite_search_terms';
        $db->insert($table, $arrSqlVal);
        if ($saved) {
            $favorite_search_terms = $db->lastInsertID();
            $this->add_search_que($db, $favorite_search_terms, $qid);

            if ($this->check_que($user_id, $qid)) {
                $this->stop_search_que($db, $user_id, $qid);
            }
        }

        if (!$saved) {
            $this->deleteMasterFile($user_id, $qid);
        }

        //リスト管理画面用の情報生成
        $this->add_terms_tree($db, $user_id, $subject);
        if ($saved) {
            $status = 1;    //リスト保存成功
        } else {
            $status = 2;    //検索条件保存
        }
        return $status;
    }


    /**
     * @param $user_id
     * @param $qid
     * @return mixed
     */
    public function get_temp_total($user_id, $qid) {
        $user_dir = $this->list_dir . '/' . $user_id;
        $temp_dir = $user_dir . '/' . $qid;
        $file = $temp_dir . '/' . self::COUNT_FILE;
        $count_file = json_decode(file_get_contents($file), true);

        return $count_file;
    }


    /**
     * リスト管理画面用データ追加
     *
     * @param mysql_db $db
     * @param $user_id
     * @param $subject
     * @return boolean
     */
    public function add_terms_tree(mysql_db $db, $user_id, $subject) {
        $table = 'favorite_search_terms';

        $id = $db->max('id', $table, 'user = ? AND core_book = ?', array($user_id, $this->mode));
        if ($this->mode == 1) {
            $col = 'search_terms_tree';
        } else {
            $col = 'book_search_terms_tree';
        }

        $terms_tree = $db->get($col, 'user', 'id = ?', array($user_id));
        $terms_data = array();
        if ($terms_tree) {
            $terms_data = unserialize($terms_tree);
        }
        $terms_data[] = $subject . '<span class="terms_id">' . $id . '</span>';

        $terms_tree = serialize($terms_data);
        $db->update('user', array($col => $terms_tree), 'id = ?', array($user_id));

        return true;
    }


    /**
     * ディレクトリがあるか判定。
     *
     * @param $user_id
     * @param $qid
     * @return bool
     */
    public function check_temp_dir($user_id, $qid) {
        if (is_null($qid)) {
            return false;
        }
        $user_dir = $this->list_dir . '/' . $user_id;
        $temp_dir = $user_dir . '/' . $qid;
        if (file_exists($temp_dir)) {
            $this->temp_dir = $temp_dir;
            return true;
        }

        return false;
    }

    /**
     * ディレクトリを再帰的に削除
     *
     * @param null $dir
     */
    public function remove_dir( $dir = null ) {

        if (is_null($dir)) {
            $dir = $this->temp_dir;
        }

        if (!file_exists($dir)) {
            return;
        }

        $cnt = 0;

        $handle = opendir($dir);
        if (!$handle) {
            return ;
        }

        while (false !== ($item = readdir($handle))) {
            if ($item === "." || $item === "..") {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $item;

            if (is_dir($path)) {
                // 再帰的に削除
                $cnt = $cnt + $this->remove_dir($path);
            }
            else {
                // ファイルを削除
                unlink($path);
            }
        }
        closedir($handle);

        // ディレクトリを削除
        if (!rmdir($dir)) {
            return ;
        }
    }


    /**
     * マスタリストの取得
     *
     * @param $user_id
     * @param $qid
     * @param $key_init
     * @return bool|mixed
     */
    public function getMasterList($user_id, $qid, $key_init = true) {
        if (!$this->check_temp_dir($user_id, $qid)) {
            return false;
        }

        if (!file_exists($this->temp_dir . '/' . self::MASTER)) {
            return false;
        }

        $arrMaster = json_decode(file_get_contents($this->temp_dir . '/' . self::MASTER), true);
        if ($this->mode == 1) {
            $idx_key = 'idx';
        } else {
            $idx_key = 'i_idx';
        }
        $i = 1;

        if (!$arrMaster) {
            return false;
        }
        foreach ($arrMaster as $m_key => $row) {
            $row[$idx_key] = $i;
            $arrMaster[$m_key] = $row;
            $i++;
        }
        if ($key_init) {
            return array_values($arrMaster);
        } else {
            return $arrMaster;
        }
    }

    /**
     * 再検索マスタリストの取得
     *
     * @param $user_id
     * @param $qid
     * @param $key_init
     * @return bool|mixed
     */
    public function getReMasterList($user_id, $qid, $key_init = true) {
        if (!$this->check_temp_dir($user_id, $qid)) {
            return false;
        }

        if (!file_exists($this->temp_dir . '/' . self::RE_MASTER)) {
            return false;
        }
        $arrReMaster = json_decode(file_get_contents($this->temp_dir . '/' . self::RE_MASTER), true);

        if ($key_init) {
            return array_values($arrReMaster);
        } else {
            return $arrReMaster;
        }

    }

    /**
     * 検索結果保存かどうかチェック
     *
     * @param $user_id
     * @param $qid
     * @return mixed
     */
    public function check_saved($user_id, $qid) {
        $db = new mysql_db();

        $list_terms = $db->get('list_terms', 'favorite_search_terms', 'user = ? AND qid = ?', array($user_id, $qid));
        $arrListTerms = json_decode($list_terms, true);

        return $arrListTerms['saved'];
    }

    /**
     *
     * 保存している検索条件を取得する
     *
     * @param $db
     * @param $user_id
     * @param $qid
     * @return array 検索条件
     */
    public function get_search_terms(mysql_db $db, $user_id, $qid) {

        $search_terms = $db->get('search_terms', 'favorite_search_terms', 'user = ? AND qid = ?', array($user_id, $qid));
        $arrCond = json_decode($search_terms, true);

        return $arrCond;
    }

    /**
     * 保存リストマスタの追加
     *
     * @param $user_id
     * @param $user_member
     * @param $qid
     * @param $arrData
     * @return bool
     */
    public function add_master_row($user_id, $user_member, $qid, $arrData) {
        /*
        $user_dir = $this->list_dir . '/' . $user_id;
        $qid_dir = $user_dir . '/' . $qid;
        */
        if (!$this->check_temp_dir($user_id, $qid)) {
            return false;
        }
        $master_path = $this->temp_dir . '/' . self::MASTER;
        $arrMaster = array();
        if (file_exists($master_path)) {
            $arrMaster = json_decode(file_get_contents($master_path), true);
        }
        $master_cnt = count($arrMaster);

        $db = new mysql_db();
        $max_cnt = $db->get('book_search_max', 'user', 'id = ?', array($user_id));
        if ($master_cnt >= $max_cnt) {
            return false;
        }

        $arrData['create_date'] = date('Y年m月d日 H:i:s');
        $arrData['execute_date'] = date('Y年m月d日 H:i:s');
        if (array_key_exists('add_row', $arrData)) {
            unset($arrData['add_row']);
        }

        if ($this->mode == 1) {
            $m_key     = $arrData['url'];
            $tel_key   = 'tel';
            $email_key = 'email';
            $arrData['idx'] = $master_cnt + 1;
        } else {
            $m_key     = $arrData['i_company_name'] . '_' . $arrData['i_tel'];
            $tel_key   = 'i_tel';
            $email_key = 'i_email';
            $arrData['i_idx'] = $master_cnt + 1;
        }

        $completion = 0;
        foreach ($this->arrColumn as $column) {
            $completion_key = $column . '_completion';
            if (array_key_exists($completion_key, $arrData)) {
                unset($arrData[$completion_key]);
                $completion++;
            }
        }

        if ($completion > 0) {
            unset($arrData['completion']);
        }

        //不要項目削除
        $arrMasterKey = array_merge($this->arrColumn, $this->arrSubColumn);
        foreach ($arrData as $key => $item) {
            if (!in_array($key, $arrMasterKey)) {
                unset($arrData[$key]);
            }
        }
        $arrMaster[$m_key] = $arrData;
        file_put_contents($master_path, json_encode($arrMaster));

        $arrCount = $db->getRow('total_count, tel_count, mail_count', 'favorite_search_terms', 'user = ? AND qid = ?', array($user_id, $qid));
        $arrCount['total_count'] += 1;
        if ($arrData[$tel_key]) {
            $arrCount['tel_count'] += 1;
        }
        if ($arrData[$email_key]) {
            $arrCount['mail_count'] += 1;
        }
        $arrSqlVal = array(
            'total_count'        => $arrCount['total_count'],
            'tel_count'          => $arrCount['tel_count'],
            'mail_count'         => $arrCount['mail_count'],
            'update_user_member' => $user_member,
            'execute_datetime'   => 'CURRENT_TIMESTAMP'
        );
        $db->update('favorite_search_terms', $arrSqlVal, 'user = ? AND qid = ?', array($user_id, $qid));

        return true;
        //$this->update_count($user_id, $qid, $row);
    }


    /**
     * 件数・更新者・更新日時の更新
     *
     * @param $user_id
     * @param $qid
     * @param $arrData
     */
    private function update_count($user_id, $qid, $arrData) {

        $db = new mysql_db();
        $arrCount = $db->getRow('total_count, tel_count, mail_count', 'favorite_search_terms', 'user = ? AND qid = ?', array($user_id, $qid));
        $arrCount['total_count'] -= 1;
        if ($arrData['tel']) {
            $arrCount['tel_count'] -= 1;
        }
        if ($arrData['email']) {
            $arrCount['mail_count'] -= 1;
        }
        $arrSqlVal = array(
            'total_count'        => $arrCount['total_count'],
            'tel_count'          => $arrCount['tel_count'],
            'mail_count'         => $arrCount['mail_count'],
            'update_user_member' => $user_id,
            'execute_datetime'   => 'CURRENT_TIMESTAMP'
        );
        $db->update('favorite_search_terms', $arrSqlVal, 'user = ? AND qid = ?', array($user_id, $qid));

    }


    /**
     *
     * 保存マスタファイルを削除
     *
     * @param $user_id
     * @param $qid
     */
    public function deleteMasterFile($user_id, $qid) {
        if ($this->checkMaster($user_id, $qid)) {
            unlink($this->temp_dir . '/' . self::MASTER);
            if (file_exists($this->temp_dir . '/' . self::RE_MASTER)) {
                unlink($this->temp_dir . '/' . self::RE_MASTER);
            }
            if (file_exists($this->temp_dir . '/' . 'master_count')) {
                unlink($this->temp_dir . '/' . 'master_count');
            }
            if (file_exists($this->temp_dir . '/' . 're_master_count')) {
                unlink($this->temp_dir . '/' . 're_master_count');
            }
        }
    }

    /**
     *
     * 再検索保存マスタファイルを削除
     *
     * @param $user_id
     * @param $qid
     */
    public function deleteReMasterFile($user_id, $qid) {
        if ($this->check_temp_dir($user_id, $qid)) {
            if (file_exists($this->temp_dir . '/' . self::RE_MASTER)) {
                @unlink($this->temp_dir . '/' . self::RE_MASTER);
            }
        }
    }


    /**
     * 保存マスタを削除
     *
     * @param $terms_id
     * @return int
     */
    public function deleteMaster($terms_id) {

        //qidを取得
        $db = new mysql_db();

        $db->begin();
        $arrData = $db->getRow('user, qid', 'favorite_search_terms', 'id = ?', array($terms_id));

        //再検索中の場合は、停止
        $this->que_un_lock(null, $arrData['user'], $arrData['qid']);

        $db->delete('auto_search_que', 'favorite_search_terms = ?', array($terms_id));
        $table = 'favorite_search_terms';
        $db->delete($table, 'id = ?', array($terms_id));
        $db->commit();

        $result = $this->check_temp_dir($arrData['user'], $arrData['qid']);
        if (!$result) {
            return -1;
        }

        //保存マスタをディレクトリごと削除
        $this->remove_dir();

        return 1;

    }


    /**
     * 保存済みマスタを全て削除
     *
     * @param $user_id
     */
    public function deleteMasterAll($user_id) {
        $user_dir = $this->list_dir . '/' . $user_id;
        $this->remove_dir($user_dir);

    }

    /**
     * 合計件数の更新
     *
     * @param mysql_db $db
     * @param $user_id
     * @param $qid
     */
    public function update_total_count(mysql_db $db, $user_id, $qid) {
        //$db = new mysql_db();
        $col = <<< EOF
            id, 
            CASE 
                WHEN last_execute_datetime = '0000-00-00 00:00:00' THEN ''
                ELSE last_execute_datetime
            END AS last_execute_datetime
EOF;

        $arrData = $db->getRow($col, 'favorite_search_terms', 'qid = ?', array($qid));
        $favorite_search_terms_id = $arrData['id'];
        if ($favorite_search_terms_id) {
            //$arrCount = $this->get_temp_total($user_id, $qid);

            $arrCount = $this->get_master_total($user_id, $qid);
            $arrSqlVal = array('total_count' => 0, 'tel_count' => 0, 'mail_count' => 0);
            if ($arrCount) {
                $arrSqlVal = array(
                    'total_count' => $arrCount['total_count'],
                    'tel_count'   => $arrCount['tel_count'],
                    'mail_count'  => $arrCount['mail_count']
                );
            }
            $db->update('favorite_search_terms', $arrSqlVal, 'id = ?', array($favorite_search_terms_id));
        }
    }



    /**
     * 保存マスタの件数取得
     *
     * @param $user_id
     * @param $qid
     * @return array
     */
    public function get_master_total($user_id, $qid) {
        $arrMaster = $this->getMasterList($user_id, $qid);
        if (!$arrMaster) {
            return array();
        }
        if ($this->mode == 1) {
            $tel_key = 'tel';
            $mail_key = 'email';
        } else {
            $tel_key = 'i_tel';
            $mail_key = 'i_email';
        }

        $total_count = 0;
        $tel_count = 0;
        $mail_count = 0;
        foreach($arrMaster as $item) {
            if ($item[$tel_key]) {
                $tel_count++;
            }
            if ($item[$mail_key]) {
                $mail_count++;
            }
            $total_count++;
        }

        return array('total_count' => $total_count, 'tel_count' => $tel_count, 'mail_count' => $mail_count);

    }

    /**
     * 再検索キューの追加
     *
     * @param $qid
     */
    public function add_re_search_que($qid) {
        $db = new mysql_db();

        $arrData = $db->getRow('id, user', 'favorite_search_terms', 'qid = ?', array($qid));
        $favorite_search_terms_id = $arrData['id'];
        $user_id = $arrData['user'];
        //最終検索実行日の更新
        $db->update('favorite_search_terms', array('last_execute_datetime' => 'CURRENT_TIMESTAMP'), 'qid = ?', array($qid));
        //再検索マスタを削除
//        $this->deleteReMasterFile($user_id, $qid);

        $this->add_search_que($db, $favorite_search_terms_id, $qid);
    }


    /**
     * 検索キューの追加
     *
     * @param mysql_db $db
     * @param $favorite_search_terms_id
     * @param $qid
     */
    private function add_search_que(mysql_db $db, $favorite_search_terms_id, $qid) {
        $user_id = $db->get('user', 'favorite_search_terms', 'qid = ?', array($qid));
        if (!$this->check_temp_dir($user_id, $qid)) {
            return;
        }

        $execute_status = 1;
        $end_datetime = '';
        $del_flg = 0;

        //検索キューに追加
        $arrQue = array(
            'favorite_search_terms' => $favorite_search_terms_id,
            'execute_status'        => $execute_status,
            'start_datetime'        => 'CURRENT_TIMESTAMP',
            'end_datetime'          => $end_datetime,
            'del_flg'               => $del_flg
        );

        $db->insert('auto_search_que', $arrQue);

        $stop_que_file = $this->temp_dir . '/' . 'stop_que';
        if (file_exists($stop_que_file)) {
            $que_code = $this->check_que_stop_instruction($user_id, $qid);
            if ($que_code == 5) {
                //全件取得済みのためキューの停止
                $this->stop_search_que($db, $user_id, $qid);
            }
            //unlink($stop_que_file);
        }

    }

    public function update_auto_re_search_que($db, $favorite_search_terms_id) {
        $arrQue = array(
            'start_datetime'        => 'CURRENT_TIMESTAMP'
        );
        $db->update('auto_search_que', $arrQue, 'favorite_search_terms = ? AND start_datetime IS NULL', array($favorite_search_terms_id));

        //最終実行日の更新
        $arrQue = array(
            'last_execute_datetime'        => 'CURRENT_TIMESTAMP'
        );
        $db->update('favorite_search_terms', $arrQue, 'id = ?', array($favorite_search_terms_id));

    }


    /**
     * 検索キューの停止
     *
     * @param $db
     * @param $user_id
     * @param $qid
     * @return bool
     */
    public function stop_search_que(mysql_db $db, $user_id, $qid) {
        if (!$this->check_temp_dir($user_id, $qid)) {
            return false;
        }

        $stop_que_file = $this->temp_dir . '/' . 'stop_que';
        if (file_exists($stop_que_file)) {
            unlink($stop_que_file);
        }
        //$db = new mysql_db();
        $favorite_search_terms_id = $db->get('id', 'favorite_search_terms', 'qid = ?', array($qid));
        if ($favorite_search_terms_id) {
            $arrQue = array(
                'end_datetime' => 'CURRENT_TIMESTAMP',
                'del_flg'      => 1
            );
            $db->update('auto_search_que', $arrQue, 'favorite_search_terms = ? AND start_datetime IS NOT NULL AND del_flg = 0', array($favorite_search_terms_id));
        }
    }


    /**
     * 検索キューを停止指示中かチェック
     *
     * @param $user_id
     * @param $qid
     * @return int
     */
    public function check_que_stop_instruction($user_id, $qid) {
        if (!$this->check_temp_dir($user_id, $qid)) {
            //保存ディレクトリが無い場合もキューの停止とみなす
            return 1;
        }

        $stop_que_file = $this->temp_dir . '/' . 'stop_que';
        if (file_exists($stop_que_file)) {
            return file_get_contents($stop_que_file);
        }
    }


    /**
     * 検索キューの停止を指示
     *
     * @param $user_id
     * @param $qid
     * @param $code
     * @return int
     */
    public function que_stop_instruction($user_id, $qid, $code) {
        if (!$this->check_temp_dir($user_id, $qid)) {
            return 0;
        }

        $stop_que_file = $this->temp_dir . '/' . 'stop_que';
        file_put_contents($stop_que_file, $code);

    }

    public function check_re_master_cnt($user_id, $qid, $parse_cnt) {
        $arrReMaster = $this->getReMasterList($user_id, $qid);
        if (is_array($arrReMaster)) {
            if ($parse_cnt <= count($arrReMaster)) {
                return true;
            }
        }
        return false;
    }


    public function get_list_info(mysql_db $db, $qid) {
        $col = <<< EOF
subject, total_count,
CASE WHEN execute_datetime = '0000-00-00 00:00:00' THEN create_datetime
ELSE execute_datetime
END AS execute_datetime, 
CASE WHEN last_execute_datetime = '0000-00-00 00:00:00' THEN create_datetime
ELSE last_execute_datetime
END AS last_execute_datetime
EOF;

        $list_info = $db->getRow($col, 'favorite_search_terms', 'qid = ?', array($qid));
        /*
        $list_info['add_cnt']    = 0;
        $list_info['update_cnt'] = 0;
        $list_info['delete_cnt'] = 0;
        $list_info['normal_cnt'] = 0;
*/
        return $list_info;
    }

    /**
     * ログイン時リストに保存されていないディレクトリは削除
     *
     * @param mysql_db $db
     * @param $user_id
     */
    public function temp_organize(mysql_db $db, $user_id) {
        $user_dir = $this->list_dir . '/' . $user_id;
        if (!file_exists($user_dir)) {
            return;
        }
        $arrQid = scandir($user_dir);
        foreach ($arrQid as $qid) {
            if ($qid == '.' || $qid == '..') {
                continue;
            }

            $master = $user_dir . '/' . $qid . '/' . self::MASTER;
            $re_master = $user_dir . '/' . $qid . '/' . self::RE_MASTER;
            $ret = $db->get('id', 'favorite_search_terms', 'qid = ?', array($qid));
            if (!$ret) {
                //ログイン時点で登録されていないqidのディレクトリは削除
                $dir = $user_dir . '/' . $qid;

                if (file_exists($master)) {
                    $file_time = filemtime($master);

                    if (time() - $file_time < 24 * 60 * 60) {
                        //24時間以内の場合は、削除しない
                        continue;
                    }
                }

                $this->remove_dir($dir);
            } else {
                $file_time = time();
                if (file_exists($re_master)) {
                    $file_time = filemtime($re_master);
                } else {
                    if (file_exists($master)) {
                        $file_time = filemtime($master);
                    }
                }

                if (time() - $file_time >= 24 * 60 * 60) {
                    $res = $db->update('auto_search_que', array('end_datetime' => 'CURRENT_TIMESTAMP', 'del_flg' => 1), 'favorite_search_terms = ? AND del_flg = 0', array($ret));
                    if ($res) {
                        if (!file_exists($re_master)) {
                            $this->update_total_count($db, $user_id, $qid);
                        }
                    }

                }
            }
        }
    }


    /**
     * キューを作成し、ロックをかける
     *
     * @param $user_id
     * @param $qid
     * @return bool|resource
     */
    public function que_lock($user_id, $qid) {
        if (!$this->check_temp_dir($user_id, $qid)) {
            return false;
        }

        //自動再検索ファイルの削除
        $auto_re_search_file = $this->temp_dir . '/' . 'auto_re_search';
        if (file_exists($auto_re_search_file)) {
            unlink($auto_re_search_file);
        }

        $que_file = $this->temp_dir . '/' . 'que';
        $fp = fopen($que_file, 'a');
        flock($fp,LOCK_EX|LOCK_NB);
        fputs($fp, $qid);
        chmod($que_file, 0777);

        return $fp;

    }

    /**
     * ロックを解除しキューの削除
     *
     * @param $fp
     * @param $user_id
     * @param $qid
     * @return bool
     */
    public function que_un_lock($fp, $user_id, $qid) {
        if (!$this->check_temp_dir($user_id, $qid)) {
            return false;
        }
        $que_file = $this->temp_dir . '/' . 'que';
        if (!file_exists($que_file)) {
            return false;
        }
        if (!$fp) {
            $fp = fopen($que_file, 'a');
        }
        fclose($fp);

        unlink($que_file);

    }


    /**
     * (再)検索中かチェック
     *
     * @param $user_id
     * @param $qid
     * @param $execute_status
     * @return bool
     */
    public function check_que($user_id, $qid, $execute_status = null) {
        if (!$this->check_temp_dir($user_id, $qid)) {
            return false;
        }

        if ($execute_status == 2) {
            //自動再検索ファイルがあるか
            $auto_re_search_file = $this->temp_dir . '/' . 'auto_re_search';
            if (file_exists($auto_re_search_file)) {
                return false;
            }
        }

        $que_file = $this->temp_dir . '/' . 'que';
        if (file_exists($que_file)) {
            $fp = fopen($que_file, 'r+');

            if (flock($fp,LOCK_EX|LOCK_NB)) {
                fclose($fp);
//                unlink($que_file);
                return true;
            } else {
                return false;
            }
        }

        return true;
    }


    public function get_active_qid(mysql_db $db, $user_id) {
        $table = 'favorite_search_terms INNER JOIN auto_search_que ON auto_search_que.favorite_search_terms = favorite_search_terms.id';
        $where = 'favorite_search_terms.user = ? AND favorite_search_terms.core_book = ? AND auto_search_que.del_flg = 0 AND auto_search_que.start_datetime IS NOT NULL';
        $qid = $db->get('qid', $table, $where, array($user_id, $this->mode));

        return $qid;
    }

    /**
     * 再検索マスタの件数を保存
     *
     * @param $user_id
     * @param $qid
     * @param $total_count
     * @param $loaded_count
     * @param $max_result
     * @param $master
     * @return bool
     */
    public function set_re_master_count($user_id, $qid, $total_count, $loaded_count, $max_result, $master = null) {
        if (!$this->check_temp_dir($user_id, $qid)) {
            return false;
        }

        $limit_count = $total_count;
        if ($total_count > $max_result) {
            $limit_count = $max_result;
        }

        $arrReMasterCount = array(
            'loaded_count' => $loaded_count,
            'total_count' => $limit_count,
            'actual_total_count' => $total_count
        );
        $re_master_count_file = $this->temp_dir . '/' . 're_master_count';
        if ($master) {
            $re_master_count_file = $this->temp_dir . '/' . 'master_count';
        }
        if (file_exists($re_master_count_file)) {
            unlink($re_master_count_file);
        }
        file_put_contents($re_master_count_file, json_encode($arrReMasterCount));
        chmod($re_master_count_file, 0777);
        return true;
    }

    /**
     * 検索マスタの件数を取得
     *
     * @param $user_id
     * @param $qid
     * @return array
     */
    public function get_master_count($user_id, $qid) {
        if (!$this->check_temp_dir($user_id, $qid)) {
            return array('loaded_count' => 0, 'total_count' => 0, 'actual_total_count' => 0);
        }

        $master_count_file = $this->temp_dir . '/' . 'master_count';
        if (!file_exists($master_count_file)) {
            //再検索マスタが無い場合は、保存マスタから取得
            $arrMaster = $this->getMasterList($user_id, $qid);
            return array('loaded_count' => count($arrMaster), 'total_count' => count($arrMaster), 'actual_total_count' => count($arrMaster));
        }

        return json_decode(file_get_contents($master_count_file), true);

    }

    /**
     * 再検索マスタの件数を取得
     *
     * @param $user_id
     * @param $qid
     * @return array
     */
    public function get_re_master_count($user_id, $qid) {
        if (!$this->check_temp_dir($user_id, $qid)) {
            return array('loaded_count' => 0, 'total_count' => 0, 'actual_total_count' => 0);
        }

        $re_master_count_file = $this->temp_dir . '/' . 're_master_count';
        if (!file_exists($re_master_count_file)) {
            $arrReMaster = $this->getReMasterList($user_id, $qid);
            if ($arrReMaster) {
                return array('loaded_count' => count($arrReMaster), 'total_count' => count($arrReMaster), 'actual_total_count' => count($arrReMaster));
            } else {
                //再検索マスタが無い場合は、保存マスタから取得
                $arrMaster = $this->getMasterList($user_id, $qid);
                return array('loaded_count' => count($arrMaster), 'total_count' => count($arrMaster), 'actual_total_count' => count($arrMaster));
            }
        }

        return json_decode(file_get_contents($re_master_count_file), true);

    }

    /**
     * 再検索マスタを基準とした比較情報を持ったリスト
     *
     * @param $user_id
     * @param $qid
     * @return array
     */
    public function getDiffList($user_id, $qid) {
        $arrReMaster = $this->getReMasterList($user_id, $qid, false);
        if (!$arrReMaster) {
            return array();
        }

        $arrMaster = $this->getMasterList($user_id, $qid, false);

        foreach ($arrReMaster as $rm_key => $row) {
            $row['row_type'] = 'add_row';
            if ($arrMaster) {
                if (array_key_exists($rm_key, $arrMaster)) {
                    //項目の比較
                    $row['row_type'] = 'normal_row';
                    $master_row = $arrMaster[$rm_key];
                    foreach ($this->arrColumn as $column) {
                        if ($column == 'remarks' || $column == 'note') {
                            if (!array_key_exists($column, $row)) {
                                $row[$column] = '';
                            }
                            if (!array_key_exists($column, $master_row)) {
                                $re_master_row[$column] = '';
                            }
                            continue;
                        }
                        //１つでも違いがあれば更新データ
                        if ($row[$column] != $master_row[$column]) {
                            $row['row_type'] = 'update_row';
                            break;
                        }
                    }
                }
            }
            $arrReMaster[$rm_key] = $row;
        }

        return $arrReMaster;
    }

    public function set_auto_re_search($user_id, $qid) {
        if (!$this->check_temp_dir($user_id, $qid)) {
            return false;
        }

        $auto_re_search_file = $this->temp_dir . '/' . 'auto_re_search';
        file_put_contents($auto_re_search_file, 1);
    }
}

endif;?>
