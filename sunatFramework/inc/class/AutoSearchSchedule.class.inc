<?php

/**
 * 自動再検索スケジュール.
 */
class AutoSearchSchedule
{

    protected $db;

    /**
     * コンストラクタ.
     *
     * @param mysql_db $db DBオブジェクト
     */
    public function __construct(mysql_db $db)
    {
        $this->db = $db;
    }

    /**
     * スケジュール区分リスト取得.
     *
     * @return array 区分リスト
     */
    public static function getDivList($date = null)
    {
        $date = $date == "" ? null : $date;
        $createDivInfo = function($id, $title, $isEnabled = true) {
            return array('id' => $id, 'title' => $title, 'is_enabled' => $isEnabled);
        };

        // 日付が指定されていない場合
        if (is_null($date)) {
            $resultList                               = array();
            $resultList[AUTO_SEARCH_DIV_ONE_DAY]      = $createDivInfo(AUTO_SEARCH_DIV_ONE_DAY, '繰り返しなし', false);
            $resultList[AUTO_SEARCH_DIV_MONTHLY_DAY]  = $createDivInfo(AUTO_SEARCH_DIV_MONTHLY_DAY, '毎月 指定日（繰り返す）', false);
            $resultList[AUTO_SEARCH_DIV_MONTHLY_WEEK] = $createDivInfo(AUTO_SEARCH_DIV_MONTHLY_WEEK, '毎月 指定曜日（繰り返す）', false);
            $resultList[AUTO_SEARCH_DIV_WEEKLY]       = $createDivInfo(AUTO_SEARCH_DIV_WEEKLY, '毎週（繰り返す）', false);
            $resultList[AUTO_SEARCH_DIV_BIWEEKLY]     = $createDivInfo(AUTO_SEARCH_DIV_BIWEEKLY, '隔週（繰り返す）', false);
            return $resultList;
        }

        // 日付が指定されている場合
        $day     = date('d', strtotime($date));
        $weekday = date('w', strtotime($date));
        list($monthWeekNum, $monthWeekDay) = self::getWeekNoFromDate($date);

        $weekList   = array('日曜', '月曜', '火曜', '水曜', '木曜', '金曜', '土曜');
        $resultList = array();

        $resultList[AUTO_SEARCH_DIV_ONE_DAY]      = $createDivInfo(
                AUTO_SEARCH_DIV_ONE_DAY, '繰り返しなし');
        $resultList[AUTO_SEARCH_DIV_MONTHLY_DAY]  = $createDivInfo(
                AUTO_SEARCH_DIV_MONTHLY_DAY, (sprintf("毎月 %d日（繰り返す）", $day)));
        $resultList[AUTO_SEARCH_DIV_MONTHLY_WEEK] = $createDivInfo(
                AUTO_SEARCH_DIV_MONTHLY_WEEK, (sprintf("毎月 第%d%s日（繰り返す）", $monthWeekNum, $weekList[$monthWeekDay])), $monthWeekNum <= 4);
        $resultList[AUTO_SEARCH_DIV_WEEKLY]       = $createDivInfo(
                AUTO_SEARCH_DIV_WEEKLY, sprintf("毎週 %s日（繰り返す）", $weekList[$weekday]));
        $resultList[AUTO_SEARCH_DIV_BIWEEKLY]     = $createDivInfo(
                AUTO_SEARCH_DIV_BIWEEKLY, sprintf("隔週 %s日（繰り返す）", $weekList[$weekday]));
        return $resultList;
    }

