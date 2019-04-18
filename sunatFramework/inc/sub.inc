<?php if (!defined('SUB_INC')) { define('SUB_INC', 1);
/*****************************************************************************
This is non public. 無断転載,無断使用を禁ず
Copyright (C) 2004. Sunatmark Co,.Ltd. All Right Reserved.

ファイル概要: 汎用サブルーチン

$Id:$
*****************************************************************************/

/*----------------------------------------------------------------------------
機能概要: ログインチェックおよびオートログイン実行
機能説明:
	ログイン状態を確認する。
	DBコネクションが渡されれば、DBにSESSION変数のログイン情報が
	正当か問い合わせを行う。省略されればSESSION変数の値のみチェックする。
	ログインSESSION情報がなく、オートログイン用のCOOKIEがある場合には
	オートログインを実行し、SESSIONにログイン情報を格納する。
引数:
	$db (I) DBコネクション
	$require_step (I)
	$set_step (I)
	$do_auto_login (I)
戻り値:
	TRUE:ログインOK  FALSE:未ログイン／ログイン失敗
----------------------------------------------------------------------------*/
function login_check($db=0, $require_step=1, $set_step=1, $do_auto_login = 1) {
	return UserLogin::login_check($db, $require_step, $set_step, $do_auto_login);
}


function array_key_check($names, $target_array) {
	ifthrow(!is_array($target_array), '$target_arrayが配列ではありません。');
	if (is_array($names)) {
		foreach ($names as $n) {
			if (!array_key_check($n, $target_array))
				return FALSE;
		}
		return TRUE;
	}
	else if (is_string($names)) {
		return array_key_exists($names, $target_array);
	}
}

/*----------------------------------------------------------------------------
 エラーメッセージ表示用コールバック関数
----------------------------------------------------------------------------*/
function err_disp($temp, $name, $data) {
	if (array_key_exists('section', $data) && $data['section']) {
		$section = $data['section'];
	} else {
		$section = '';
	}
	list($key1, $key2) = explode("+", $name);
	if (isset($data[$key1]) && isset($data[$key1][$key2]) && $data[$key1][$key2] == 1) {
		$temp->put(array(), $temp->get($section.$name));
	}
}

/*----------------------------------------------------------------------------
 ログイン情報表示用コールバック関数
----------------------------------------------------------------------------*/
function login_info($temp, $name, $data) {
	if ($data) $temp->put($data, $temp->get($name));
}

/*----------------------------------------------------------------------------
機能概要: テンプレート向け配列加工
機能説明:
	引数で渡された配列の内容をテンプレート向けに加工する。

引数:
	名前 (I/O) 説明
	$in     (I) 加工元配列
	$listes (I) 選択/複数選択項目データの配列

戻り値:
	加工後の配列
		フィールド名補足:
			d: または :display  → htmlspecialchars()をしてHTMLとして出力して安全な文字列
			t: または :text     → さらに、nl2br()をして改行を<BR>タグに置き換えた文字列
			v: または :value    → <INPUT>タグのvalue属性として使用できる文字列
			c: または :checked  → <INPUT>タグでtypeが checkbox radio の場合に checked属性として使用できる文字列
			s: または :selected → <OPTION>タグのselected属性として使用できる文字列
----------------------------------------------------------------------------*/
function in2values($in, $lists = array()) {
	$result = array();
	foreach ($in as $field => $value) {
		if (is_null($value) || is_scalar($value)) {
			if (preg_match('/^[dtvcs]:/', $field)
			||  preg_match('/:(?:(?:display)|(?:text)|(?:value)|(?:checked)|(?:selected)|(?:on)|(?:off))$/', $field))
				continue;
			if (is_null($value)) $value = '';
			if (array_key_exists($field, $lists)) {
				$list = $lists[$field];
				$result[$field] = array();

				if (is_array($list)) {
					foreach ($list as $key => $face) {
						$f = htmlspecialchars($face);
						$rec = array();
						$rec["c:$field"]        = $key == $value? ' checked':  '';
						$rec["$field:checked"]  = $key == $value? ' checked':  '';
						$rec["s:$field"]        = $key == $value? ' selected': '';
						$rec["$field:selected"] = $key == $value? ' selected': '';
						$rec["v:$field"]        = " value=\"$key\"";
						$rec["$field:value"]    = " value=\"$key\"";
						$rec["d:$field"]        = $f;
						$rec["$field:display"]  = $f;
						$rec["t:$field"]        = nl2br($f);
						$rec["$field:text"]     = nl2br($f);
						if ($key == $value) $rec["$key:on"]  = TRUE;
						else                $rec["$key:off"] = TRUE;
						array_push($result[$field], $rec);
					}
					if (array_key_exists($value, $list))
						$value = $list[$value];
				}
			}
			$v = htmlspecialchars($value);
			$result["d:$field"]        = $v;
			$result["$field:display"]  = $v;
			$result["t:$field"]        = nl2br($v);
			$result["$field:text"]     = nl2br($v);
			$result["v:$field"]        = $value? " value=\"$v\"": '';
			$result["$field:value"] = (is_null($value) || $value === '')? '': " value=\"$v\"";
			$result["c:$field"]        = $value? ' checked': '';
			$result["$field:checked"]  = $value? ' checked': '';
			$result["s:$field"]        = $value? ' selected': '';
			$result["$field:selected"] = $value? ' selected': '';
			if ($value) $result["$field:on"]  = TRUE;
			else        $result["$field:off"] = TRUE;
		}
		else if (is_array($value)) {
			if (array_key_exists($field, $lists)) {
				$list = $lists[$field];
				$result[$field] = array();
				if (is_array($list)) {
					foreach ($list as $key => $face) {
						$f = in_array($key, $value);
						$v = htmlspecialchars($face);
						$rec = array();
						$rec["c:$field"]        = $f? ' checked':  '';
						$rec["$field:checked"]  = $f? ' checked':  '';
						$rec["$field:off:checked"]  = $f? '': ' checked';
						$rec["s:$field"]        = $f? ' selected': '';
						$rec["$field:selected"] = $f? ' selected': '';
						$rec["v:$field"]        = " value=\"$key\"";
						$rec["$field:value"]    = " value=\"$key\"";
						$rec["d:$field"]        = $v;
						$rec["$field:display"]  = $v;
						$rec["t:$field"]        = nl2br($v);
						$rec["$field:text"]     = nl2br($v);
						if ($f) $rec["$key:on"]  = TRUE;
						else    $rec["$key:off"] = TRUE;
						array_push($result[$field], $rec);
					}
				}
			}
			else {
				$result[$field] = in2values($value, $lists);
			}
		}
	}
	return $result;
}


/*----------------------------------------------------------------------------
機能概要: URLのプロトコル部とホスト名を取得
機能説明:
	URLのプロトコル部とホスト部を返す。

引数:
	名前 (I/O) 説明
	$ssl  (I) SSL祖接続

戻り値:
	URLのプロトコル部とホスト部
----------------------------------------------------------------------------*/
function GetURLBase($ssl = FALSE) {
	return UserLogin::GetURLBase($ssl);
}


/*----------------------------------------------------------------------------
機能概要: リダイレクト
機能説明:
	指定されたURIにリダイレクトする
	リダイレクト後はスクリプトを終了する
引数:
	名前 (I/O) 説明
	$uri (I) リダイレクト先
	$ssl (I) SSLプロトコル指定(省略時:HTTP)
戻り値: なし
----------------------------------------------------------------------------*/
function redirect($uri, $ssl = NULL) {
	UserLogin::redirect($uri, $ssl);
}

/*----------------------------------------------------------------------------
機能概要: 暗号化パスワードの取得
機能説明:
	暗号化済みパスワードを取得する
引数:
	名前 (I/O) 説明
	$str (I) テキスト
戻り値: string 暗号化済みパスワード
----------------------------------------------------------------------------*/
function get_login_passwd($str) {
	return  crypt(md5($str));
}

/*----------------------------------------------------------------------------
機能概要: ページ繰りの作成
機能説明: ページ繰りの作成
引数:
	$total		(I)	元データの総件数
	$max_rows	(I)	１ページの最大件数
	$point		(I)	現在地
	$option		(I)	リンクの表示項目
	$max_pages	(I)	最大何件までリンクを表示するか
戻り値: ページ繰り
----------------------------------------------------------------------------*/
function getPager($total = 0, $max_rows, $point = 0, array $option = NULL, $max_pages = NULL) {

	//１ページの表示件数
	$pager = array();

	if ($total < $max_rows) return;

	//メイン部分の取得
	//リンク数
	$link_count = ceil($total / $max_rows);

	//総件数が１ページの最大行数より大きい時作成
	if ($total > $max_rows) {
		if (is_null($max_pages) || $link_count <= $max_pages) {
			$min = 1;
			$max = $link_count;
		}
		else {
			$prev_links = floor(($max_pages - 1) / 2);
			if ($point < $prev_links) {
				$min = 1;
				$max = $max_pages;
			}
			elseif (($point + $max_pages - $prev_links - 1) > $link_count) {
				$min = $link_count - $max_pages + 1;
				$max = $link_count;
			}
			else {
				$min = $point - $prev_links;
				$max = $point + $max_pages - $prev_links - 1;
			}
		}
		for ($i = $min; $i <= $max; $i++) {
			if (($point == 0 && $i == 1) || $i == $point) $link = 0;
			else  $link = $i;
			$pager['pager'][] = array('link' => $link, 'text' => $i);
		}
		if (is_array($option)) {
			//開始
			if (in_array('start', $option)) {
				$start_arr = array();
				if ($point > 1) $start_arr = array('start' => 1);
				$pager += $start_arr;
			}

			//前へ
			if (in_array('prev', $option)) {
				$prev_arr = array();
				if ($point > 0) $prev_arr = array('prev' => $point - 1);
				$pager += $prev_arr;
			}

			//100前へ
			if (in_array('prev100', $option)) {
				$prev100_arr = array();
				if ($point > 100) $prev100_arr = array('prev100' => $point - 100);
				$pager += $prev100_arr;
			}

			//1000前へ
			if (in_array('prev1000', $option)) {
				$prev1000_arr = array();
				if ($point > 1000) $prev1000_arr = array('prev1000' => $point - 1000);
				$pager += $prev1000_arr;
			}

			//1000後ろへ
			if (in_array('next1000', $option)) {
				$next1000_arr = array();
				if (($link_count - $point) > 1000) $next1000_arr = array('next1000' => $point + 1000);
				$pager += $next1000_arr;
			}

			//100後ろへ
			if (in_array('next100', $option)) {
				$next100_arr = array();
				if (($link_count - $point) > 100) $next100_arr = array('next100' => $point + 100);
				$pager += $next100_arr;
			}

			//次へ
			if (in_array('next', $option)) {
				$next_arr = array();
				if ($point == 0) $next_arr = array('next' => 2);
				else if ($point != $link_count && $point < $link_count) $next_arr = array('next' => $point + 1);
				$pager += $next_arr;
			}

			//最後
			if (in_array('end', $option)) {
				$end_arr = array();
				if ($point != $link_count && $point < $link_count) $end_arr = array('end' => $link_count);
				$pager += $end_arr;
			}

			//プルダウン
			if (in_array('pull', $option)) {
				foreach($pager['pager'] as $key => $val) {
					if ($point == $val['text']) $val['link'] = $point;
					$pager2['pager'][] = $val;
				}
				$pager = $pager2;
			}
		}
	}

	return $pager;
}

/*----------------------------------------------------------------------------
機能概要: ログインパスワードチェック
機能説明:
	入力されたパスワードと登録されているパスワードとの整合チェックを行なう
引数:
	名前 (I/O) 説明
	$db_passwd (I) DBに登録されているパスワード
	$passwd (I) 入力パスワード
戻り値: boolean 結果
----------------------------------------------------------------------------*/
function login_passwd_check($db_passwd, $passwd) {
	return ($db_passwd == crypt(md5($passwd), $db_passwd));
}

/*----------------------------------------------------------------------------
機能概要: ログインのログ記録
機能説明:

引数:
	名前 (I/O) 説明
	$db (I) MySQL操作オブジェクト
	$user (I) ユーザID
	$session_limit (I) 同時接続許可数
	$client_type (I) クライアントタイプ指定
戻り値: integer login_log.id
----------------------------------------------------------------------------*/
function loggingLogin(mysql_db $db, $user, $session_limit, $client_type = null) {
	$client_type = 1;	//TSⅡは全てPCとして扱う
	$service = 2;		//サービス(TSⅡ)
	
	$remote_addr = $_SERVER['REMOTE_ADDR'];	// リモートアドレス
	$expire = $db->get('value', 'system_info', 'name = ?', array('login_expire'));	// ログイン有効期限

	$db->begin();
	$table = 'login_log';
	$ret = 0;
	// ユーザー、サービスが同じで、未ログアウトの最終アクセス日時がログイン有効期限内であるログインログ件数を取得
	$arrWhere = array($user, $service);
	$where = "user = ? AND service = ? AND logout_datetime = '0000-00-00 00:00:00' AND CURRENT_TIMESTAMP < DATE_ADD(last_access, $expire)";
	// 同時接続数が上限に達していればログイン不可とする
	if ($db->count($table, $where, $arrWhere) >= $session_limit) return $ret;
	
	//常にログインログを生成
	$arrValues = array(
		'user'			=> $user,
		'client_type'	=> $client_type,
		'service'		=> $service,
		'remote_addr'	=> $remote_addr,
		'last_access'	=> 'CURRENT_TIMESTAMP'
	);
	$db->insert($table, $arrValues);
	$ret = $db->max('id', $table);
	$db->commit();
	return $ret;
}

/*----------------------------------------------------------------------------
機能概要: 利用端末判別
機能説明: アクセスされた利用端末をUAから判別する

引数: void
戻り値: integer 利用端末
----------------------------------------------------------------------------*/
function getClientType() {
	$ua_list = array (
		'iPad',
		'PlayBook',
		'iPod',
		'iPhone',
		'Android',
		'BlackBerry',
		'webOS',
		'Windows Phone OS'
	);
	if (!array_key_exists('HTTP_USER_AGENT', $_SERVER)) $_SERVER['HTTP_USER_AGENT'] = null;

	$client_type = CLIENT_TYPE_PC;	// 利用端末をPCで初期化
	$is_Mobile = preg_match('/Mobile/i', $_SERVER['HTTP_USER_AGENT']);

	foreach ($ua_list as $ua) {
		$ret = preg_match('/' . $ua . '/i', $_SERVER['HTTP_USER_AGENT']);
		if ($ret != 0) {
			switch ($ua) {
				case 'iPad':
				case 'PlayBook':
					// タブレット(PCと判別)
					break;
				case 'Android':
					// Androidの場合はUA内にMobileがあればスマホ、なければタブレット(PCと判別)
					if ($is_Mobile != 0) {
						$client_type = CLIENT_TYPE_SP;
					}
					break;
				default:
					// スマホ
					$client_type = CLIENT_TYPE_SP;
					break;
			}
			break;
		}
	}
	return $client_type;
}

/*----------------------------------------------------------------------------
機能概要: DB用日付文字列取得
機能説明: 年・月・日からDB用日付文字列(Timestamp)を取得する

引数:
	名前 (I/O) 説明
	$year (I) 年
	$month (I) 月
	$day (I) 日
	$last (I) 付加する時刻を最終とするか
戻り値: string DB用日付文字列
----------------------------------------------------------------------------*/
function getTimestamp($year, $month, $day, $last = false) {
	if ($year != '' && $month != '' && $day != '') {
		if ($last) {
			$time = '23:59:59';
		} else {
			$time = '00:00:00';
		}
		$date = $year.'-'.$month.'-'.$day.' '.$time;
	} else {
		$date = '';
	}
	return $date;
}

/*----------------------------------------------------------------------------
機能概要: 検索ログ記録
機能説明:

引数:
	名前 (I/O) 説明
	$keyword (I) 検索文字列
戻り値: void
----------------------------------------------------------------------------*/
function loggingSearch($keyword) {

	if (is_null($keyword)) return;

	// 前後のスペースを削除
	// カタカナは全角
	// 英数字およびスペースは半角
	$keyword = trim($keyword);
	$keyword = mb_convert_kana($keyword, 'KVsa');
	// 整形の結果、空文字ならロギングは行わない
	if ($keyword == '') return;

	$db = new mysql_db();

	$table = 'search_keyword';
	$arrValues = array(
		'search_keyword' => $keyword
	);
	$db->begin();
	$db->insert($table, $arrValues);
	$search_id = $db->max('id', $table);

	$table = 'search_log';
	$arrValues = array(
		'login_log' => $_SESSION['login_info']['login_id'],
		'search_keyword' => $search_id,
		'search_datetime' => 'CURRENT_TIMESTAMP'
	);
	$db->insert($table, $arrValues);

	$db->commit();
}

/*----------------------------------------------------------------------------
機能概要: 検索時の同時アクセス数チェック
機能説明:
	検索時の同時アクセス数チェックを行う。
	同時アクセス数を満たしていない場合ログインログの最終アクセス日時を更新する。
引数:
	名前 (I/O) 説明
	$db (I) MySQL操作オブジェクト
戻り値: なし
----------------------------------------------------------------------------*/
function session_limit_check(mysql_db $db = null) {
	if (is_null($db)) $db = new mysql_db();
	
	$service = 2;		//サービス(TSⅡ)
	
	$login_info = $_SESSION['login_info'];
	$login_id = $login_info['login_id'];
	$email = $login_info['email'];
	$user_id = $login_info['id'];
	$session_limit = $login_info['session_limit'];
	
	//期限切れの場合は強制ログアウト
	if (!$db->count('user', 'id = ? AND CURRENT_TIMESTAMP < expire_datetime', array($user_id))) {
		$db->update('login_log', array('logout_datetime' => 'CURRENT_TIMESTAMP'), 'id = ?', array($login_id));
		unset($_SESSION['login_info']);
		$_SESSION['login_err'] = array('login_id' => $email, 'message_id' => 4);
		redirect('/login.php');
		exit;
	}
	
	$expire = $db->get('value', 'system_info', 'name = ?', array('login_expire'));	// ログイン有効期限

	// ログインID、最終アクセス日時がログイン有効期限内かを取得
	$arrWhere = array($login_id);
	$where = "id = ? AND CURRENT_TIMESTAMP < DATE_ADD(last_access, $expire)";
	$table = 'login_log';
	
	if (!$db->count($table, $where, $arrWhere)) {
		// ログインIDが違い、ユーザー、サービスが同じで、最終アクセス日時がログイン有効期限内であるログインログ数を取得
		$arrWhere = array($login_id, $user_id, $service);
		$where = "id != ? AND user = ? AND service = ? AND logout_datetime = '0000-00-00 00:00:00' AND CURRENT_TIMESTAMP < DATE_ADD(last_access, $expire)";
		if ($db->count($table, $where, $arrWhere) >= $session_limit) {
			unset($_SESSION['login_info']);
			//session_destroy();
			$_SESSION['login_err'] = array('login_id' => $email, 'message_id' => 5);
			redirect('/login.php');
			exit;
		}
		else {
			//NOP
		}
	}
	else {
		//NOP
	}
	
	list($login_datetime) = $db->getCol('login_datetime', $table, 'id = ?', array($login_id));
	$arrValues = array(
		'login_datetime' => $login_datetime,
		'last_access' => 'CURRENT_TIMESTAMP'
	);

	$db->update($table, $arrValues, 'id = ?', array($login_id));
}



/*----------------------------------------------------------------------------
機能概要: User-Agentをランダムで取得
機能説明:
	Indeed検索用にUser-Agentをランダムで取得する
引数:
	名前 (I/O) 説明
	なし
戻り値: string User-Agent
----------------------------------------------------------------------------*/
function get_random_user_agent() {
	$arrUserAgent = array(
		'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0; SLCC1; .NET CLR 2.0.50727; Media Center PC 5.0; .NET CLR 3.0.04506)', 
		'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; .NET CLR 2.0.50727; .NET CLR 3.0.04506.30; .NET CLR 3.0.04506.648)', 
		'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; .NET CLR 2.0.50727; .NET CLR 3.0.04506.30; .NET CLR 3.0.04506.648)', 
		'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; .NET CLR 2.0.50727; .NET CLR 3.0.04506.30; .NET CLR 3.0.04506.648)', 
		'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0)', 
		'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0)', 
		'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0)', 
		'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0)', 
		'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:19.0) Gecko/20100101 Firefox/19.0', 
		'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.22 (KHTML, like Gecko) Chrome/25.0.1364.160 Safari/537.22', 
		'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.22 (KHTML, like Gecko) Chrome/25.0.1364.160 Safari/537.22', 
		'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.22 (KHTML, like Gecko) Chrome/25.0.1364.160 Safari/537.22', 
		'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.22 (KHTML, like Gecko) Chrome/25.0.1364.160 Safari/537.22', 
		'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_3; ja-jp) AppleWebKit/531.21.11 (KHTML, like Gecko) Version/4.0.4 Safari/531.21.10', 
		'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_3; ja-jp) AppleWebKit/533.16 (KHTML, like Gecko) Version/5.0 Safari/533.16'
	);
	
	return $arrUserAgent[array_rand($arrUserAgent)];

}

function getIdList($arr_list) {
	$list = array();

	foreach($arr_list as $key => $value) {
		$list[] = $value['id'];
	}

	return $list;
}

function getIdFaceList($arr_list) {
	$list = array();

	foreach($arr_list as $key => $value) {
		$list[$value['id']] = $value['face'];
	}

	return $list;
}


}?>
