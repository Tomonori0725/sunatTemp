<?php declare(encoding = 'utf-8');
if (!defined('CORE_SEARCH_CLASS_INC')): define('CORE_SEARCH_CLASS_INC', 1);
/*****************************************************************************
  This is non public. 無断転載,無断使用を禁ず
  Copyright (C) 2009. SUNATMARK Co.,Ltd. All Right Reserved.

ファイル概要:
 リストクラスター基底クラス

$Id$
*****************************************************************************/

class CoreSearch extends ListCluster {

    /*----------------------------------------------------------------------------
    機能概要: コンストラクタ
    機能説明:
    引数:	なし
    戻り値:	なし
    ----------------------------------------------------------------------------*/
    public function __construct() {
        $this->mode = 1;
        $this->mode_str = 'core';
        $this->arrColumn = array(
            'company_name', 'location_detail', 'zip', 'email', 'tel', 'fax',
            'employment_type', 'job_type', 'salary', 'location',
            'representative', 'employees', 'capital', 'turnover', 'establishment',
            'remarks', 'note'
        );

        $this->arrSubColumn = array(
            'url_code', 'url_ind', 'url', 'source', 'done', 'idx', 'create_date', 'execute_date', 'ppt_query', 'tel_n', 'last_row'
        );

        $this->arrMasterColumn = array_merge($this->arrColumn, $this->arrSubColumn);
        parent::__construct();
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

    /*----------------------------------------------------------------------------
    機能概要: 保存済みリスト一覧の取得
    機能説明:
        保存済みリスト一覧を取得する
    引数:	名前 	(I/O) 	説明

    戻り値:	なし
    ----------------------------------------------------------------------------*/
    public function getSaveList(mysql_db $db, $user_id) {
        return parent::getSaveList($db, $user_id);
    }

    /**
     * 保存リストマスターの作成
     *
     * @param $user_id
     * @param $qid
     * @param $arrData
     * @return bool
     */
    public function make_master($user_id, $qid, $arrData) {
        if (!$this->check_temp_dir($user_id, $qid)) {
            return false;
        }
        $master_path = $this->temp_dir . '/' . parent::MASTER;

        if (file_exists($master_path)) {
            $arrMaster = json_decode(file_get_contents($master_path), true);
        } else {
            $arrMaster = array();
        }
//        $idx = urlencode($arrData['url']);
        $arrData['idx'] = count($arrMaster) + 1;

        $m_key = $arrData['url'];
        if (!$m_key) {
            return false;
        }
        $tel = $arrData['tel'];
        $email = $arrData['email'];

        $arrData['done'] = 1;
        if (!array_key_exists('note', $arrData)) {
            $arrData['note'] = '';
        }
        $arrData['create_date'] = date('Y年m月d日 H:i:s');
        $arrData['execute_date'] = date('Y年m月d日 H:i:s');
        $arrMaster[$m_key] = $arrData;
        file_put_contents($master_path, json_encode($arrMaster));
    }

    /**
     * 再検索保存リストマスターの作成
     *
     * @param $user_id
     * @param $qid
     * @param $arrData
     * @return bool
     */
    public function make_re_master($user_id, $qid, $arrData) {
        if (!$this->check_temp_dir($user_id, $qid)) {
            return false;
        }
        $re_master_path = $this->temp_dir . '/' . parent::RE_MASTER;

        if (file_exists($re_master_path)) {
            $arrReMaster = json_decode(file_get_contents($re_master_path), true);
        } else {
            $arrReMaster = array();
        }

        $m_key = $arrData['url'];
        $arrData['idx'] = count($arrReMaster) + 1;
        $arrReMaster[$m_key] = $arrData;

        file_put_contents($re_master_path, json_encode($arrReMaster));
        chmod($re_master_path, 0777);
    }

    /**
     * 保存リストマスターの取得
     *
     * @param $user_id
     * @param $qid
     * @param $loaded_remaster_key_array
     * @return array
     */
    public function get_master_row($user_id, $qid, $loaded_remaster_key_array) {
        $status = 0;
        $arrMaster = parent::getMasterList($user_id, $qid, false);

        $row = array();
        $rm_key = '';

        if (is_array($arrMaster)) {
            foreach ($arrMaster as $key => $item) {
                if (!in_array($key, $loaded_remaster_key_array)) {
                    $row = $item;
                    $row['normal_row'] = 1;
                    $row['row_type'] = 'normal_row';
                    $rm_key = $key;
                    break;
                }
            }
        }

        return array('status' => $status, 'row' => $row, 'rm_key' => $rm_key);

    }

    /**
     * 再検索保存リストマスターの取得
     *
     * @param $user_id
     * @param $qid
     * @param $inited_data_key_array
     * @param $loaded_remaster_key_array
     * @return array
     */
    public function get_re_master_row($user_id, $qid, $inited_data_key_array, $loaded_remaster_key_array) {
        $status = 0;
        $arrReMaster = parent::getReMasterList($user_id, $qid, false);
        if ($arrReMaster) {
            $arrMaster = parent::getMasterList($user_id, $qid, false);
        } else {
            $status = -99;  //再検索マスタがない
            return array('status' => $status);
        }

        $row = array();
        $rm_key = '';

        foreach ($arrReMaster as $key => $item) {
            if (!in_array($key, $loaded_remaster_key_array)) {
                $row = $item;
                $rm_key = $key;
                break;
            }
        }

        if ($rm_key) {
            if (is_array($arrMaster) && array_key_exists($rm_key, $arrMaster)) {
                //$row = $arrMaster[$rm_key];

                $row['normal_row'] = 1;
                $row['row_type'] = 'normal_row';

                $idx = array_search($rm_key, $inited_data_key_array);
                //項目の比較
                $master_row = $arrMaster[$rm_key];
                $master_row['idx'] = $idx + 1;
                foreach ($this->arrColumn as $column) {
                    if ($column == 'remarks' || $column == 'note') {
                        if (!array_key_exists($column, $row)) {
                            $row[$column] = '';
                        }
                        if (!array_key_exists($column, $master_row)) {
                            $master_row[$column] = '';
                        }
                        continue;
                    }
                    //１つでも違いがあれば更新データ
                    if ($row[$column] != $master_row[$column]) {
                        $row['idx'] = $idx + 1;
                        $row['update_row'] = 1;
                        $row['row_type'] = 'update_row';
                        //file_put_contents('../debug/' . __LINE__ . '_' .rand(1000, 9999), print_r($row, true));
                        break;
                    }
                }
            } else {
                $row['add_row'] = 1;
                $row['row_type'] = 'add_row';
            }
        } else {
        }

        return array('status' => $status, 'row' => $row, 'rm_key' => $rm_key);

    }

    /**
     * 保存済みリストの取得
     *
     * @param $db
     * @param $user_id
     * @param $qid
     * @param $load_list
     * @return bool|mixed
     */
    public function getCoreList(mysql_db $db, $user_id, $qid, $load_list) {
        $arrMaster = parent::getMasterList($user_id, $qid, false);
        if (!$arrMaster) {
            return array(array(), array());
        }
        $arrReMaster = parent::getReMasterList($user_id, $qid, false);
        //再検索保存リストがあればリストの比較を行う
        $arrUpdate = array();
        $que_exists = 0;
        if (file_exists($this->temp_dir . '/' . 'que')) {
            $que_exists = 1;
        }

        $re_search = 0;
        if ($load_list == 1) {
            $last_execute_datetime = $db->get('last_execute_datetime', 'favorite_search_terms', 'user = ? AND qid = ?', array($user_id, $qid));
            if ($last_execute_datetime != '0000-00-00 00:00:00') {
                $re_search = 1;
            }
        }

        foreach ($arrMaster as $m_key => $row) {
            $row['normal_row'] = 1;
            $row['row_type'] = 'normal_row';
            if ($load_list == 1) {
                if ($arrReMaster) {
                    if (array_key_exists($m_key, $arrReMaster)) {
                        //項目の比較
                        $re_master_row = $arrReMaster[$m_key];
                        foreach ($this->arrColumn as $column) {
                            if ($column == 'remarks' || $column == 'note') {
                                if (!array_key_exists($column, $row)) {
                                    $row[$column] = '';
                                }
                                if (!array_key_exists($column, $re_master_row)) {
                                    $re_master_row[$column] = '';
                                }
                                continue;
                            }
                            //１つでも違いがあれば更新データ
                            if ($row[$column] != $re_master_row[$column]) {
                                $row['update_row'] = 1;
                                $row['row_type'] = 'update_row';
                                $re_master_row['update_row'] = 1;
                                $re_master_row['row_type'] = 'update_row';
                                $re_master_row['idx'] = $row['idx'];
                                $arrUpdate[] = $re_master_row;
                                break;
                            }
                        }
                    }
                } else {
                    if ($que_exists == 0 && $re_search == 1) {
                        //リスト呼出で再検索中でない場合で且つ、再検索マスタにデータが無い
                        $row['row_type'] = 'delete_row';
                        $row['delete_row'] = 1;
                    }
                }
            }
            $arrMaster[$m_key] = $row;
        }
        if ($load_list == 1) {
            if ($arrReMaster) {
                foreach ($arrReMaster as $rm_key => $row) {
                    if (!array_key_exists($rm_key, $arrMaster)) {
                        $row['row_type'] = 'add_row';
                        $row['add_row'] = 1;
                        $row['idx'] = count($arrMaster) + 1;
                        $arrMaster[$rm_key] = $row;
                    }
                }
            }
        }

        return array(array_values($arrMaster), $arrUpdate, $re_search);
    }


    /**
     * 保存リストマスタの更新
     *
     * @param $user_id
     * @param $user_member
     * @param $qid
     * @param $row
     * @param $arrUpdate
     * @return array
     */
    public function update_master_row($user_id, $user_member, $qid, $row, $arrUpdate) {
        if (!$this->check_temp_dir($user_id, $qid)) {
            return array();
        }
        $master_path = $this->temp_dir . '/' . self::MASTER;
        if (!file_exists($master_path)) {
            $arrMaster = array();
        } else {
            $arrMaster = json_decode(file_get_contents($master_path), true);
        }

        $arrBefore = array();
        $tel = $row['tel'];
        $email = $row['email'];
        $db = new mysql_db();
        $arrCount = $db->getRow('total_count, tel_count, mail_count', 'favorite_search_terms', 'user = ? AND qid = ?', array($user_id, $qid));
        if (!$arrCount) {
            $arrCount = array(
                'total_count' => 0,
                'tel_count'   => 0,
                'mail_count'  => 0
            );
        }
        $completion = 0;
        $row_type = '';
        if (array_key_exists('row_type', $row) && $row['row_type'] == 'add_row') {
            $row_type = $row['row_type'];
        }
        foreach($arrUpdate as $column => $value) {
            if (!in_array($column, $this->arrColumn)) {
                continue;
            }
            if ($column == 'note') {
                if (!array_key_exists($column, $row)) {
                    $row[$column] = '';
                }
            }
            $before = $row[$column];
            if ($before != $value) {
                $row[$column] = $value;
                $arrBefore['d_' . $column] = $before;
                if (!array_key_exists($column . '_completion', $row)) {
                    $arrBefore[$column . '_completion'] = 1;
                } else {
                    $arrBefore[$column . '_completion'] = $row[$column . '_completion'] + 1;
                }

                if ($column == 'tel') {
                    if ($row_type == 'add_row') {
                        if ($value) {
                            $arrCount['tel_count'] += 1;
                        }
                    } else {
                        if (!$tel && $value) {
                            $arrCount['tel_count'] += 1;
                        } else if ($tel && !$value) {
                            $arrCount['tel_count'] -= 1;
                        }
                    }
                }
                if ($column == 'email') {
                    if ($row_type == 'add_row') {
                        if ($value) {
                            $arrCount['mail_count'] += 1;
                        }
                    } else {
                        if (!$email && $value) {
                            $arrCount['mail_count'] += 1;
                        } else if ($email && !$value) {
                            $arrCount['mail_count'] -= 1;
                        }
                    }
                }
                $completion++;
            } else {
                if ($row_type == 'add_row') {
                    if ($column == 'tel') {
                        if ($value) {
                            $arrCount['tel_count'] += 1;
                        }
                    } else if ($column == 'email') {
                        if ($value) {
                            $arrCount['mail_count'] += 1;
                        }
                    }
                }
            }
        }

        if ($completion == 0) {
            return $row;
        }
        if ($row_type == 'add_row') {
            $arrCount['total_count'] += 1;
            $row['row_type'] = 'normal_row';
            $row['execute_date'] = date('Y年m月d日 H:i:s');
        }

        $row['execute_date'] = date('Y年m月d日 H:i:s');
        $arrBefore['completion'] = $completion;

        //不要項目削除
        $arrMasterRow = array();
        foreach ($row as $key => $item) {
            if (in_array($key, $this->arrMasterColumn)) {
                $arrMasterRow[$key] = $item;
            }
        }

        $saved = $this->check_saved($user_id, $qid);
        if ($saved) {
            $arrMaster[$arrMasterRow['url']] = $arrMasterRow;
            file_put_contents($master_path, json_encode($arrMaster));

            //$this->update_count($user_id, $qid, $row);
            $arrSqlVal = array(
                'total_count'        => $arrCount['total_count'],
                'tel_count'          => $arrCount['tel_count'],
                'mail_count'         => $arrCount['mail_count'],
                'update_user_member' => $user_member,
                'execute_datetime'   => 'CURRENT_TIMESTAMP'
            );
            $db->update('favorite_search_terms', $arrSqlVal, 'user = ? AND qid = ?', array($user_id, $qid));
        }

        return array_merge($row, $arrBefore);
    }



    /**
     * 保存リストマスタのリセット
     *
     * @param $user_id
     * @param $qid
     * @param $row
     */
    public function reset_master_row($user_id, $qid, $row) {
        /*
        $user_dir = $this->list_dir . '/' . $user_id;
        $qid_dir = $user_dir . '/' . $qid;
        */
        if (!$this->check_temp_dir($user_id, $qid)) {
            return array();
        }
        $master_path = $this->temp_dir . '/' . parent::MASTER;
        if (!file_exists($master_path)) {
            $arrMaster = array();
        } else {
            $arrMaster = json_decode(file_get_contents($master_path), true);
        }

        $row['execute_date'] = date('Y年m月d日 H:i:s');
        $tel = $row['tel'];
        $email = $row['email'];
        $db = new mysql_db();
        $arrCount = $db->getRow('total_count, tel_count, mail_count', 'favorite_search_terms', 'user = ? AND qid = ?', array($user_id, $qid));

        foreach ($this->arrColumn as $column) {
            $d_key = 'd_' . $column;
            $completion_key = $column . '_completion';
            if (array_key_exists($d_key, $row)) {
                $row[$column] = $row[$d_key];
                $row[$completion_key] -= 1;

                if ($column == 'tel') {
                    if (!$tel && $row[$d_key]) {
                        $arrCount['tel_count'] += 1;
                    } else if ($tel && !$row[$d_key]) {
                        $arrCount['tel_count'] -= 1;
                    }
                }
                if ($column == 'email') {
                    if (!$email && $row[$d_key]) {
                        $arrCount['mail_count'] += 1;
                    } else if ($email && !$row[$d_key]) {
                        $arrCount['mail_count'] -= 1;
                    }
                }
                unset($row[$d_key]);
            }
            if (array_key_exists($completion_key, $row) && $row[$completion_key] == 0) {
                unset($row[$completion_key]);
            }

        }

        if (array_key_exists('completion', $row)) {
            unset($row['completion']);
        }

        //不要項目削除
        $arrMasterRow = array();
        foreach ($row as $key => $item) {
            if (in_array($key, $this->arrMasterColumn)) {
                $arrMasterRow[$key] = $item;
            }
        }

        $saved = $this->check_saved($user_id, $qid);
        if ($saved) {
            $arrMaster[$arrMasterRow['url']] = $arrMasterRow;
            file_put_contents($master_path, json_encode($arrMaster));

            $arrSqlVal = array(
                'tel_count' => $arrCount['tel_count'],
                'mail_count' => $arrCount['mail_count'],
                'update_user_member' => $user_id,
                'execute_datetime' => 'CURRENT_TIMESTAMP'
            );
            $db->update('favorite_search_terms', $arrSqlVal, 'user = ? AND qid = ?', array($user_id, $qid));
        }
        return $row;
    }




    public function saved_list($user_id, $qid, $temp_arr) {
        $user_dir = $this->list_dir . '/' . $user_id;
        $temp_dir = $user_dir . '/' . $qid;

        $temp_name = urlencode($temp_arr['url']);
        $tel = $temp_arr['tel'];
        $email = $temp_arr['email'];

        $temp_arr['done'] = 1;
        $temp_arr['create_date'] = date('Y年m月d日 H:i:s');
        $temp_arr['execute_date'] = date('Y年m月d日 H:i:s');
        file_put_contents($temp_dir . '/' . $temp_name, json_encode($temp_arr));

        $this->saved_key_list($temp_dir, $temp_name);

        $this->set_count($temp_dir, $tel, $email);
    }

    public function saved_key_list($temp_dir, $temp_name) {
        $file = $temp_dir . '/' . parent::KEY_LIST;
        $fp = fopen($file, 'a+');
        fwrite($fp, $temp_name ."\r\n");
        fclose($fp);
    }

    public function set_count($temp_dir, $tel, $mail) {
        $file = $temp_dir . '/' . parent::COUNT_FILE;
        if (file_exists($file)) {
            $count_file = json_decode(file_get_contents($file), true);
        } else {
            $count_file = array(
                'total_count' => 0,
                'tel_count'   => 0,
                'mail_count'  => 0
            );
        }

        $count_file['total_count'] += 1;
        if ($tel) {
            $count_file['tel_count'] += 1;
        }
        if ($mail) {
            $count_file['mail_count'] += 1;
        }

        file_put_contents($file, json_encode($count_file));
    }


    /**
     * リストの削除
     *
     * @param $user_id
     * @param $user_member
     * @param $qid
     * @param $del_key
     * @return bool
     */
    public function delete_row($user_id, $user_member, $qid, $del_key) {
        if (!$this->check_temp_dir($user_id, $qid)) {
            return false;
        }

        $master_path = $this->temp_dir . '/' . self::MASTER;
        if (!file_exists($master_path)) {
            return false;
        }
        $arrMaster = json_decode(file_get_contents($master_path), true);

        if (!array_key_exists($del_key, $arrMaster)) {
            return false;
        }
        $arrData = $arrMaster[$del_key];
        unset($arrMaster[$del_key]);

        //idxの振り直し
        $i = 1;
        foreach ($arrMaster as &$row) {
            $row['idx'] = $i;
            $i++;
        }

        file_put_contents($master_path, json_encode($arrMaster));

        $db = new mysql_db();
        $arrCount = $db->getRow('total_count, tel_count, mail_count', 'favorite_search_terms', 'user = ? AND qid = ?', array($user_id, $qid));
        if (!$arrCount) {
            return true;
        }
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
            'update_user_member' => $user_member,
            'execute_datetime'   => 'CURRENT_TIMESTAMP'
        );
        $db->update('favorite_search_terms', $arrSqlVal, 'user = ? AND qid = ?', array($user_id, $qid));

        return true;
    }