    /**
     * 日付の曜日から月の第n〇曜日を取得する.
     *
     * @param string $date Y-m-d
     * @return array (第n, 曜日)
     */
    public static function getWeekNoFromDate($date)
    {
        $targetYear    = date('Y', strtotime($date));
        $targetMonth   = date('m', strtotime($date));
        $targetDay     = date('d', strtotime($date));
        $targetWeekday = date('w', strtotime($date));

        // 指定年月の始まり曜日を取得
        $weekdayOfFirst = (int) date('w', mktime(0, 0, 0, $targetMonth, 1, $targetYear));

        // 指定日が第何週か取得
        $weekNoDay = $targetDay + $weekdayOfFirst;
        $weekNo    = ceil($weekNoDay / 7);
        if ($targetWeekday < $weekdayOfFirst) {
            $weekNo--;
        }
        return array($weekNo, $targetWeekday);
    }

    /**
     * 月の第n〇曜日の日を取得する.
     *
     * @param int $year 年
     * @param int $month 月
     * @param int $weekNo 第n
     * @param int $weekday 曜日
     * @return int 日
     */
    public static function getDayFromMonthWeekNo($year, $month, $weekNo,
                                                 $weekday)
    {
        $weekdayOfFirst = (int) date('w', mktime(0, 0, 0, $month, 1, $year));
        if ($weekday < $weekdayOfFirst) {
            $weekNo++;
        }
        return ($weekNo - 1) * 7 + $weekday + 1 - $weekdayOfFirst;
    }

    /**
     * 自動再検索スケジュール取得.
     *
     * @param int $id ID
     * @return array 自動再検索スケジュール
     */
    public function getRow($id)
    {
        return $this->db->getRow('*', 'auto_search_schedule', 'id = ?', array($id));
    }

    /**
     * 自動再検索スケジュールに変更があるかどうか調べる.
     *
     * @param int $id ID
     * @param string $baseDatetime 基準日時
     * @param int $scheduleDiv スケジュール区分
     * @return boolean true:変更あり, false 変更無し
     */
    public function isChange($id, $baseDatetime, $scheduleDiv)
    {
        $data = $this->getRow($id);
        if (strtotime($data['base_datetime']) == strtotime($baseDatetime) &&
                $data['schedule_div'] == $scheduleDiv) {
            return false;
        }
        return true;
    }

    /**
     * 対象ユーザーの自動再検索スケジュールの登録件数を取得する.
     *
     * @param int $user_id ユーザーID
     * @param int $core_book 検索対象
     * @param int $exclude_team_id 除外検索条件ID
     * @return 自動再検索スケジュールの登録件数
     */
    public function getUserScheduleCount($user_id, $core_book,
                                         $exclude_team_id = null)
    {
        $sql = <<<_SQL_
                SELECT
                    COUNT(*) AS count
                FROM
                    auto_search_schedule ASS
                        INNER JOIN favorite_search_terms FST
                            ON ASS.favorite_search_terms = FST.id
                WHERE
                    ASS.del_flg            = 0
                    AND FST.user           = :user_id
                    AND FST.core_book      = :core_book
_SQL_;

        $arrVal = array(
            'user_id'   => $user_id,
            'core_book' => $core_book
        );

        if (!is_null($exclude_team_id)) {
            $sql                       .= " AND FST.id <> :exclude_team_id ";
            $arrVal['exclude_team_id'] = $exclude_team_id;
        }

        $result = $this->db->getOne($sql, $arrVal);
        return $result;
    }

    /**
     * 引数の自動再検索スケジュールリストに、スケジュール日時の選択項目設定を追加して返す.
     *
     * @param array $autoSearchScheduleList 自動再検索スケジュールリスト
     * @return array 自動再検索スケジュールリスト
     */
    public function addAutoSearchScheduleSelectList($autoSearchScheduleList)
    {
        $list = array();
        foreach ($autoSearchScheduleList as $key => $autoSchedule) {
            $divList = self::getDivList($autoSchedule['base_datetime']);
//            $schedule_div = $autoSchedule['schedule_div'];

            $autoSchedule['divList'] = $divList;
//            $autoSchedule['schedule_div_name'] = $divList[$schedule_div]['title'];
//            $autoSchedule['schedule_string']   = sprintf("%s 実行&nbsp;&nbsp;%s",
//                    date('Y/m/d H:i', strtotime($autoSchedule['execute_datetime'])),
//                    $autoSchedule['schedule_div_name']);

            $list[] = $autoSchedule;
        }
        return $list;
    }

