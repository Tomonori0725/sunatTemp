<?php

declare(encoding = 'utf-8');
if (!defined('LIST_CLUSTER_CLASS_INC')): define('LIST_CLUSTER_CLASS_INC', 1);
    /*     * ***************************************************************************
      This is non public. 無断転載,無断使用を禁ず
      Copyright (C) 2009. SUNATMARK Co.,Ltd. All Right Reserved.

      ファイル概要:
      リストクラスター基底クラス

      $Id$
     * *************************************************************************** */

    class ListCluster
    {

        protected $mode;
        protected $mode_str;
        protected $root_path;
        protected $saved_dir;
        protected $list_dir;
        protected $temp_dir;
        protected $arrColumn;
        protected $arrSubColumn;
        protected $arrMasterColumn;
        protected $arrMergeKey;
        protected $arrMergeId;
        protected $arrMergeColumn;
        protected $location_detail_pattern;
        protected $arrCsvColumn;
        protected $arrMasterEditColumn;

        const COUNT_FILE = 'count_file';
        const KEY_LIST   = 'key_list';
        const MASTER     = 'master';
        const RE_MASTER  = 're_master';

        /* ----------------------------------------------------------------------------
          機能概要: コンストラクタ
          機能説明:
          引数:	なし
          戻り値:	なし
          ---------------------------------------------------------------------------- */

        public function __construct()
        {
            $this->root_path = dirname(dirname(dirname(__FILE__)));
            $this->saved_dir = $this->root_path . '/saved';
            $this->list_dir  = $this->saved_dir . '/' . $this->mode_str;
            //$this->temp_dir = $this->list_dir . '/temp';

            $this->arrMergeColumn = array();
        }

        /* ----------------------------------------------------------------------------
          機能概要: インスタンス取得
          機能説明:
          引数:	なし
          戻り値:	ListClusterクラスのオブジェクト
          ---------------------------------------------------------------------------- */
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
         * Instance生成.
         *
         * @param int $core_book
         * @return \WideSearch|\CoreSearch
         */
        public static function createInstance($core_book)
        {
            if ($core_book == 1) {
                return new CoreSearch();
            }
            return new WideSearch();
        }

        /**
         * 機能概要： 保存済みリスト一覧の取得
         * 機能説明：
         *      保存済みリスト一覧を取得する
         *
         * @param mysql_db $db
         * @param $user_id
         * @return array|null
         */
        protected function getSaveList(mysql_db $db, $user_id)
        {

            if ($this->mode == 1) {
                $col = 'core_search_result_save';
            } else {
                $col = 'book_search_result_save';
            }
            $search_result_save = $db->get($col, 'user', 'id = ?', array($user_id));


            $col = <<< EOF
            favorite_search_terms.id, subject, core_book, total_count, tel_count, mail_count, list_terms, qid, creator,
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
            (SELECT pic FROM user_member WHERE user_member.id = favorite_search_terms.create_user_member) AS user_name,
            (SELECT execute_status FROM auto_search_que WHERE favorite_search_terms = favorite_search_terms.id AND auto_search_que.del_flg = 0 AND auto_search_que.start_datetime IS NOT NULL LIMIT 1 OFFSET 0) AS execute_status,
            CASE
                WHEN search_terms IS NULL THEN 0
                ELSE 1
            END AS re_search_enable,
            (SELECT execute_status FROM auto_search_que WHERE favorite_search_terms = favorite_search_terms.id AND auto_search_que.del_flg = 1 AND favorite_search_terms.last_execute_datetime != '0000-00-00 00:00:00' ORDER BY auto_search_que.id DESC LIMIT 1 OFFSET 0) AS last_auto_search
EOF;

            $table   = 'favorite_search_terms';
            $where   = 'user = ? AND core_book = ?';
            $arrList = $db->select($col, $table, $where, array($user_id, $this->mode));

            foreach ($arrList as $key => $item) {
                $list_terms = json_decode($item['list_terms'], true);
                if (is_array($list_terms)) {
                    if (!$search_result_save) {
                        $list_terms['saved'] = 0;
                    }
                    $item['list_terms'] = $list_terms;
                    $arrList[$key]      = $item;
                }

                $item['master_exists'] = $this->checkMaster($user_id, $item['qid']);
                $arrList[$key]         = $item;
            }

            $col      = 'COUNT(favorite_search_terms.id) AS list_count, SUM(total_count) AS target_count';
            $arrCount = $db->getRow($col, $table, $where, array($user_id, $this->mode));


            //コア・ワイド合計ターゲット件数の取得
            $col               = 'user.search_history_save_number - SUM(COALESCE(total_count, 0)) AS rest_target_count';
            $table             = 'user LEFT JOIN favorite_search_terms ON user.id = favorite_search_terms.user';
            $rest_target_count = $db->get($col, $table, 'user.id = ?', array($user_id));
            if ($rest_target_count < 0) {
                $arrCount['rest_target_count'] = 0;
            } else {
                $arrCount['rest_target_count'] = $rest_target_count;
            }

            return array($arrList, $arrCount);
        }

        /**
         * リストツリー情報リスト取得.
         *
         * @param mysql_db $db
         * @param int $userId ユーザーID
         * @return array リストツリー情報リスト
         */
        public function getSearchTermsTreeArray(mysql_db $db, $userId)
        {
            $searchTermsTree = $this->getSearchTermsTree($db, $userId);
            if (!$searchTermsTree) {
                return array();
            }
            //mb_send_mail('kusa@sunatmark.co.jp', time(), $searchTermsTree);
            $nodeList   = unserialize($searchTermsTree);
            $resultList = array();
            foreach ($nodeList as $key => $node) {
                $resultList[$key] = trim($node);
            }
            return $resultList;
        }

        /**
         * リストツリー情報取得.
         *
         * @param mysql_db $db
         * @param int $userId ユーザーID
         * @return string リストツリー情報
         */
        public function getSearchTermsTree(mysql_db $db, $userId)
        {
            $searchCol       = $this->mode == 1 ? 'search_terms_tree' : 'book_search_terms_tree';
            $searchTermsTree = $db->get($searchCol, 'user', 'id = ?', array($userId));
            return $searchTermsTree;
        }

        /**
         * 保存リスト情報取得.
         *
         * @param mysql_db $db
         * @param type $terms_id
         */
        public function getFavoriteSearchTerms(mysql_db $db, $qid)
        {
            $sql        = <<< _SQL_
                    SELECT
                        FST.user,
                        FST.qid,
                        FST.subject,
                        FST.total_count,
                        FST.tel_count,
                        FST.mail_count,
                        CASE
                            WHEN FST.execute_datetime = '0000-00-00 00:00:00' THEN FST.create_datetime
                            ELSE FST.execute_datetime
                        END execute_datetime,
                        CASE
                            WHEN FST.last_execute_datetime = '0000-00-00 00:00:00' THEN FST.create_datetime
                            ELSE FST.last_execute_datetime
                        END last_execute_datetime,
                        U.name AS user_name
                    FROM
                        favorite_search_terms FST
                            INNER JOIN user U
                                ON FST.user = U.id
                    WHERE
                        FST.qid = :qid
_SQL_;
            $resultList = $db->getAll($sql, array('qid' => $qid));
            if (count($resultList) == 0) {
                return null;
            }
            return $resultList[0];
        }

        /**
         * 検索条件情報リスト取得.
         *
         * @param mysql_db $db
         * @param int $user_id ユーザーID
         * @return array 検索条件情報リスト
         */
        public function getFavoriteSearchTermsList(mysql_db $db, $user_id)
        {
            if ($this->mode == 1) {
                $col = 'core_search_result_save';
            } else {
                $col = 'book_search_result_save';
            }
            $search_result_save = $db->get($col, 'user', 'id = ?', array($user_id));

            $sql                     = <<< _SQL_
                SELECT
                    FST.id,
                    FST.user,
                    FST.core_book,
                    FST.subject,
                    FST.total_count,
                    FST.tel_count,
                    FST.mail_count,
                    FST.search_terms,
                    FST.list_terms,
                    FST.note,
                    FST.qid,
                    FST.create_user_member,
                    FST.update_user_member,
                    CASE WHEN FST.create_datetime = '0000-00-00 00:00:00' THEN NULL
                        ELSE FST.create_datetime
                        END create_datetime,
                    CASE WHEN FST.execute_datetime = '0000-00-00 00:00:00' THEN NULL
                        ELSE FST.execute_datetime
                        END execute_datetime,
                    CASE WHEN FST.last_execute_datetime = '0000-00-00 00:00:00' THEN TIME_TO_SEC(DATEDIFF(NOW(), FST.create_datetime))
                        ELSE TIME_TO_SEC(DATEDIFF(NOW(), FST.last_execute_datetime))
                        END AS elapsed,
                    FST.creator,
                    FST.notification_mail_ex,
                    FST.purpose,
                    FST.score,
                    FST.notification_mail_send,
                    CASE WHEN search_terms IS NULL THEN 0
                        ELSE 1
                        END re_search_enable,
                    (
                        SELECT
                            execute_status
                        FROM
                            auto_search_que
                        WHERE
                            favorite_search_terms = FST.id
                            AND auto_search_que.del_flg = 1
                            AND FST.last_execute_datetime != '0000-00-00 00:00:00'
                        ORDER BY
                            auto_search_que.id DESC
                        LIMIT 1 OFFSET 0
                    ) AS last_auto_search,
                    (
                        SELECT
                            CASE WHEN COUNT(*) > 0 THEN 1 ELSE 0 END count
                        FROM
                            auto_search_que ASQ
                        WHERE
                            ASQ.del_flg        = 0
                            AND ASQ.start_datetime IS NOT NULL
                            AND ASQ.end_datetime IS NULL
                            AND ASQ.favorite_search_terms = FST.id
                    ) AS is_re_search_running
                FROM
                    favorite_search_terms FST
                WHERE
                    FST.user = :user
                    AND FST.core_book = :core_book
                ORDER BY
                    FST.id
_SQL_;
            $favoriteSearchTeamsList = $db->getAll($sql, array(
                'user'      => $user_id,
                'core_book' => $this->mode
            ));

            $resultList = array();
            foreach ($favoriteSearchTeamsList as $data) {
                $list_terms = json_decode($data['list_terms'], true);
                $list_terms = is_array($list_terms) ? $list_terms : array();
                if (!$search_result_save) {
                    $list_terms['saved'] = 0;
                }
                $data['list_terms']    = $list_terms;
                $data['master_exists'] = $this->checkMaster($user_id, $data['qid']);

                // -------------------------------------------
                // 再検索での更新情報取得
                // -------------------------------------------
                // 保存マスタと再検索マスタの差分取得
                $diffCount                   = $this->getDiffCountInfo($user_id, $data['qid']);
                $data['research_diff_count'] = $diffCount;

                $id              = $data['id'];
                $resultList[$id] = $data;
            }
            return $resultList;
        }

        /**
         * 自動再検索スケジュールを含む保存リスト取得.
         *
         * @param mysql_db $db
         * @param int $userId ユーザーID
         * @param int $coreBook 検索対象
         * @return array 検索条件情報リスト
         */
        protected function getScheduleTermsList(mysql_db $db, $userId)
        {
            $favoriteSearchTermsList = $this->getFavoriteSearchTermsList($db, $userId);

            $autoSearchScheduleObj  = new AutoSearchSchedule($db);
            $autoSearchScheduleList = $autoSearchScheduleObj->getAutoSearchScheduleListFromUserId($userId);
            $scheduleDataList       = $autoSearchScheduleObj->addAutoSearchScheduleSelectList($autoSearchScheduleList);

            $resultList = array();
            foreach ($favoriteSearchTermsList as $key => $favoriteSearchTerms) {
                // -------------------------------------------
                // 再検索スケジュールの設定
                // -------------------------------------------
                $favoriteSearchTerms['schedule_list']        = array();
                $favoriteSearchTerms['schedule_string_list'] = array();
                // 保存リストに再検索スケジュールを統合する
                foreach ($scheduleDataList as $scheduleDataKey => $scheduleData) {
                    if ($favoriteSearchTerms['id'] != $scheduleData['favorite_search_terms']) {
                        continue;
                    }

                    $executeDateStr  = date('Y/m/d H:i', strtotime($scheduleData['execute_datetime']));
                    $scheduleDiv     = $scheduleData['schedule_div'];
                    $scheduleDivName = $scheduleData['divList'][$scheduleDiv]['title'];

                    $favoriteSearchTerms['schedule_list'][]        = $scheduleData;
                    $favoriteSearchTerms['schedule_string_list'][] = sprintf("%s %s", $executeDateStr, $scheduleDivName);
                    unset($scheduleDataList[$scheduleDataKey]);
                }
                // スケジュールを保持しているかどうか
                $favoriteSearchTerms['has_schedule'] = count($favoriteSearchTerms['schedule_list']) > 0;

                // 保存リストが検索実行中かどうか
                $favoriteSearchTerms['is_search_running'] = $this->isSaveSearchRunning($userId, $favoriteSearchTerms['qid']);

                $resultList[$key] = $favoriteSearchTerms;
            }
            return $resultList;
        }

        /**
         * 新規未反映企業が存在する保存リストの数を取得する.
         *
         * @param mysql_db $db DB
         * @param int $user_id ユーザーID
         * @return int 保存リスト数
         */
        public function getNewDataExistsTermsCount(mysql_db $db, $user_id)
        {

            $scheduleTermsList = $this->getFavoriteSearchTermsList($db, $user_id);
            $newCount          = 0;
            foreach ($scheduleTermsList as $scheduleTerms) {
                if ($scheduleTerms['research_diff_count']['add_row'] > 0) {
                    $newCount++;
                }
            }
            return $newCount;
        }

        /**
         * ツリー構造保存リスト情報を取得する.
         *
         * @param mysql_db $db
         * @param int $user_id ユーザーID
         * @param int $search_result_save
         * @return array ツリー構造保存リスト情報
         */
        public function getSaveTreeList(mysql_db $db, $user_id, $search_result_save)
        {
            $searchTermsTree   = $this->getSearchTermsTreeArray($db, $user_id);
            $scheduleTermsList = $this->getScheduleTermsList($db, $user_id);

            // 階層
            $level = 0;
            $list  = array();
            foreach ($searchTermsTree as $treeKey => $treeData) {
                if ($treeData == '') {
                    continue;
                }
                // ツリー情報からリスト情報を取得する
                $matchResult = preg_match('/^(.+?)(<span[^>]+?>(\d*)<\/span>)?$/iu', $treeData, $m);
                if ($matchResult != 1 || !array_key_exists('3', $m)) {
                    if ($m[1] == 'down') {
                        $level++;
                    } else if ($m[1] == 'up') {
                        $level--;
                    }
                    continue;
                }
                $termsId = $m[3];
                // --------------------------------------------
                // 検索条件以外の場合
                // --------------------------------------------
                if (!array_key_exists($termsId, $scheduleTermsList)) {
                    $list[] = array(
                        'level'     => $level,
                        'name'      => $m[1],
                        'is_leaf'   => false,
                        'list_mode' => null,
                        'id'        => null,
                        'terms'     => null
                    );
                    continue;
                }
                // --------------------------------------------
                // 検索条件の場合
                // --------------------------------------------
                $scheduleTerms = $scheduleTermsList[$termsId];
                $list_terms    = $scheduleTerms['list_terms'];

                //検索結果保存
                if (array_key_exists('saved', $list_terms) && $list_terms['saved'] == 1) {
                    $list_mode = ' saved';
                } else {
                    $list_mode = ' not_saved';
                }
                //自動再検索
                if ($scheduleTerms['has_schedule']) {
                    $list_mode .= ' auto_search';
                }
                //リスト保存不可の場合
                if (!$search_result_save) {
                    $list_mode = ' not_saved';
                }

                $list[] = array(
                    'level'     => $level,
                    'name'      => $m[1],
                    'is_leaf'   => true,
                    'list_mode' => $list_mode,
                    'id'        => $termsId,
                    'terms'     => $scheduleTerms
                );
            }
            // ツリー構造にする
            $treeData = array('list' => array());
            foreach ($list as $data) {
                $treeData = $this->createTree($data, $treeData);
            }
            // ツリーのディレクトリに配下の保蔵リストの件数を設定する
            foreach ($treeData['list'] as &$data) {
                if ($data['is_leaf']) {
                    continue;
                }
                $data['list_all_count'] = $this->setTreeSaveListCount($data);
            }
            return $treeData;
        }

        /**
         * データのレベル値によりツリー構造を再帰的に生成する.
         *
         * @param array $data データ
         * @param array $resultData ツリー構造データ
         * @param int $currentlevel 現在階層
         * @return array ツリー構造データ
         */
        protected function createTree($data, $resultData, $currentlevel = 0)
        {
            if ($data['level'] == $currentlevel) {
                if (!isset($resultData['list'])) {
                    $resultData['list'] = array();
                }
                $resultData['list'][] = $data;
                return $resultData;
            }
            $index                                   = count($resultData['list']) - 1;
            $resultData['list'][$index]['has_child'] = 1;

            $currentlevel++;
            $resultData['list'][$index] = $this->createTree($data, $resultData['list'][$index], $currentlevel);
            return $resultData;
        }

        /**
         * ツリーのディレクトリに配下の保蔵リストの件数を設定する.
         *
         * @param type $data
         * @return int
         */
        protected function setTreeSaveListCount(&$data)
        {
            // 保存リストの場合は1を返す
            if ($data['is_leaf']) {
                return 1;
            }
            // ディレクトリの場合で、子要素が存在しない場合は0を返す
            if (!isset($data['has_child']) || $data['has_child'] != 1) {
                $count                  = 0;
                $data['list_all_count'] = $count;
                return $count;
            }
            // ディレクトリの場合で、子要素が存在する場合
            $count = 0;
            foreach ($data['list'] as &$child) {
                $count += $this->setTreeSaveListCount($child);
            }
            $data['list_all_count'] = $count;
            return $count;
        }

        /**
         *
         * マスタ保存用ディレクトリを作成
         *
         * @param $user_id
         * @param $qid
         */
        public function make_temp_dir($user_id, $qid)
        {
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
            $this->temp_dir = $temp_dir;
        }

        /**
         * 保存マスタがあるかチェック
         *
         * @param $user_id
         * @param $qid
         * @return int
         */
        public function checkMaster($user_id, $qid)
        {
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
        public function checkReMaster($user_id, $qid)
        {
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
        public function saveMaster($qid, $subject)
        {
            $env = new Env();

            $login_info  = $_SESSION['login_info'];
            $user_id     = $login_info['id'];
            $user_member = $login_info['user_member'];
            if ($this->mode == 1) {
                $search_result_save = $login_info['core_search_result_save'];
                $arrCond            = $_SESSION['q'][$qid]['arrCond'];
            } else {
                $search_result_save = $login_info['book_search_result_save'];
                $arrCond            = $_SESSION['bq'][$qid]['arrCond'];
            }
            $db         = new mysql_db();
            $table      = 'favorite_search_terms';
            $regist_cnt = $db->count($table, 'user = ?', array($user_id));

            if ($regist_cnt <= $env->search->regist_limit) {
                $length = strlen($subject);
//            if ($length == 0 || $length > 80) {
                if ($length == 0) {
                    return -2; //文字数エラー
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
                'tel_count'   => 0,
                'mail_count'  => 0
            );
            if ($saved) {
                $arrCount = $this->get_master_total($user_id, $qid);
            }
            if ($arrCount) {
                $arrSqlVal += array(
                    'total_count' => $arrCount['total_count'],
                    'tel_count'   => $arrCount['tel_count'],
                    'mail_count'  => $arrCount['mail_count'],
                );
            }

            //検索条件登録件数の取得
            $table = 'favorite_search_terms';
            $db->insert($table, $arrSqlVal);
            if ($saved) {
                $favorite_search_terms = $db->lastInsertID();
                $this->add_search_que($db, $favorite_search_terms, $qid, session_id());
// 20180507 大量データが検索該当する際、キューが削除されてしまうためコメント化
//                if ($this->check_que($user_id, $qid)) {
//                    $this->stop_search_que($db, $user_id, $qid);
//                }
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
        public function get_temp_total($user_id, $qid)
        {
            $user_dir   = $this->list_dir . '/' . $user_id;
            $temp_dir   = $user_dir . '/' . $qid;
            $file       = $temp_dir . '/' . self::COUNT_FILE;
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
        public function add_terms_tree(mysql_db $db, $user_id, $subject)
        {
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
        public function check_temp_dir($user_id, $qid)
        {
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
        public function remove_dir($dir = null)
        {

            if (is_null($dir)) {
                $dir = $this->temp_dir;
            }

            if (!file_exists($dir)) {
                return;
            }

            $cnt = 0;

            $handle = opendir($dir);
            if (!$handle) {
                return;
            }

            while (false !== ($item = readdir($handle))) {
                if ($item === "." || $item === "..") {
                    continue;
                }

                $path = $dir . DIRECTORY_SEPARATOR . $item;

                if (is_dir($path)) {
                    // 再帰的に削除
                    $cnt = $cnt + $this->remove_dir($path);
                } else {
                    // ファイルを削除
                    unlink($path);
                }
            }
            closedir($handle);

            // ディレクトリを削除
            if (isEmptyDir($dir) && !rmdir($dir)) {
                return;
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
        public function getMasterList($user_id, $qid, $key_init = true)
        {
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
                $row[$idx_key]     = $i;
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
        public function getReMasterList($user_id, $qid, $key_init = true)
        {
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
        public function check_saved($user_id, $qid)
        {
            $db = new mysql_db();

            $list_terms   = $db->get('list_terms', 'favorite_search_terms', 'user = ? AND qid = ?', array($user_id, $qid));
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
        public function get_search_terms(mysql_db $db, $user_id, $qid)
        {

            $search_terms = $db->get('search_terms', 'favorite_search_terms', 'user = ? AND qid = ?', array($user_id, $qid));
            $arrCond      = json_decode($search_terms, true);

            if (is_null($arrCond)) {
                $arrCond = array();
            }

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
        public function add_master_row($user_id, $user_member, $qid, $arrData)
        {
            /*
              $user_dir = $this->list_dir . '/' . $user_id;
              $qid_dir = $user_dir . '/' . $qid;
             */
            if (!$this->check_temp_dir($user_id, $qid)) {
                return false;
            }
            $master_path = $this->temp_dir . '/' . self::MASTER;
            $arrMaster   = null;
            if (file_exists($master_path)) {
                $arrMaster = json_decode(file_get_contents($master_path), true);
            }
            $arrMaster = is_null($arrMaster) ? array() : $arrMaster;
            $master_cnt = count($arrMaster);

            $db      = new mysql_db();
            $max_cnt = $db->get('book_search_max', 'user', 'id = ?', array($user_id));
            if ($master_cnt >= $max_cnt) {
                return false;
            }

            if (array_key_exists('execute_date', $arrData)) {
                $arrData['create_date'] = $arrData['execute_date'];
            } else {
                $arrData['create_date'] = null;
            }

            if (array_key_exists('add_row', $arrData)) {
                unset($arrData['add_row']);
            }

            if ($this->mode == 1) {
                $m_key          = $arrData['url'];
                $tel_key        = 'tel';
                $email_key      = 'email';
                $arrData['idx'] = $master_cnt + 1;
            } else {
                $m_key            = $arrData['i_company_name'] . '_' . $arrData['i_tel'];
                $tel_key          = 'i_tel';
                $email_key        = 'i_email';
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
            if (array_key_exists($m_key, $arrMaster)) {
                $arrMaster[$m_key] = $arrData;
                file_put_contents($master_path, json_encode($arrMaster));
                return true;
            } else {
                $arrMaster[$m_key] = $arrData;
                file_put_contents($master_path, json_encode($arrMaster));
            }

            $arrCount                = $db->getRow('total_count, tel_count, mail_count', 'favorite_search_terms', 'user = ? AND qid = ?', array($user_id, $qid));
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
        private function update_count($user_id, $qid, $arrData)
        {

            $db                      = new mysql_db();
            $arrCount                = $db->getRow('total_count, tel_count, mail_count', 'favorite_search_terms', 'user = ? AND qid = ?', array($user_id, $qid));
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
        public function deleteMasterFile($user_id, $qid)
        {
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
        public function deleteReMasterFile($user_id, $qid)
        {
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
        public function deleteMaster($terms_id)
        {

            //qidを取得
            $db = new mysql_db();

            $db->begin();
            $arrData = $db->getRow('user, qid', 'favorite_search_terms', 'id = ?', array($terms_id));

            //再検索中の場合は、停止
            $this->check_que_stop_instruction($arrData['user'], $arrData['qid']);
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
         * 保存マスタを削除
         *
         * @param $terms_id
         * @return int
         */
        public function deleteSaveTerms(mysql_db $db, $terms_id)
        {

            $arrData = $db->getRow('user, qid', 'favorite_search_terms', 'id = ?', array($terms_id));

            $db->delete('auto_search_que', 'favorite_search_terms = ?', array($terms_id));
            $db->delete('favorite_search_terms', 'id = ?', array($terms_id));

            if ($this->isSaveSearchRunning($arrData['user'], $arrData['qid'])) {
                //再検索中の場合は、停止ファイルを生成して終了
                $this->que_stop_instruction($arrData['user'], $arrData['qid'], 1);
                return;
            }
            // 再検索中でない場合は、保存マスタを削除する
            $result = $this->check_temp_dir($arrData['user'], $arrData['qid']);
            if (!$result) {
                return -1;
            }
            $this->remove_dir();
            return 1;
        }

        /**
         * 保存リストツリーデータを更新する.
         *
         * @param int $userId ユーザーID
         * @param array $treeData ツリーデータ
         */
        public function updateSearchTermsTree(mysql_db $db, $userId, $treeData)
        {
            $updateColumn = $this->mode == 1 ? 'search_terms_tree' : 'book_search_terms_tree';
            $db->update('user', array($updateColumn => $treeData), 'id = ?', array($userId));
        }

        /**
         * 保存済みマスタをディレクトリごと削除
         *
         * @param $user_id
         */
        public function deleteMasterAll($user_id)
        {
            $user_dir = $this->list_dir . '/' . $user_id;
            $this->remove_dir($user_dir);
        }

        /**
         *
         * 再検索マスタを保存済みマスタにコピーする
         *
         * @param mysql_db $db
         * @param $user_id
         * @param $qid
         * @param bool $forced
         */
        public function cpReMaster(mysql_db $db, $user_id, $qid, $forced = false)
        {
            $list_terms   = $db->get('list_terms', 'favorite_search_terms', 'qid = ? ', array($qid));
            $arrListTerms = json_decode($list_terms, true);
            if (!array_key_exists('saved', $arrListTerms)) {
                return;
            } else {
                if (!$arrListTerms['saved']) {
                    return;
                }
            }

            if ($this->checkMaster($user_id, $qid)) {
                if (!$forced) {
                    //保存済みマスタがあるためコピーしない
                    return;
                }
            }

            $arrRemaster = $this->getReMasterList($user_id, $qid, false);
            if (!$arrRemaster) {
                //再検索マスタが無い
                return;
            }

            //
            $master_path = $this->temp_dir . '/' . self::MASTER;
            file_put_contents($master_path, json_encode($arrRemaster));
            chmod($master_path, 0777);
            $total_count = 0;
            $tel_count   = 0;
            $mail_count  = 0;
            foreach ($arrRemaster as $re_master) {
                $total_count++;
                if ((array_key_exists('tel', $re_master) && $re_master['tel']) || (array_key_exists('i_tel', $re_master) && $re_master['i_tel'])) {
                    $tel_count++;
                }
                if ((array_key_exists('email', $re_master) && $re_master['email']) || (array_key_exists('i_email', $re_master) && $re_master['i_email'])) {
                    $mail_count++;
                }
            }
            $arrSqlVal = array(
                'total_count' => $total_count,
                'tel_count'   => $tel_count,
                'mail_count'  => $mail_count
            );
            $db->update('favorite_search_terms', $arrSqlVal, 'qid = ?', array($qid));
        }

        /**
         * 合計件数の更新
         *
         * @param mysql_db $db
         * @param $user_id
         * @param $qid
         */
        public function update_total_count(mysql_db $db, $user_id, $qid)
        {
            //$db = new mysql_db();
            $col = <<< EOF
            id,
            CASE
                WHEN last_execute_datetime = '0000-00-00 00:00:00' THEN ''
                ELSE last_execute_datetime
            END AS last_execute_datetime
EOF;

            $arrData                  = $db->getRow($col, 'favorite_search_terms', 'qid = ?', array($qid));
            $favorite_search_terms_id = $arrData['id'];
            if ($favorite_search_terms_id) {
                //$arrCount = $this->get_temp_total($user_id, $qid);

                $arrCount  = $this->get_master_total($user_id, $qid);
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
        public function get_master_total($user_id, $qid)
        {
            $arrMaster = $this->getMasterList($user_id, $qid);
            if (!$arrMaster) {
                return array();
            }
            if ($this->mode == 1) {
                $tel_key  = 'tel';
                $mail_key = 'email';
            } else {
                $tel_key  = 'i_tel';
                $mail_key = 'i_email';
            }

            $total_count = 0;
            $tel_count   = 0;
            $mail_count  = 0;
            foreach ($arrMaster as $item) {
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
        public function add_re_search_que($qid)
        {
            $db = new mysql_db();

            $arrData                  = $db->getRow('id, user', 'favorite_search_terms', 'qid = ?', array($qid));
            $favorite_search_terms_id = $arrData['id'];
            $user_id                  = $arrData['user'];
            //最終検索実行日の更新
            $db->update('favorite_search_terms', array('last_execute_datetime' => 'CURRENT_TIMESTAMP'), 'qid = ?', array($qid));

            return $this->add_search_que($db, $favorite_search_terms_id, $qid, session_id());
        }

        /**
         * 検索キューの追加
         *
         * @param mysql_db $db
         * @param $favorite_search_terms_id
         * @param $qid
         */
        private function add_search_que(mysql_db $db, $favorite_search_terms_id, $qid, $session_id = null)
        {
            $user_id = $db->get('user', 'favorite_search_terms', 'qid = ?', array($qid));
            if (!$this->check_temp_dir($user_id, $qid)) {
                return;
            }

            $execute_status = 1;
            $end_datetime   = '';
            $del_flg        = 0;

            //検索キューに追加
            $arrQue = array(
                'favorite_search_terms' => $favorite_search_terms_id,
                'execute_status'        => $execute_status,
                'start_datetime'        => 'CURRENT_TIMESTAMP',
                'end_datetime'          => $end_datetime,
                'del_flg'               => $del_flg,
                'session_id'            => $session_id
            );

            $db->insert('auto_search_que', $arrQue);
            $lastInsertId = $db->lastInsertID();

            $stop_que_file = $this->temp_dir . '/' . 'stop_que';
            if (file_exists($stop_que_file)) {
                $que_code = $this->check_que_stop_instruction($user_id, $qid);
                if ($que_code == 5) {
                    //全件取得済みのためキューの停止
                    $this->stop_search_que($db, $user_id, $qid);
                }
                //unlink($stop_que_file);
            }
            return $lastInsertId;
        }

        public function update_auto_re_search_que($db, $favorite_search_terms_id)
        {
            $arrQue = array(
                'start_datetime' => 'CURRENT_TIMESTAMP'
            );
            $db->update('auto_search_que', $arrQue, 'favorite_search_terms = ? AND start_datetime IS NULL', array($favorite_search_terms_id));

            //最終実行日の更新
            $arrQue = array(
                'last_execute_datetime' => 'CURRENT_TIMESTAMP'
            );
            $db->update('favorite_search_terms', $arrQue, 'id = ?', array($favorite_search_terms_id));
        }

        /**
         *
         * @param mysql_db $db
         * @param type $qid
         */
        public function deleteAutoSearchQueFromQid($db, $qid = null)
        {
            if (is_null($qid)) return;
            if (is_scalar($db)) return;

            $arrVal = array(
                'end_datetime' => 'CURRENT_TIMESTAMP',
                'del_flg'      => 1
            );

            $where = 'favorite_search_terms = (SELECT id FROM favorite_search_terms WHERE qid = ?) '
                    . ' AND del_flg = 0 '
                    . ' AND execute_status = ? ';

            $db->update('auto_search_que', $arrVal, $where, array(
                $qid,
                SEARCH_QUE_EXEC_STATUS_NORMAL));
        }

        /**
         * 検索キューの停止
         *
         * @param $db
         * @param $user_id
         * @param $qid
         * @return bool
         */
        public function stop_search_que(mysql_db $db, $user_id, $qid)
        {
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

                $this->cpReMaster($db, $user_id, $qid);
            }
        }

        /**
         * 検索キューを停止指示中かチェック
         *
         * @param $user_id
         * @param $qid
         * @return int
         */
        public function check_que_stop_instruction($user_id, $qid)
        {
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
        public function que_stop_instruction($user_id, $qid, $code)
        {
            if (!$this->check_temp_dir($user_id, $qid)) {
                return 0;
            }

            // 実行中でない場合はキューを削除する
            if (!$this->isSaveSearchRunning($user_id, $qid)) {
                $db = new mysql_db();
                $this->deleteAutoSearchQueFromQid($db, $qid);
            }

            $stop_que_file = $this->temp_dir . '/' . 'stop_que';
            $debugUtil  = new DebugUtil();
            $debugUtil->logOut("$qid : [crawler処理] 検索キュー停止指示");
            file_put_contents($stop_que_file, $code);
        }

        public function que_stop_instruction_terms(mysql_db $db, $terms_id, $code)
        {
            $data = $db->getRow('user, qid', 'favorite_search_terms', 'id = ?', array($terms_id));
            $this->que_stop_instruction($data['user'], $data['qid'], $code);
        }

        public function check_re_master_cnt($user_id, $qid, $parse_cnt)
        {
            $arrReMaster = $this->getReMasterList($user_id, $qid);
            if (is_array($arrReMaster)) {
                if ($parse_cnt <= count($arrReMaster)) {
                    return true;
                }
            }
            return false;
        }

        public function get_list_info(mysql_db $db, $qid)
        {
            $col = <<< EOF
subject, total_count, tel_count, mail_count,
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
        public function temp_organize(mysql_db $db, $user_id)
        {
            $user_dir = $this->list_dir . '/' . $user_id;
            if (!file_exists($user_dir)) {
                return;
            }
            $arrQid = scandir($user_dir);
            foreach ($arrQid as $qid) {
                if ($qid == '.' || $qid == '..') {
                    continue;
                }

                $master    = $user_dir . '/' . $qid . '/' . self::MASTER;
                $re_master = $user_dir . '/' . $qid . '/' . self::RE_MASTER;
                $ret       = $db->get('id', 'favorite_search_terms', 'qid = ?', array($qid));
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
        public function que_lock($user_id, $qid)
        {
            if (!$this->check_temp_dir($user_id, $qid)) {
                return false;
            }

            $que_file = $this->temp_dir . '/' . 'que';
            $fp       = fopen($que_file, 'a');
            flock($fp, LOCK_EX | LOCK_NB);
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
        public function que_un_lock($fp, $user_id, $qid)
        {
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
         * 保存リストが検索実行中かどうか調べる.
         *
         * @return boolean true:検索実行中, false:検索実行中でない
         */
        public function isSaveSearchRunning($user_id, $qid)
        {
            //保存ディレクトリが無い場合は検索実行中でないとみなす
            if (!$this->check_temp_dir($user_id, $qid)) {
                return false;
            }
            // クローラー検索実行中（新規・再検索）の場合
            if (!$this->canLockQueSearch($user_id, $qid)) {
                return true;
            }
            // Indeed検索実行中（新規）の場合
            if (!$this->canLockNewSearch($user_id, $qid)) {
                return true;
            }
            // 自動再検索中の場合
            if (!$this->canLockAutoSearch($user_id, $qid)) {
                return true;
            }
            return false;
        }

        /**
         * (再)検索中かチェック
         *
         * @param $user_id
         * @param $qid
         * @param $execute_status
         * @return bool
         */
        public function check_que($user_id, $qid, $execute_status = null)
        {
            if (!$this->check_temp_dir($user_id, $qid)) {
                return false;
            }

            // 自動再検索がロックできない場合
            if (!$this->canLockAutoSearch($user_id, $qid)) {
                return false;
            }

            $que_file = $this->temp_dir . '/' . 'que';
            if (file_exists($que_file)) {
                $fp = fopen($que_file, 'r+');

                if (flock($fp, LOCK_EX | LOCK_NB)) {
                    fclose($fp);
                    return true;
                } else {
                    return false;
                }
            }

            return true;
        }

        public function get_active_qid(mysql_db $db, $user_id)
        {
            $table = 'favorite_search_terms INNER JOIN auto_search_que ON auto_search_que.favorite_search_terms = favorite_search_terms.id';
            $where = 'favorite_search_terms.user = ? AND favorite_search_terms.core_book = ? AND auto_search_que.del_flg = 0 AND auto_search_que.start_datetime IS NOT NULL';
            $qid   = $db->get('qid', $table, $where, array($user_id, $this->mode));

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
        public function set_re_master_count($user_id, $qid, $total_count, $loaded_count, $max_result, $master = null)
        {
            if (!$this->check_temp_dir($user_id, $qid)) {
                return false;
            }

            $limit_count = $total_count;
            if ($total_count > $max_result) {
                $limit_count = $max_result;
            }

            $arrReMasterCount     = array(
                'loaded_count'       => $loaded_count,
                'total_count'        => $limit_count,
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
        public function get_master_count($user_id, $qid)
        {
            if (!$this->check_temp_dir($user_id, $qid)) {
                return array('loaded_count' => 0, 'total_count' => 0, 'actual_total_count' => 0);
            }

            $master_count_file = $this->temp_dir . '/' . 'master_count';
            if (!file_exists($master_count_file)) {
                //再検索マスタが無い場合は、保存マスタから取得
                $arrMaster = $this->getMasterList($user_id, $qid);
                if (!$arrMaster) {
                    return array('loaded_count' => 0, 'total_count' => 0, 'actual_total_count' => 0);
                } else {
                    return array('loaded_count' => count($arrMaster), 'total_count' => count($arrMaster), 'actual_total_count' => count($arrMaster));
                }
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
        public function get_re_master_count($user_id, $qid)
        {
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
        public function getDiffList($user_id, $qid)
        {
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
                        $master_row      = $arrMaster[$rm_key];
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

        public function getDiffCountInfo($user_id, $qid)
        {

            // 差分リスト取得
            $diffList = $this->getDiffList($user_id, $qid);

            $countArray = array(
                'normal_row' => 0,
                'add_row'    => 0,
                'update_row' => 0
            );

            foreach ($diffList as $key => $diff) {
                $rowType = $diff['row_type'];
                if (isset($countArray[$rowType])) {
                    $countArray[$rowType] ++;
                };
            }
            return $countArray;
        }

        /**
         * ログインセッション別再検索ロックが可能かどうか調べる.
         *
         * @param string $session_id セッションID
         * @return boolean true:可能, false:不可能
         */
        public function canLockLoginSessionReSearch($session_id)
        {
            $sessionDir  = $this->list_dir . '/login_session';
            $sessionFile = $sessionDir . '/' . $session_id;

            if (!file_exists($sessionFile)) {
                return true;
            }
            $fp = fopen($sessionFile, 'r');
            if (!flock($fp, LOCK_EX | LOCK_NB)) {
                return false;
            }
            flock($fp, LOCK_UN);
            fclose($fp);
            return true;
        }

        /**
         * ログインセッション別再検索ロックを取得する.
         *
         * @param mysql_db $db DB
         * @param string $session_id セッションID
         * @param int $user_id ユーザーID
         * @param int $qid キューID
         * @return resource $fp ファイルポインタリソース
         */
        public function lockLoginSessionReSearch(mysql_db $db, $session_id, $user_id, $qid)
        {
            // ロック可能か調べる
            if ($this->canLockLoginSessionReSearch($session_id)) {
                return $this->lockLoginSession($session_id);
            }
            // 同一セッションIDで実行している他のキューを取得
            $otherQueList = $this->getRunningReSearchQueList(
                    $db, $this->mode, $session_id, $qid);

            // 取得キューに停止処理を行う
            foreach ($otherQueList as $otherQue) {
                $this->que_stop_instruction($user_id, $otherQue['qid'], 1);
            }
            // 最大10回、ロック可能かどうか実施する
            $count = 1;
            while ($count <= 10) {
                // 2秒スリープする
                sleep(2);
                // ロック可能になるまで繰り返す
                if ($this->canLockLoginSessionReSearch($session_id)) {
                    return $this->lockLoginSession($session_id);
                }
                $count++;
            }
            // それでもロックできなかった場合はfalseを返す
            return false;
        }

        /**
         * ログインセッション別ロックを取得する.
         *
         * @param string $session_id セッションID
         * @return resource $fp ファイルポインタリソース
         */
        public function lockLoginSession($session_id)
        {
            $sessionDir = $this->list_dir . '/login_session';
            if (!file_exists($sessionDir)) {
                mkdir($sessionDir, 0777);
                chmod($sessionDir, 0777);
            }
            $sessionFile = $sessionDir . '/' . $session_id;
            $fp          = fopen($sessionFile, 'a');
            if (!flock($fp, LOCK_EX | LOCK_NB)) {
                return false;
            }
            fputs($fp, $session_id);
            chmod($sessionFile, 0777);
            return $fp;
        }

        /**
         * ログインセッション別再検索ロックを開放する.
         *
         * @param resource $fp ファイルポインタリソース
         * @param string $session_id セッションID
         */
        public function unlockLoginSessionReSearch($fp, $session_id)
        {

            $sessionDir  = $this->list_dir . '/login_session';
            $sessionFile = $sessionDir . '/' . $session_id;

            if (!file_exists($sessionFile)) {
                return;
            }
            if (is_null($fp)) {
                return;
            }
            fclose($fp);
            unlink($sessionFile);
        }

        /**
         * キューがロックされているか調べる.
         *
         * @param type $user_id
         * @param type $qid
         * @return boolean
         */
        public function canLockQueSearch($user_id, $qid)
        {
            return $this->canLockSearchFile($user_id, $qid, 'que');
        }

        /**
         * 新規検索をロックする.
         *
         * @param int $user_id ユーザーID
         * @param int $qid キューID
         * @return resource 成功した場合にファイルポインタリソース、エラー時に FALSE を返します。
         */
        public function lockNewSearch($user_id, $qid)
        {
            return $this->lockSearchFile($user_id, $qid, 'new_search');
        }

        /**
         * 新規検索をアンロックする.
         *
         * @param resource $fp ファイルポインタリソース
         * @param int $user_id ユーザーID
         * @param int $qid キューID
         */
        public function unlockNewSearch($fp, $user_id, $qid)
        {
            $this->unlockSearchFile($fp, $user_id, $qid, 'new_search');
        }

        /**
         * 新規検索がロック可能か調べる
         *
         * @param int $user_id ユーザーID
         * @param int $qid キューID
         * @return boolean true:ロックされている, false:されていない
         */
        public function canLockNewSearch($user_id, $qid)
        {
            return $this->canLockSearchFile($user_id, $qid, 'new_search');
        }

        /**
         * 自動再検索をロックする.
         *
         * @param int $user_id ユーザーID
         * @param int $qid キューID
         * @return resource 成功した場合にファイルポインタリソース、エラー時に FALSE を返します。
         */
        public function lockAutoReSearch($user_id, $qid)
        {
            return $this->lockSearchFile($user_id, $qid, 'auto_re_search');
        }

        /**
         * 自動再検索をアンロックする.
         *
         * @param resource $fp ファイルポインタリソース
         * @param int $user_id ユーザーID
         * @param int $qid キューID
         */
        public function unLockAutoReSearch($fp, $user_id, $qid)
        {
            $this->unlockSearchFile($fp, $user_id, $qid, 'auto_re_search');
        }

        /**
         * 自動再検索がロック可能か調べる
         *
         * @param int $user_id ユーザーID
         * @param int $qid キューID
         * @return boolean true:ロックされている, false:されていない
         */
        public function canLockAutoSearch($user_id, $qid)
        {
            return $this->canLockSearchFile($user_id, $qid, 'auto_re_search');
        }

        /**
         * 検索をロックする.
         *
         * @param int $user_id ユーザーID
         * @param int $qid キューID
         * @param string $fileName ロックファイル名
         * @return resource 成功した場合にファイルポインタリソース、エラー時に FALSE を返します。
         */
        protected function lockSearchFile($user_id, $qid, $fileName)
        {
            if (!$this->check_temp_dir($user_id, $qid)) {
                return false;
            }
            $autoReSearchFile = $this->temp_dir . '/' . $fileName;
            $fp               = fopen($autoReSearchFile, 'a');
            if (!flock($fp, LOCK_EX | LOCK_NB)) {
                fclose($fp);
                return false;
            }
            fputs($fp, $qid);
            chmod($autoReSearchFile, 0777);
            return $fp;
        }

        /**
         * 検索をアンロックする.
         *
         * @param resource $fp ファイルポインタリソース
         * @param int $user_id ユーザーID
         * @param int $qid キューID
         * @param string $fileName ロックファイル名
         */
        protected function unlockSearchFile($fp, $user_id, $qid, $fileName)
        {
            if (!$this->check_temp_dir($user_id, $qid)) {
                return;
            }
            $autoReSearchFile = $this->temp_dir . '/' . $fileName;
            if (!file_exists($autoReSearchFile)) {
                return;
            }
            if (is_null($fp)) {
                return;
            }
            fclose($fp);
            unlink($autoReSearchFile);
        }

        /**
         * 検索がロック可能か調べる
         *
         * @param int $user_id ユーザーID
         * @param int $qid キューID
         * @param string $fileName ロックファイル名
         * @return boolean true:ロックされている, false:されていない
         */
        protected function canLockSearchFile($user_id, $qid, $fileName)
        {

            if (!$this->check_temp_dir($user_id, $qid)) {
                return false;
            }
            $autoReSearchFile = $this->temp_dir . '/' . $fileName;
            if (!file_exists($autoReSearchFile)) {
                return true;
            }
            $fp = fopen($autoReSearchFile, 'r+');
            if (!flock($fp, LOCK_EX | LOCK_NB)) {
                return false;
            }
            fclose($fp);
            return true;
        }

        /**
         * 再検索完了メール通知ファイルパスを取得する.
         *
         * @param type $user_id
         * @param type $qid
         * @return type
         */
        protected function getReSearchFinishMailSendFilePath($user_id, $qid)
        {
            if (!$this->check_temp_dir($user_id, $qid)) {
                return null;
            }
            return $this->temp_dir . '/' . 're_search_finish_mail_send';
        }

        /**
         * 再検索完了メール通知ファイルが存在するか調べる.
         *
         * @param type $user_id
         * @param type $qid
         * @return boolean
         */
        public function existsReSearchFinishMailSendFile($user_id, $qid)
        {
            $filePath = $this->getReSearchFinishMailSendFilePath($user_id, $qid);
            return file_exists($filePath);
        }

        /**
         * 再検索完了メール通知ファイルを作成する.
         *
         * @param type $user_id
         * @param type $qid
         */
        public function createReSearchFinishMailSendFile($user_id, $qid)
        {
            $filePath = $this->getReSearchFinishMailSendFilePath($user_id, $qid);
            touch($filePath);
            chmod($filePath, 0777);
        }

        /**
         * 再検索完了メール通知ファイルを削除する.
         *
         * @param type $user_id
         * @param type $qid
         * @return boolean
         */
        public function deleteReSearchFinishMailSendFile($user_id, $qid)
        {
            $filePath = $this->getReSearchFinishMailSendFilePath($user_id, $qid);
            if (!file_exists($filePath)) {
                return;
            }
            unlink($filePath);
        }

        /**
         *
         * アップロードされたファイルからマスタを作成
         *
         * @param mysql_db $db
         * @param UserLogin $user
         * @param $arrValue
         * @return int
         */
        public function saveUploadList(mysql_db $db, UserLogin $user, $arrValue)
        {

            $filePath      = $arrValue['upfile']['path'];
            $dl            = $arrValue['dl'];
            $subject       = $arrValue['subject'];
            $note          = $arrValue['note'];
            $arrColumnData = $this->arrCsvColumn[$dl];
            $arrMaster     = $this->getCsvData($filePath, $arrColumnData);

            if (count($arrMaster) == 0) {
                return -1;
            }

            //リストが保存可能数を下回っているかチェック
            $rest_count = $this->getRestCount($db, $user->id);
            if (count($arrMaster) > $rest_count) {
                return -2;
            }

            //1リストあたり最大出力可能数を下回っているかチェック
            if (count($arrMaster) > $user->book_search_max) {
                return -3;
            }

            $this->register_master($db, $user, null, null, $subject, $note, $arrMaster);
            @unlink($filePath);

            //
            return 1;
        }

        /**
         * CSVファイルを読み込んで、行列の二次元配列で返す.
         *
         * @param string $filePath ファイルパス
         * @param array $arrColumnData
         *
         * @access protected
         * @return array CSVデータ配列
         */
        protected function getCsvData($filePath, $arrColumnData)
        {

            setlocale(LC_ALL, 'ja_JP.UTF-8');
            $buffer = mb_convert_encoding(file_get_contents($filePath), "UTF-8", "sjis-win");
            $fp     = tmpfile();
            fwrite($fp, $buffer);
            rewind($fp);

            $csvRowList = array();
            $rowCount   = 0;
            while (($line       = fgetcsv($fp, 0, ",")) !== FALSE) {

                $rowCount++;
                // 改行のみの行はスキップする
                if (count($line) === 1 && $line[0] === '') {
                    continue;
                }
                // すべてカラのみの行はスキップする
                $notEmpty = false;
                foreach ($line as $data) {
                    if (!is_null($data) && $data != '') {
                        $notEmpty = true;
                        break;
                    }
                }
                if (!$notEmpty) {
                    continue;
                }
                // 1行目はスキップする
                if ($rowCount === 1) {
                    continue;
                }

                // 2行目以降
                // 項目名をキーに持つ配列とする
                $csvRow = array();
                for ($i = 0; $i < count($line); $i++) {
                    /*
                      if (!isset($numberRecord[$i]) || !isset($arrColumnData[$i])) {
                      continue;
                      }
                     */

                    //マスタに取り込まない項目はスキップする
                    if (!array_key_exists($i, $arrColumnData)) {
                        continue;
                    }

                    if ($arrColumnData[$i] == '') {
                        continue;
                    }

                    // 項目定義を取得する
                    $colName = $arrColumnData[$i];

                    // DB項目名をキーに値を設定する
                    $csvRow[$colName] = $line[$i];
                }

                if ($this->mode == 1) {
                    //コア
                    if (array_key_exists('url', $csvRow) && $csvRow['url']) {
                        $row_key = $csvRow['url'];
                    } else {
                        //取得元URLはCSVから取得できないためキーをランダム生成する
                        $random_str    = $this->makeRandStr();
                        $row_key       = date('YmdHis') . '_' . $random_str;
                        $csvRow['url'] = $row_key;
                    }
                } else {
                    //ワイド
                    $row_key = $csvRow['i_company_name'] . '_' . $csvRow['i_tel'];
                    if (strlen($row_key) == 1) {
                        $random_str = $this->makeRandStr();
                        $row_key    = date('YmdHis') . '_' . $random_str;
                    }
                }

                $csvRowList[$row_key] = $csvRow;
            }

            return $csvRowList;
        }

        /**
         *
         * ランダム文字列の生成
         *
         * @param int $length
         * @return null|string
         */
        public function makeRandStr($length = 5)
        {
            $str   = array_merge(range('a', 'z'), range('0', '9'), range('A', 'Z'));
            $r_str = null;
            for ($i = 0; $i < $length; $i++) {
                $r_str .= $str[rand(0, count($str) - 1)];
            }
            return $r_str;
        }

        /**
         *
         * リストマージ用マージキー情報の取得
         *
         * @return mixed
         */
        public function getArrMergeKey()
        {
            return $this->arrMergeKey;
        }

        /**
         *
         * リストマージ用マージIDの取得
         *
         * @return mixed
         */
        public function getArrMergeId()
        {
            return $this->arrMergeId;
        }

        public function getMergeMaster(mysql_db $db, $user_id, $terms_id, $arrScrutinyId)
        {

            $base_qid       = $db->get('qid', 'favorite_search_terms', 'id = ?', array($terms_id));
            $arrBaseMaster  = $this->getMasterList($user_id, $base_qid, false);
            $in_str         = implode(',', array_pad(array(), count($arrScrutinyId), '?'));
            $where          = "id IN ({$in_str})";
            $arrScrutinyQid = $db->getCol('qid', 'favorite_search_terms', $where, $arrScrutinyId);

            $arrScrutinyMasters = array();
            foreach ($arrScrutinyQid as $scrutiny_qid) {
                $arrScrutinyMasters[] = $this->getMasterList($user_id, $scrutiny_qid, false);
            }

            return array($arrBaseMaster, $arrScrutinyMasters);
        }

        /**
         *
         * マージリストの保存
         *
         * @param mysql_db $db
         * @param UserLogin $user
         * @param $arrValue
         * @return int
         */
        public function saveMergeList(mysql_db $db, UserLogin $user, $arrValue)
        {

            $terms_id      = $arrValue['list_base'];
            $arrScrutinyId = $arrValue['list_scrutiny'];
            $scrutiny      = $arrValue['scrutiny'];
            $arrMergeKeyId = $arrValue['merge_key'];
            $subject       = $arrValue['subject'];
            $note          = $arrValue['note'];

            list($arrBaseMaster, $arrScrutinyMasters) = $this->getMergeMaster($db, $user->id, $terms_id, $arrScrutinyId);
            foreach ($arrMergeKeyId as $key_id) {
                $this->arrMergeColumn[] = $this->arrMergeKey[$key_id - 1]['column'];
            }

            $arrMaster = array();
            switch ($scrutiny) {
                case 1:
                    //リスト集合
                    $arrMaster = $this->mergeListSet($arrBaseMaster, $arrScrutinyMasters);
                    break;
                case 2:
                    //全件マッチ
                    $arrMaster = $this->mergeAllMatch($arrBaseMaster, $arrScrutinyMasters);
                    break;
                case 3:
                    //基本マッチ
                    $arrMaster = $this->mergeBaseMatch($arrBaseMaster, $arrScrutinyMasters);
                    break;
                case 4:
                    //差分マッチ
                    $arrMaster = $this->mergeDiffMatch($arrBaseMaster, $arrScrutinyMasters);
                    break;
            }

            //マージの結果0件
            if (count($arrMaster) == 0) {
                return -1;
            }

            //マージされたリストが保存可能数を下回っているかチェック
            $rest_count = $this->getRestCount($db, $user->id);
            if (count($arrMaster) > $rest_count) {
                return -2;
            }

            //1リストあたり最大出力可能数を下回っているかチェック
            if (count($arrMaster) > $user->book_search_max) {
                return -3;
            }

            $this->register_master($db, $user, $terms_id, $scrutiny, $subject, $note, $arrMaster);

            //
            return 1;
        }

        /**
         *
         * 残り保存可能数の取得
         *
         * @param mysql_db $db
         * @param $user_id
         * @return integer
         */
        private function getRestCount(mysql_db $db, $user_id)
        {

            //マージされたリストが保存可能数を下回っているかチェック
            $col        = 'user.search_history_save_number - SUM(COALESCE(total_count, 0)) AS rest_target_count';
            $table      = 'user LEFT JOIN favorite_search_terms ON user.id = favorite_search_terms.user';
            $rest_count = $db->get($col, $table, 'user.id = ?', array($user_id));

            return $rest_count;
        }

        /**
         *
         * リスト集合用マージデータ作成
         *
         * 基本リストに精査リストにのみある企業を追加
         *
         * @param $arrBaseMaster
         * @param $arrScrutinyMasters
         * @return mixed
         */
        private function mergeListSet($arrBaseMaster, $arrScrutinyMasters)
        {

            $arrMergeKey = $this->get_arr_merge_key($arrBaseMaster);
            $arrMaster   = $arrBaseMaster;

            //精査リストのリスト集合を作成
            foreach ($arrScrutinyMasters as $arrScrutinyMaster) {
                foreach ($arrScrutinyMaster as $arrScrutiny) {
                    $merge_key = $this->get_merge_key($arrScrutiny);

                    if (!in_array($merge_key, $arrMergeKey)) {
                        if ($this->mode == 1) {
                            if (array_key_exists('url', $arrScrutiny)) {
                                $m_key = $arrScrutiny['url'];
                            } else {
                                $random_str = $this->makeRandStr();
                                $m_key      = date('YmdHis') . '_' . $random_str;
                            }
                        } else {
                            $m_key = $arrScrutiny['i_company_name'] . '_' . $arrScrutiny['i_tel'];
                            if (strlen($m_key) == 1) {
                                $random_str = $this->makeRandStr();
                                $m_key      = date('YmdHis') . '_' . $random_str;
                            }
                        }

                        if (array_key_exists($m_key, $arrMaster)) {
                            $suffixes = $this->makeRandStr();
                            $m_key    .= '_' . $suffixes;
                        }
                        $arrMaster[$m_key] = $arrScrutiny;
                        $arrMergeKey[]     = $merge_key;
                    }
                }
            }

            return $arrMaster;
        }

        /**
         *
         * 全件マッチ用マージデータ作成
         *
         * 基本リストと全ての精査リストにある企業
         *
         * @param $arrBaseMaster
         * @param $arrScrutinyMasters
         * @return array
         */
        private function mergeAllMatch($arrBaseMaster, $arrScrutinyMasters)
        {

            $all_match_count = count($arrScrutinyMasters);
            $arrMaster       = array();
            foreach ($arrBaseMaster as $m_key => $arrBase) {
                $base_merge_key = $this->get_merge_key($arrBase);
                $match_count    = 0;
                foreach ($arrScrutinyMasters as $arrScrutinyMaster) {
                    $scrutiny_match_count = 0;
                    foreach ($arrScrutinyMaster as $arrScrutiny) {
                        $scrutiny_merge_key = $this->get_merge_key($arrScrutiny);
                        if ($base_merge_key == $scrutiny_merge_key && $scrutiny_match_count == 0) {
                            $match_count++;
                            $scrutiny_match_count++;
                            continue;
                        }
                    }
                }

                if ($all_match_count == $match_count) {
                    if (array_key_exists($m_key, $arrMaster)) {
                        $suffixes = $this->makeRandStr();
                        $m_key    .= '_' . $suffixes;
                    }
                    $arrMaster[$m_key] = $arrBase;
                }
            }

            return $arrMaster;
        }

        /**
         *
         * 基本マッチ用マージデータ作成
         *
         * 基本リストにあり、他の精査リストにもある企業
         *
         * @param $arrBaseMaster
         * @param $arrScrutinyMasters
         * @return array
         */
        private function mergeBaseMatch($arrBaseMaster, $arrScrutinyMasters)
        {

            $arrMaster = array();
            foreach ($arrBaseMaster as $m_key => $arrBase) {
                $base_merge_key = $this->get_merge_key($arrBase);
                $match_count    = 0;
                foreach ($arrScrutinyMasters as $arrScrutinyMaster) {
                    $scrutiny_match_count = 0;
                    foreach ($arrScrutinyMaster as $arrScrutiny) {
                        $scrutiny_merge_key = $this->get_merge_key($arrScrutiny);
                        if ($base_merge_key == $scrutiny_merge_key && $scrutiny_match_count == 0) {
                            $match_count++;
                            $scrutiny_match_count++;
                            continue;
                        }
                    }
                }

                if ($match_count > 0) {
                    if (array_key_exists($m_key, $arrMaster)) {
                        $suffixes = $this->makeRandStr();
                        $m_key    .= '_' . $suffixes;
                    }
                    $arrMaster[$m_key] = $arrBase;
                }
            }

            return $arrMaster;
        }

        /**
         *
         * 差分マッチ用マージデータ作成
         *
         * 基本リストにあり、精査リストにはない企業
         *
         * @param $arrBaseMaster
         * @param $arrScrutinyMasters
         * @return array
         */
        private function mergeDiffMatch($arrBaseMaster, $arrScrutinyMasters)
        {

            $arrMaster = array();
            foreach ($arrBaseMaster as $m_key => $arrBase) {
                $base_merge_key = $this->get_merge_key($arrBase);
                $match_count    = 0;
                foreach ($arrScrutinyMasters as $arrScrutinyMaster) {
                    $scrutiny_match_count = 0;
                    foreach ($arrScrutinyMaster as $arrScrutiny) {
                        $scrutiny_merge_key = $this->get_merge_key($arrScrutiny);
                        if ($base_merge_key == $scrutiny_merge_key && $scrutiny_match_count == 0) {
                            $match_count++;
                            $scrutiny_match_count++;
                            continue;
                        }
                    }
                }

                if ($match_count == 0) {
                    if (array_key_exists($m_key, $arrMaster)) {
                        $suffixes = $this->makeRandStr();
                        $m_key    .= '_' . $suffixes;
                    }
                    $arrMaster[$m_key] = $arrBase;
                }
            }

            return $arrMaster;
        }

        /**
         *
         * 保存マスタからマージキー配列を作成
         *
         * @param $arrMaster
         * @return array
         */
        private function get_arr_merge_key($arrMaster)
        {

            $arrMergeKey = array();
            foreach ($arrMaster as $master_line) {
                $merge_key     = $this->get_merge_key($master_line);
                $arrMergeKey[] = $merge_key;
            }

            return $arrMergeKey;
        }

        /**
         *
         * マージキーの生成
         *
         * @param $master_line
         * @return string
         */
        private function get_merge_key($master_line)
        {

            $merge_key = '';
            foreach ($this->arrMergeColumn as $column) {
                if ($column == 'location_detail') {
                    $location_detail = '';
                    if ($master_line['location'] != '') {
                        $location_detail = $master_line['location'];
                    } else {
                        if (preg_match($this->location_detail_pattern, $master_line[$column], $ma)) {
                            $location_detail = $ma[1] . $ma[2];
                        }
                    }

                    $arrMergeData[] = $location_detail;
                } else {
                    $arrMergeData[] = $master_line[$column];
                }

                $merge_key = implode('', $arrMergeData);
            }

            //空文字の場合、ランダム生成
            if (!$merge_key) {
                $rand_str  = $this->makeRandStr();
                $merge_key = time() . $rand_str;
            }

            return $merge_key;
        }

        /**
         *
         * CSVアップロード・リストマージデータの保存
         *
         * @param mysql_db $db
         * @param UserLogin $user
         * @param $terms_id
         * @param $scrutiny
         * @param $subject
         * @param $note
         * @param $arrMaster
         */
        protected function register_master(mysql_db $db, UserLogin $user, $terms_id, $scrutiny, $subject, $note, $arrMaster)
        {
            $qid = time() . rand(100, 999);

            $search_terms = '';
            if ($terms_id) {
                if ($scrutiny == 1) {
                    $search_terms = $db->get('search_terms', 'favorite_search_terms', 'id = ? AND user = ? AND core_book = ?', array($terms_id, $user->id, $this->mode));
                }
            }

            $this->make_temp_dir($user->id, $qid);
            //作成日を更新
            $master_count = count($arrMaster);
            $i            = 1;
            foreach ($arrMaster as $key => $master) {
                if ($master_count == $i) {
                    $master['last_row'] = 1;
                } else {
                    $master['last_row'] = 0;
                }

                $master['done']         = 1;
                $master['create_date']  = date('Y年m月d日 H:i:s');
                $master['execute_date'] = date('Y年m月d日 H:i:s');

                //マスタに必要な項目でデータが無いものは空文字を設定
                foreach ($this->arrColumn as $m_key) {
                    if (!array_key_exists($m_key, $master)) {
                        $master[$m_key] = '';
                    }
                }

                $arrMaster[$key] = $master;

                $i++;
            }
            $master_path = $this->temp_dir . '/' . self::MASTER;
            file_put_contents($master_path, json_encode($arrMaster));

            $list_terms = array(
                'saved'       => 1,
                'auto_search' => ''
            );

            $arrVal = array(
                'user'               => $user->id,
                'core_book'          => $this->mode,
                'subject'            => $subject,
                'note'               => $note,
                'search_terms'       => $search_terms,
                'list_terms'         => json_encode($list_terms),
                'qid'                => $qid,
                'create_user_member' => $user->user_member,
                'update_user_member' => $user->user_member
            );

            $arrCount = $this->get_master_total($user->id, $qid);
            if ($arrCount) {
                $arrVal += array(
                    'total_count' => $arrCount['total_count'],
                    'tel_count'   => $arrCount['tel_count'],
                    'mail_count'  => $arrCount['mail_count'],
                );
            }

            $db->insert('favorite_search_terms', $arrVal);

            $this->add_terms_tree($db, $user->id, $subject);
        }

        /**
         * 保存リストに評価を設定する.
         *
         * @param mysql_db $db DBオブジェクト
         * @param int $terms_id 保存リストID
         * @param float $score 評価
         */
        public function setFavoriteSearchTermsScore(mysql_db $db, $terms_id, $score)
        {

            $sql = <<< _SQL_
                UPDATE favorite_search_terms SET
                    score = :score
                WHERE
                    id = :teams_id
_SQL_;
            return $db->exec($sql, array(
                        'score'    => $score,
                        'teams_id' => $terms_id
            ));
        }

        /**
         * 再検索完了メール情報取得.
         *
         * @param mysql_db $db DB
         * @param int $termsId 保存リストID
         * @return array 再検索完了メール情報
         */
        public function getReSearchFinishMailData(mysql_db $db, $qid)
        {

            $sql = <<< _SQL_
                SELECT
                    FST.*,
                    U.search_mail_address,
                    U.search_mail_template,
                    MT.template_file AS mail_template_file,
                    CASE WHEN FST.core_book = 1 THEN U.core_search_mail_send
                        ELSE U.book_search_mail_send
                        END search_mail_send
                FROM
                    favorite_search_terms FST
                        INNER JOIN user U
                            ON FST.user = U.id
                        INNER JOIN mail_template MT
                            ON U.search_mail_template = MT.id
                WHERE
                    FST.qid = :qid
_SQL_;

            $resultList = $db->getAll($sql, array('qid' => $qid));
            if (count($resultList) === 0) {
                return null;
            }
            return $resultList[0];
        }

        /**
         * 再検索結果件数情報取得.
         *
         * @param type $userId
         * @param type $qid
         * @return type
         */
        protected function getReSearchCountInfo($userId, $qid)
        {

            $reMasterList = $this->getReMasterList($userId, $qid);
            $totalCount   = count($reMasterList);

            $telCount  = 0;
            $mailCount = 0;
            foreach ($reMasterList as $reMaster) {
                if (isset($reMaster['tel']) && $reMaster['tel'] == '') {
                    $telCount++;
                }
                if (isset($reMaster['email']) && $reMaster['email'] == '') {
                    $mailCount++;
                }
            }
            return array(
                'total_count' => $totalCount,
                'tel_count'   => $telCount,
                'mail_count'  => $mailCount
            );
        }

        /**
         * 保存済みリストの取得.
         *
         * @param mysql_db $db
         * @param type $user_id
         * @param type $qid
         * @param type $load_list
         */
        protected function getList(mysql_db $db, $user_id, $qid, $load_list)
        {
            // 継承先でオーバーライド
        }

        /**
         * 再検索差分数情報取得.
         *
         * @param mysql_db $db
         * @param type $userId
         * @param type $qid
         */
        protected function getReSearchDiffCountInfo(mysql_db $db, $userId, $qid)
        {
            // コアサーチで差分結果を取得
            list($result_list, $update_list, $re_search) = $this->getList($db, $userId, $qid, 1);

            $normal_count = 0;
            $add_count    = 0;
            $update_count = 0;
            $delete_count = 0;
            foreach ($result_list as $item) {
                if (!array_key_exists('row_type', $item)) {
                    continue;
                }
                if ($item['row_type'] == 'normal_row') {
                    $normal_count++;
                } elseif ($item['row_type'] == 'add_row') {
                    $add_count++;
                } elseif ($item['row_type'] == 'update_row') {
                    $update_count++;
                } elseif ($item['row_type'] == 'delete_row') {
                    $delete_count++;
                }
            }
            return array(
                'normal_count' => $normal_count,
                'add_count'    => $add_count,
                'update_count' => $update_count,
                'delete_count' => $delete_count
            );
        }

        /**
         * 再検索完了時メール送信.
         *
         * @param mysql_db $db
         * @param type $qid
         * @return type
         */
        public function sendReSearchFinishMail(mysql_db $db, $user_id, $qid)
        {
            // ----------------------------------------
            // 再検索メール送信情報取得
            // ----------------------------------------
            $mailData = $this->getReSearchFinishMailData($db, $qid);
            // 検索完了時メール通知がOFFの場合は何もしない場合
            if ($mailData['search_mail_send'] != 1) {
                return;
            }
            // 保存リストで再検索完了時通知先が一つも設定されていない場合
            if ($mailData['notification_mail_send'] != 1 && is_null($mailData['notification_mail_ex'])) {
                return;
            }

            // ----------------------------------------
            // 各種データ取得
            // ----------------------------------------
            // 保存リスト情報
            $favoriteSearchTerms = $this->getFavoriteSearchTerms($db, $qid);

            // 再検索件数情報
            $reSearchCountInfo = $this->getReSearchCountInfo($user_id, $qid);

            // 再検索結果差分数情報
            $reSearchDiffCountInfo = $this->getReSearchDiffCountInfo($db, $user_id, $qid);

            // ----------------------------------------
            // 送信先メールアドレス設定
            // ----------------------------------------
            $mailToArray = array();
            // 共通通知先
            if ($mailData['notification_mail_send'] == 1) {
                $mailToArray[] = $mailData['search_mail_address'];
            }
            // その他通知先
            $notificationMailExList = is_null($mailData['notification_mail_ex']) ?
                    array() : json_decode($mailData['notification_mail_ex']);
            $mailToArray            = array_merge($mailToArray, $notificationMailExList);

            // ----------------------------------------
            // メール送信
            // ----------------------------------------
            // テンプレート変数設定
            $templateParams = array(
                'mailData'              => $mailData,
                'favoriteSearchTerms'   => $favoriteSearchTerms,
                'reSearchCountInfo'     => $reSearchCountInfo,
                'reSearchDiffCountInfo' => $reSearchDiffCountInfo
            );

            $env      = new Env();
            $mailFrom = $env->auto_research->finish_mail_from;
            $this->sendTemplateMail(
                    $mailData['mail_template_file'], $templateParams, $mailToArray, $mailFrom);

            $debugUtil = new DebugUtil();
            $debugUtil->logOut("再検索完了メール通知送信");
        }

        /**
         * テンプレートを指定してメール送信する.
         *
         * @param string $mailTemplate メールテンプレート
         * @param string $params テンプレート変数
         * @param string $mailToArray 送信先アドレス
         * @param string $mailFrom 送信元アドレス
         */
        public function sendTemplateMail($mailTemplate, $params, $mailToArray, $mailFrom)
        {
            if (!file_exists(MAIL_TEMPLATE_DIR . "/" . $mailTemplate)) {
                return;
            }
            $app                 = new Silex\Application();
            $app->register(new Silex\Provider\TwigServiceProvider(), array(
                'twig.path' => MAIL_TEMPLATE_DIR,
            ));
            $renderData          = $app['twig']->render($mailTemplate, $params);
            $renderDataSplitList = explode("\n", $renderData);
            if (count($renderData) === 0) {
                return;
            }
            $subject = $renderDataSplitList[0];
            unset($renderDataSplitList[0]);
            $body    = implode("\n", $renderDataSplitList);

            foreach ($mailToArray as $mailTo) {
                $this->sendMail($mailTo, $mailFrom, $subject, $body);
            }
        }

        /**
         * メール送信する.
         *
         * @param string $mailTo 送信先メールアドレス
         * @param string $mailFrom 送信元メールアドレス
         * @param string $subject メール件名
         * @param string $body メール本文
         */
        public function sendMail($mailTo, $mailFrom, $subject, $body)
        {
            mb_language("Japanese");
            mb_internal_encoding("UTF-8");
            $headers = 'From: ' . $mailFrom;
            mb_send_mail($mailTo, $subject, $body, $headers);
        }

        /**
         * 同一ログインセッションで、指定キューID以外の実行中キューIDリストを取得する.
         *
         * @param mysql_db $db DB
         * @param int $core_book 検索ターゲット
         * @param string $session_id セッションID
         * @param string $qid キューID
         * @return array キューIDリスト
         */
        public function getRunningReSearchQueList(mysql_db $db, $core_book, $session_id, $qid)
        {

            $sql = "SELECT "
                    . "    FST.qid,"
                    . "    ASQ.* "
                    . "FROM "
                    . "    auto_search_que ASQ "
                    . "        INNER JOIN favorite_search_terms FST "
                    . "            ON ASQ.favorite_search_terms = FST.id "
                    . "WHERE "
                    . "    FST.core_book      = :core_book "
                    . "    AND FST.qid            <> :qid "
                    . "    AND ASQ.execute_status = :execute_status "
                    . "    AND ASQ.session_id     = :session_id "
                    . "    AND ASQ.del_flg        = 0 "
                    . "    AND ASQ.start_datetime IS NOT NULL "
                    . "    AND ASQ.end_datetime   IS NULL ";

            $dataList = $db->getAll($sql, array(
                'core_book'      => $core_book,
                'qid'            => $qid,
                'execute_status' => SEARCH_QUE_EXEC_STATUS_NORMAL,
                'session_id'     => $session_id
            ));

            return $dataList;
        }

        /**
         * 保存リストデータ更新.
         *
         * @param mysql_db $db
         * @param type $id
         * @param type $listTermsData
         */
        public function updateFavoriteSearchTerms(mysql_db $db, $id, $listTermsData)
        {
            $data = $db->getRow('list_terms', 'favorite_search_terms', 'id = ?', array($id));

            $listTerms = json_decode($data['list_terms'], true);
            if (isset($listTermsData['saved'])) {
                $listTerms['saved'] = $listTermsData['saved'];
            }
            if (isset($listTermsData['auto_search'])) {
                $listTerms['auto_search'] = $listTermsData['auto_search'];
            }

            $arrSqlVal = array(
                'list_terms' => json_encode($listTerms),
            );
            $db->update('favorite_search_terms', $arrSqlVal, 'id = ?', array($id));
        }

        /**
         * row_typeを取得する.
         * 
         * @param string $taretKey データのキーとなるURL
         * @param array $targetColumnList 一致するか調べる項目リスト
         * @return string row_type
         */
        protected function getRowType($taretKey, $targetColumnList)
        {
            // 再検索マスタが存在しない場合はnullを返す
            $reMasterPath = $this->temp_dir . '/' . self::RE_MASTER;
            if (!file_exists($reMasterPath)) {
                return null;
            }
            // マスタ、保存マスタのデータが一致するかどうか調べる
            $isMasterEqual = $this->isMasterEqual($taretKey, $targetColumnList);
            if (!$isMasterEqual) {
                // 一致しない場合は更新行とする
                return 'update_row';
            }
            return 'normal_row';
        }

        /**
         * マスタ、再検索マスタのデータが一致するかどうか調べる.
         * 
         * @param string $taretKey データのキーとなるURL
         * @param array $targetColumnList 一致するか調べる項目リスト
         * @return boolean true:一致する、false:一致しない
         */
        protected function isMasterEqual($taretKey, $targetColumnList)
        {
            // マスタの対象データ取得
            $masterPath         = $this->temp_dir . '/' . self::MASTER;
            $arrMaster          = json_decode(file_get_contents($masterPath), true);
            $targetMasterData   = $arrMaster[$taretKey];
            // 再検索マスタの対象データ取得
            $reMasterPath       = $this->temp_dir . '/' . self::RE_MASTER;
            $arrReMaster        = json_decode(file_get_contents($reMasterPath), true);
            $targetReMasterData = $arrReMaster[$taretKey];

            // 対象項目において、値が一致するかどうか調べる
            foreach ($targetColumnList as $targetColumn) {
                if (!isset($targetMasterData[$targetColumn]) || !isset($targetReMasterData[$targetColumn])) {
                    continue;
                }
                if ($targetMasterData[$targetColumn] != $targetReMasterData[$targetColumn]) {
                    // 一致しない項目がひとつでもあればfalseを返す
                    return false;
                }
            }
            return true;
        }

        /**
         * 再検索マスタ更新.
         * 
         * @param string $taretKey データのキーとなるURL
         * @param array $updateData 更新データ
         * @param array $targetColumnList 更新対象項目リスト
         */
        protected function updateReMaster($taretKey, $updateData, $targetColumnList)
        {

            // 再検索マスタパス取得
            $reMasterPath = $this->temp_dir . '/' . self::RE_MASTER;
            if (!file_exists($reMasterPath)) {
                return;
            }
            // 再検索マスタデータ取得
            $arrReMaster = json_decode(file_get_contents($reMasterPath), true);
            if (!array_key_exists($taretKey, $arrReMaster)) {
                return;
            }
            $reMasterData = $arrReMaster[$taretKey];
            // 再検索マスタデータ上書き
            foreach ($targetColumnList as $targetColumn) {
                if (!isset($reMasterData[$targetColumn]) || !isset($updateData[$targetColumn])) {
                    continue;
                }
                if ($reMasterData[$targetColumn] != $updateData[$targetColumn]) {
                    $reMasterData[$targetColumn] = $updateData[$targetColumn];
                }
            }
            $arrReMaster[$taretKey] = $reMasterData;
            // 再検索マスタファイルへ反映
            file_put_contents($reMasterPath, json_encode($arrReMaster));
        }

    }

    endif;
?>