    /**
     * コメントの更新
     *
     * @param $user_id
     * @param $qid
     * @param $key
     * @param $note
     * @return bool
     */
    public function update_note_row($user_id, $qid, $key, $note) {
        if (!$this->check_temp_dir($user_id, $qid)) {
            return false;
        }

        $arrMaster = parent::getMasterList($user_id, $qid, false);
        if (!$arrMaster) {
            return false;
        }
        if (!array_key_exists($key, $arrMaster)) {
            //再検索で追加前の場合
            return false;
        }

        $arrMaster[$key]['note'] = $note;
        $master_file = $this->temp_dir . '/' . self::MASTER;
        file_put_contents($master_file, json_encode($arrMaster));
    }


    /**
     * CSVヘッダーの取得
     *
     * @param $dl
     * @return array
     */
    public function getCsvHeader($dl) {
        if ($dl == 1) {
            //リストクラスターⅠ互換形式
            $csv_header = array(
                '法人:0/個人:1', '名称', 'フリガナ', 'URL', '郵便番号', '住所',
                '電話番号', 'メールアドレス','コメント', '作成日', '更新日'
            );
        }
        else if ($dl == 2) {
            //全項目
            $csv_header = array(
                '名称', '電話番号', 'FAX番号', 'メールアドレス', '郵便番号',
                '住所', '業種', 'URL', '取得ページ', '代表者',
                '資本金', '設立日', '従業員数',
                '雇用形態', '給与', '売上高', '備考', 'コメント', '作成日', '更新日'
            );

        }

        return $csv_header;
    }