    /**
     * 自動実行スケジュール検索.
     *
     * @param type $terms_id
     * @return type
     */
    public function selectAutoSearchSchedule($terms_id)
    {
        $sql    = <<<_SQL_
                SELECT
                    *,
                    id AS schedule_id
                FROM
                    auto_search_schedule
                WHERE
                    del_flg = 0
                    AND favorite_search_terms = :favorite_search_terms
                ORDER BY
                    id
_SQL_;
        $arrVal = array(
            'favorite_search_terms' => $terms_id
        );

        $result = $this->db->getAll($sql, $arrVal);
        return $result;
    }

    /**
     * 自動再検索スケジュール登録.
     *
     * @param int $terms_id 検索条件ID
     * @param array $autoSearchSchedule 自動再検索スケジュール
     * @return type
     */
    public function insertAutoSearchSchedule($terms_id, $autoSearchSchedule)
    {
        $date   = date('Y-m-d H:i:s');
        $arrVal = array(
            'favorite_search_terms' => $terms_id,
            'base_datetime'         => $autoSearchSchedule['base_datetime'],
            'schedule_div'          => $autoSearchSchedule['schedule_div'],
            'execute_datetime'      => $autoSearchSchedule['base_datetime'],
            'create_datetime'       => $date,
            'update_datetime'       => $date
        );

        return $this->db->insert('auto_search_schedule', $arrVal);
    }

    /**
     * 自動再検索スケジュール更新.
     *
     * @param int $terms_id 検索条件ID
     * @param array $autoSearchSchedule 自動再検索スケジュール
     * @return type
     */
    public function updateAutoSearchSchedule($terms_id, $autoSearchSchedule)
    {
        $arrVal = array(
            'favorite_search_terms' => $terms_id,
            'base_datetime'         => $autoSearchSchedule['base_datetime'],
            'schedule_div'          => $autoSearchSchedule['schedule_div'],
            'execute_datetime'      => $autoSearchSchedule['base_datetime'],
            'update_datetime'       => date('Y-m-d H:i:s')
        );

        return $this->db->update('auto_search_schedule', $arrVal, 'id = ?', array($autoSearchSchedule['schedule_id']));
    }

    /**
     * 自動再検索スケジュール削除.
     *
     * @param int $schedule_id 自動再検索スケジュール
     */
    public function deleteAutoSearchSchedule($schedule_id)
    {
        //return $this->db->delete('auto_search_schedule', 'id = ?', array($schedule_id));
        $arrVal = array(
            'update_datetime'       => date('Y-m-d H:i:s'),
            'del_flg'               => DEL_FLG_ON
        );
        return $this->db->update('auto_search_schedule', $arrVal, 'id = ?', array($schedule_id));
    }

    /**
     * 自動再検索スケジュール削除.
     *
     * @param int $teamsId 保存リストID
     */
    public function deleteAutoSearchScheduleFromTeamsId($teamsId)
    {
//        return $this->db->delete('auto_search_schedule', 'favorite_search_terms = ?', array($teamsId));
        $arrVal = array(
            'update_datetime'       => date('Y-m-d H:i:s'),
            'del_flg'               => DEL_FLG_ON
        );
        return $this->db->update('auto_search_schedule', $arrVal, 'favorite_search_terms = ?', array($teamsId));
    }

    /**
     * 実行日時を次回に更新する.
     *
     * @param array $autoSearchSchedule 自動再検索スケジュール情報
     */
    public function updateExecuteDatetimeNext($autoSearchSchedule)
    {
        // 次回実行日時取得
        $nextExecuteDatetime = $this->getNextExecuteDatetime(
                $autoSearchSchedule['schedule_div'], $autoSearchSchedule['base_datetime'], $autoSearchSchedule['execute_datetime']);

        // 次回実行日日時が取得できなかった場合は何もしない
        if (is_null($nextExecuteDatetime)) {
            return;
        }

        $sql = "UPDATE auto_search_schedule SET "
                . "    execute_datetime = :execute_datetime, "
                . "    update_datetime  = NOW() "
                . "WHERE "
                . "    id = :id ";

        $this->db->exec($sql, array(
            'execute_datetime' => $nextExecuteDatetime,
            'id'               => $autoSearchSchedule['id']
        ));
    }

    /**
     * 自動再検索スケジュールの次回実行日を取得する.
     *
     * @param int $scheduleDiv スケジュール区分
     * @param string $baseDatetime 基準日 Y-m-d H:i
     * @param string $executeDatetime 実行日 Y-m-d H:i
     * @return string 次回実行日
     */
    public function getNextExecuteDatetime($scheduleDiv, $baseDatetime,
                                           $executeDatetime)
    {
        if (is_null($baseDatetime) || is_null($executeDatetime)) {
            return null;
        }

        // 基準日 日、時間
        $baseDay      = date("d", strtotime($baseDatetime));
        $baseTime     = date("H:i", strtotime($baseDatetime));
        // 実行日
        $executeYear  = date("Y", strtotime($executeDatetime));
        $executeMonth = date("m", strtotime($executeDatetime));
        $executeDay   = date("d", strtotime($executeDatetime));
        $executeTime  = date("H:i", strtotime($executeDatetime));

        switch ($scheduleDiv) {
            case AUTO_SEARCH_DIV_MONTHLY_DAY:
                // ------------------------------------------------
                // 毎月　指定日（繰り返す）
                // ------------------------------------------------
                // 次月を取得
                $nextYear    = date('Y', mktime(0, 0, 0, $executeMonth + 1, 1, $executeYear));
                $nextMonth   = date('m', mktime(0, 0, 0, $executeMonth + 1, 1, $executeYear));
                // 次月の最終日を取得
                $nextYm      = sprintf("%s-%02d-1", $nextYear, $nextMonth);
                $nextLastDay = date('t', strtotime($nextYm));
                // 次月の最終日を超える場合は最終日とする
                $nextDay     = $baseDay > $nextLastDay ? $nextLastDay : $baseDay;
                return sprintf("%s-%02d-%02d %s", $nextYear, $nextMonth, $nextDay, $baseTime);

            case AUTO_SEARCH_DIV_MONTHLY_WEEK:
                // ------------------------------------------------
                // 毎月　指定曜日（繰り返す）
                // ------------------------------------------------
                // 第n〇曜日か取得
                list($weekNo, $weekday) = self::getWeekNoFromDate($baseDatetime);
                // 次月を取得
                $nextYear  = date('Y', mktime(0, 0, 0, $executeMonth + 1, 1, $executeYear));
                $nextMonth = date('m', mktime(0, 0, 0, $executeMonth + 1, 1, $executeYear));
                // 日を取得
                $nextDay   = self::getDayFromMonthWeekNo($nextYear, $nextMonth, $weekNo, $weekday);
                return sprintf("%d-%02d-%02d %s", $nextYear, $nextMonth, $nextDay, $baseTime);

            case AUTO_SEARCH_DIV_WEEKLY:
                // ------------------------------------------------
                // 毎週　（繰り返す）
                // ------------------------------------------------
                $date = date('Y-m-d', mktime(0, 0, 0, $executeMonth, $executeDay + 7, $executeYear));
                return $date . " " . $executeTime;

            case AUTO_SEARCH_DIV_BIWEEKLY:
                // ------------------------------------------------
                // 隔週　（繰り返す）
                // ------------------------------------------------
                $date = date('Y-m-d', mktime(0, 0, 0, $executeMonth, $executeDay + 14, $executeYear));
                return $date . " " . $executeTime;
            default:
                return null;
        }
    }