    /**
     * CSVコンテンツの取得(行)
     *
     * @param $buf
     * @param $dl
     * @return array
     */
    public function getCsvContents($buf, $dl) {

        //CSV出力からHTMLタグを除く
        foreach($buf as $key => &$val) {
            if ($key == 'ppt_query') continue;
            //$val = html_entity_decode($val);
            if ($key != 'note' && $key != 'remarks') $val = str_replace(array("\r\n","\r","\n"), " ", $val);
            //$val = preg_replace('/<br[[:space:]]*\/?[[:space:]]*>/iu', " ", $val);
            //$val = strip_tags($val);
        }

        if (!isset($buf['note'])) $buf['note'] = null; // ロード済みでパースされていない場合
        $genetate_date = date('Y/m/d');
        if (isset($buf['create_date'])) {
            $create_date = $buf['create_date'];
        } else {
            $create_date = date('Y年m月d日 H:i:s');
        }
        if (isset($buf['execute_date'])) {
            $execute_date = $buf['execute_date'];
        } else {
            $execute_date = date('Y年m月d日 H:i:s');
        }
        if ($dl == 1) {
            //リストクラスターⅠ互換形式
            $csv_contents = array(
                0, $buf['company_name'], null, null, $buf['zip'], $buf['location_detail'],
                $buf['tel'], $buf['email'], $buf['note'], $create_date, $execute_date
            );
        }
        else if ($dl == 2) {
            //全項目
            $csv_contents = array(
                $buf['company_name'], $buf['tel'], $buf['fax'], $buf['email'], $buf['zip'],
                $buf['location_detail'], $buf['job_type'], null, $buf['url'], $buf['representative'],
                $buf['capital'], $buf['establishment'], $buf['employees'],
                $buf['employment_type'], $buf['salary'], $buf['turnover'], $buf['remarks'], $buf['note'],
                $create_date, $execute_date
            );
        }

        return $csv_contents;
    }

}

endif;?>