    /**
     * ユーザーIDから自動再検索スケジュールを取得する.
     *
     * @param int $userId ユーザーID
     */
    public function getAutoSearchScheduleListFromUserId($userId)
    {
        $sql    = <<<_SQL_
                SELECT
                    ASS.*
                FROM
                    auto_search_schedule ASS
                        INNER JOIN favorite_search_terms FST
                            ON ASS.favorite_search_terms = FST.id
                WHERE
                    ASS.del_flg = 0
                    AND FST.user    = :user
                ORDER BY
                    ASS.favorite_search_terms,
                    ASS.id
_SQL_;
        $arrVal = array(
            'user' => $userId
        );
        return $this->db->getAll($sql, $arrVal);
    }

    /**
     * 自動再検索スケジュールが設定されている保存リスト一覧を取得する.
     *
     * @param int $userId ユーザーID
     * @param int $coreBook 検索ターゲット
     * @return array 保存リスト一覧
     */
    public function getReserveTermsList($userId, $coreBook)
    {
        $searchTermsTree   = $this->getSearchTermsTree($userId, $coreBook);
        $scheduleTermsList = $this->getScheduleTermsList($userId, $coreBook);

        $list = array();
        foreach ($searchTermsTree as $treeKey => $treeData) {
            if ($treeData == '') {
                continue;
            }
            // ツリー情報からリスト情報を取得する
            $matchResult = preg_match('/^(.+?)(<span[^>]+?>(\d*)<\/span>)?$/iu', $treeData, $m);
            if ($matchResult != 1 || !array_key_exists('3', $m)) {
                continue;
            }
            $termsId = $m[3];
            if (!array_key_exists($termsId, $scheduleTermsList)) {
                continue;
            }
            $list[$termsId] = $scheduleTermsList[$termsId];
            unset($scheduleTermsList[$termsId]);
        }
        return $list;
    }

    /**
     * リストツリー情報取得.
     *
     * @param int $userId ユーザーID
     * @param int $coreBook 検索対象
     * @return array リストツリー情報
     */
    protected function getSearchTermsTree($userId, $coreBook)
    {
        $searchCol       = $coreBook == 1 ? 'search_terms_tree' : 'book_search_terms_tree';
        $searchTermsTree = $this->db->get($searchCol, 'user', 'id = ?', array($userId));
        if (!$searchTermsTree) {
            return array();
        }
        $nodeList   = unserialize($searchTermsTree);
        $resultList = array();
        foreach ($nodeList as $key => $node) {
            $resultList[$key] = trim($node);
        }
        return $resultList;
    }

    /**
     * 検索条件情報リスト取得.
     *
     * @param int $userId ユーザーID
     * @param int $coreBook 検索対象
     * @return array 検索条件情報リスト
     */
    protected function getFavoriteSearchTermsList($userId, $coreBook)
    {

        $col                     = <<< EOF
                favorite_search_terms.id,
                favorite_search_terms.subject,
                favorite_search_terms.purpose,
                favorite_search_terms.creator,
                CASE WHEN create_datetime = '0000-00-00 00:00:00' THEN '' ELSE create_datetime END AS create_datetime,
                CASE WHEN execute_datetime = '0000-00-00 00:00:00' THEN '' ELSE execute_datetime END AS execute_datetime,
                (SELECT pic FROM user_member WHERE user_member.id = favorite_search_terms.create_user_member) AS user_name,
                favorite_search_terms.total_count,
                favorite_search_terms.qid,
                favorite_search_terms.list_terms
EOF;
        $from                    = 'favorite_search_terms';
        $favoriteSearchTeamsList = $this->db->select($col, $from, 'user = ? AND core_book = ?', array($userId, $coreBook));
        $resultList              = array();
        foreach ($favoriteSearchTeamsList as $favoriteSearchTeams) {
            $id              = $favoriteSearchTeams['id'];
            $resultList[$id] = $favoriteSearchTeams;
        }
        return $resultList;
    }

    /**
     * 自動再検索スケジュールを含む検索条件情報リスト取得.
     *
     * @param int $userId ユーザーID
     * @param int $coreBook 検索対象
     * @return array 検索条件情報リスト
     */
    protected function getScheduleTermsList($userId, $coreBook)
    {
        $favoriteSearchTermsList = $this->getFavoriteSearchTermsList($userId, $coreBook);
        $autoSearchScheduleObj   = new AutoSearchSchedule($this->db);
        $autoSearchScheduleList  = $autoSearchScheduleObj->getAutoSearchScheduleListFromUserId($userId);
        $scheduleDataList        = $autoSearchScheduleObj->addAutoSearchScheduleSelectList($autoSearchScheduleList);

        $resultList = array();
        foreach ($favoriteSearchTermsList as $key => $favoriteSearchTerms) {
            $favoriteSearchTerms['schedule_list']        = array();
            $favoriteSearchTerms['schedule_string_list'] = array();
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

            $resultList[$key] = $favoriteSearchTerms;
        }
        return $resultList;
    }

    /**
     * 自動再検索対象キューを取得する.
     *
     * @return type
     */
    public function getExecuteAutoSearchScheduleList()
    {

        $sql = <<< _SQL_
                SELECT
                    MIN(ASS1.id) AS id,
                    ASS1.favorite_search_terms,
                    ASS1.execute_datetime,
                    FST.user,
                    FST.qid,
                    FST.core_book
                FROM
                    auto_search_schedule ASS1
                        INNER JOIN favorite_search_terms FST
                            ON ASS1.favorite_search_terms = FST.id
                        INNER JOIN (
                                SELECT
                                    favorite_search_terms,
                                    MIN(execute_datetime) AS execute_datetime
                                FROM
                                    auto_search_schedule
                                WHERE
                                    del_flg = 0
                                    AND execute_datetime < NOW()
                                GROUP BY
                                    favorite_search_terms
                            ) ASS2
                                ON ASS1.favorite_search_terms = ASS2.favorite_search_terms
                                    AND ASS1.execute_datetime = ASS2.execute_datetime
                       INNER JOIN user U
                            ON FST.user = U.id
                WHERE
                    ASS1.del_flg = 0
                    AND U.status <> 0
                    AND U.start_datetime < NOW()
                    AND (U.expire_datetime IS NULL OR U.expire_datetime > NOW())
                GROUP BY
                    ASS1.favorite_search_terms,
                    ASS1.execute_datetime
                ORDER BY
                    ASS1.execute_datetime
_SQL_;

        $targetScheduleList = $this->db->getAll($sql);
        return $targetScheduleList;
    }

    /**
     * 自動再検索スケジュール実行後の更新を行う.
     *
     * @param int $autoSearchScheduleId 自動再検索スケジュールID
     */
    public function updateScheduleExecuteAfter($autoSearchScheduleId)
    {
        $autoSearchSchedule = $this->getRow($autoSearchScheduleId);
        switch ($autoSearchSchedule['schedule_div']) {
            // --------------------------------------
            // 日時指定（繰り返しなし）
            // --------------------------------------
            case AUTO_SEARCH_DIV_ONE_DAY :
                // 自動再検索スケジュール削除
                $this->deleteAutoSearchSchedule($autoSearchScheduleId);
                break;
            // --------------------------------------
            // 毎月　指定日（繰り返す）
            // 毎月　指定曜日（繰り返す）
            // 毎週　（繰り返す）
            // 隔週　（繰り返す）
            // --------------------------------------
            case AUTO_SEARCH_DIV_MONTHLY_DAY:
            case AUTO_SEARCH_DIV_MONTHLY_WEEK:
            case AUTO_SEARCH_DIV_WEEKLY:
            case AUTO_SEARCH_DIV_BIWEEKLY:
                // 実行日時を次回分に更新
                $this->updateExecuteDatetimeNext($autoSearchSchedule);
                break;
        }
    }

}
