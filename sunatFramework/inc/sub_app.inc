<?php if (!defined('SUB_APP_INC')): define('SUB_APP_INC', 1);
/*****************************************************************************
This is non public. 無断転載,無断使用を禁ず
Copyright (C) 2011. Sunatmark Co,.Ltd. All Right Reserved.

ファイル概要: アプリケーション共通関数

$Id:$
*****************************************************************************/

/*----------------------------------------------------------------------------
機能概要: 
機能説明: 
引数:
戻り値: void
----------------------------------------------------------------------------*/
function request_indeed($arrResult, $backend_server, $arrCond, $user_id, $contract_type, $user_agent, $qid, $idx_from = 0) {
	$env = new Env;
	$ppt_url = $env->config->ppt_url;
	$arrAlert = array(); // 正規表現によるマッチに失敗した可能性など・アラート項目

	$total_count	= 0;
	$loaded_count	= 0;
    $db = new mysql_db();
    $max_result = $db->get('book_search_max', 'user', 'id = ?', array($user_id));

	/*
	$max_result = MAX_RESULT;
    if (array_key_exists($qid, $_SESSION['q']) && array_key_exists('max_result', $_SESSION['q'][$qid])) {
    	$max_result = $_SESSION['q'][$qid]['max_result'];
	}
*/
	$fax_search = 'ハローワーク';
	$email_search = 'email e-mail eメール';
	
	if ($arrCond['is_option']) {
		// オプション検索
		$query = 'http://jp.indeed.com/%E6%B1%82%E4%BA%BA?psf=advsrch';
		$opt_search_keys = array(
			'l', 'jt', 'salary', 'as_and', 'as_phr',
			'as_any', 'as_not', 'as_ttl', 'as_cmp', 'st',
			'sr', 'fromage'
		);

		foreach ($opt_search_keys as $key) {
			if (array_key_exists($key, $arrCond) && $arrCond[$key]) {
				if ($key == 'as_phr' && (array_key_exists('fax', $arrCond) && $arrCond['fax'] == 1)) {
					//FAX番号の取得
					$query .= '&'.$key.'='.urlencode($arrCond[$key] . ' ' . $fax_search );
				}
				else if ($key == 'as_any' && (array_key_exists('email', $arrCond) && $arrCond['email'] == 1)) {
					//E-mailの取得
					$query .= '&'.$key.'='.urlencode($arrCond[$key] . ' ' . $email_search );
				}
				else {
					$query .= '&'.$key.'='.urlencode($arrCond[$key]);
				}
			}
			else if ($key == 'as_phr' && array_key_exists($key, $arrCond)) {
				//FAX番号の取得
				if (array_key_exists('fax', $arrCond) && $arrCond['fax'] == 1) {
					$query .= '&'.$key.'='.urlencode($fax_search);
				}
			}
			else if ($key == 'as_any' && array_key_exists($key, $arrCond)) {
				//E-mailの取得
				if (array_key_exists('email', $arrCond) && $arrCond['email'] == 1) {
					$query .= '&'.$key.'='.urlencode($email_search);
				}
			}
		}
	}
	else {
		// 標準検索（企業採用ページのみを対象にする）
		$query = 'http://jp.indeed.com/%E6%B1%82%E4%BA%BA?q='.urlencode($arrCond['q']).'&l='.urlencode($arrCond['l']);
	}
	
	// 共通項目
	$query .= '&radius=0'; // 勤務地「範囲内のみ」
	$query .= '&sort=date'; // 並び替え「新着順」
	if (!array_key_exists('filter', $arrCond) || !$arrCond['filter']) $query .= '&filter=0'; //類似する求人も表示する
	$query .= in_array($contract_type, array(2,3)) ? '&limit=50' : '&limit=30';

	if ($idx_from) $query .= '&start='.$idx_from;
	// バックエンドサーバを経由して、実際に問い合わせを行う
	$content = array('stream_option' => make_request_context_array($_SERVER, 'GET', $user_agent), 'mode' => 'f', 'url' => $query);
	$post_context_stream = stream_context_create(make_request_context_array($_SERVER, 'POST', $user_agent, $content));
	$data = @file_get_contents($backend_server['proxy_script_url'], false, $post_context_stream);
	if ($data === false) {
		// リクエストエラー
		$arrAlert['BACKEND_SERVER_ABNORMAL_RESPONSE'] = isset($http_response_header) ? $http_response_header : array();
	}
	$res = (isset($http_response_header) && array_key_exists(0, $http_response_header)) ? $http_response_header[0] : null;
	if ($res) {
		if (preg_match('#^HTTP/1.1 4\d{2}#', $res)) {
			$res_info = array('バックエンドサーバ' => $backend_server);
			$res_info['レスポンスヘッダー'] = $http_response_header;
			alert_mail(REQUEST_ERROR_ALERT_MAIL_TITLE, $res_info);
		}
	}

	request_log($res, $user_id, $backend_server['id'], $qid, 'search '.$idx_from);

	// 各項目を抽出するための正規表現と、異常と判定するマッチ成功率の設定
	$regex_list = array(
		'company_name' => array(
			'regex'	=> '/<span class="?company"? itemprop="hiringOrganization" itemtype="http:\/\/schema\.org\/Organization">\s*<span itemprop="name">\s*(.+?)\s*<\/span>\s*<\/span>/us',
			'match_rate'	=> 20,
			'match_count'	=> 0
		),
		'location' => array(
			'regex'	=> '/<span itemprop="addressLocality">(.+?)<\/span>/us',
			'match_rate'	=> 50,
			'match_count'	=> 0
		),
		'salary' => array(
			'regex'	=> '/<span class="no-wrap">(.+?)<\/span>/us',
			'match_rate'	=> 10,
			'match_count'	=> 0
		),
		'source' => array(
			'regex'	=> '/<span class="?(?:sdn|result-link-source)"?>(.+?)<\/span>/us',
			'match_rate'	=> 90,
			'match_count'	=> 0
		),
		'url_code' => array(
			'regex'	=> '/href="\/rc\/clk\?(?:[^"]+&)*?jk=(.*?)"/us',
			'match_rate'	=> 50,
			'match_count'	=> 0
		),
		'url_ind' => array(
			'regex' => '/href="\/cmp\/[^\/"]+\/jobs\/[^"]*\-(\w+)/us',
			'match_rate'	=> 0,
			'match_count'	=> 0
		),
		'employment_type' => array(
            'regex'	=> '/<td class=snip>\s*(?:<span class="no-wrap">[^<]*?<\/span>\s*)?\-?\s*(.*?)\s*<div>/us',
			'match_rate'	=> 10,
			'match_count'	=> 0
		),
		/*
		新着はjsで埋め込んでいる？ソースから直接は取得不可
		'is_new' => array(
			'regex' => '/<span class=new>(新着)<\/span>/us'
		),
		*/
		'job_type'			=> array(),
		'location_detail'	=> array(),
		'tel'				=> array(),
		'fax'				=> array(),
		'zip'				=> array(),
		'email'				=> array(),
		'representative'	=> array(),
		'employees'			=> array(),
		'capital'			=> array(),
		'turnover'			=> array(),
		'establishment'		=> array(),
		'remarks'			=> array()
	);
	
	if ($data === false) {
		// NOP（HTTPリクエストにそもそも失敗した）
	}
	elseif (preg_match_all('/<div class="\s*(?:last)?row[^"]*"\s*id=".*?(<\/div>\s*<\/div>\s*<\/div>|その他のツール<\/a>)/isu', $data, $arrMatches, PREG_SET_ORDER)) {
        $result_count = 0;
        foreach ($arrMatches as $idx => $arrMatch) {

            if ($arrMatch[1] != "その他のツール</a>") {
				continue;
			}
			$gotInfo = array();
			foreach ($regex_list as $key => $regex) {
				$gotInfo[$key] = null;
				if (array_key_exists('regex', $regex) && preg_match($regex['regex'], $arrMatch[0], $ma)) {
					$gotInfo[$key] = $ma[1];
					$regex_list[$key]['match_count']++;
				}
			}
			$gotInfo['done'] = 0;
			$gotInfo['idx']	 = $idx_from + $result_count + 1;
			$arrResult[($idx_from + $result_count)] = $gotInfo;

            if (count($arrResult) >= $max_result) {
                break;
            }

            $result_count++;
		}

		// 取得できた結果が10件以上なら、マッチの成功率を確認
		if ($result_count >= 10) {
			foreach ($regex_list as $key => $regex) {
				if (array_key_exists('match_rate', $regex) &&
				    (($regex['match_count'] * 100 / $result_count) < $regex['match_rate'])) {
						if (!array_key_exists('MATCH_RATE_ERROR', $arrAlert)) {
							$arrAlert['MATCH_RATE_ERROR'] = array();
						}
						$arrAlert['MATCH_RATE_ERROR'][] = array(
							'key'			 => $key,
							'result_count'	 => $result_count,
							'match_count'	 => $regex['match_count'],
							'min_match_rate' => $regex['match_rate']
						);
				}
			}
		}
		
		// 検索結果件数
		if (preg_match('/求人検索結果\s([\d,]+)\s件中\s([\d,]+)\s\-\s([\d,]+)/u', $data, $ma)) {
			$total_count	= str_replace(',', '', $ma[1]);
			$loaded_count	= str_replace(',', '', $ma[3]);
		}
		else {
			$arrAlert['COUNT_MATCH_ERROR'] = COUNT_MATCH_ERROR_MSG;
		}
		
	}
	elseif (preg_match('/勤務地の <b>[^<>]*<\/b> が見つかりませんでした。/u', $data)) {
		// 「勤務地の ○○ が見つかりませんでした。」→スルー
		// NOP
	}
	elseif (strpos($data, 'に一致する求人は見つかりませんでした') === false) {
		// HTMLは取れたのに、「見つかりませんでした」でも無く、データが取れなかった → 解析失敗の可能性
		$arrAlert['PRIMITIVE_MATCH_ERROR'] = PRIMITIVE_MATCH_ERROR_MSG;
		require 'primitive_match_error.inc';
	}

	// データが取得できたら、結果1件ずつに対して、もう一度indeedに情報取得元URLを問い合わせに行く
	$idx = $idx_from;
	$has_result = count($arrResult) ? true : false;
	$has_next = false;
	if ($data !== false) {
		while (isset($arrResult[$idx])) {
			$row = $arrResult[$idx];
			if ($row['url_code'])
				// $arrResult[$idx]['url'] = sprintf(INDEED_REDIRECTER_URL_PATTERN, $row['url_code']);
				$arrResult[$idx]['url'] = false; // indeedに直接アクセスされることを防ぐため、URLは持たない
			else
				$arrResult[$idx]['url'] = false;
			
			/*
			if ($row['company_name']) $arrResult[$idx]['ppt_url'] = $ppt_url;
			else $arrResult[$idx]['ppt_url'] = false;
			*/

			if ($row['company_name']) {
				$arrResult[$idx]['ppt_query'] = array(
					'name'	=> urlencode(htmlspecialchars_decode(strip_tags($row['company_name']))),
					'url'	=> $ppt_url
				);
			}

            if (!isset($arrResult[$idx+1])) {
                if ($total_count == ($idx + 1)) $arrResult[$idx]['last_row'] = 1;
                else $arrResult[$idx]['last_row'] = 2;
            }
            else {
                $arrResult[$idx]['last_row'] = 0;
            }
            if ($total_count > $max_result && $max_result == $idx + 1) $arrResult[$idx]['last_row'] = 3;

			$idx++;
		}
		
		if ($idx >= $max_result) {
			// NOP（上限に達した）
		}
		elseif (preg_match('/<span class=np>次へ&nbsp;&raquo;<\/span>/su', $data)) {
			$has_next['disp_limit'] = in_array($contract_type, array(2,3)) ? 50 : 30; // 常に50件(トライアルの場合は30件)毎
		}
		else {
			$total_count = $loaded_count; // 次のページが無ければ、結果件数を実際取得した数で上書き
		}
	}

    // 正規表現による解析に失敗した可能性があれば通知を行う
	if ($arrAlert) {
		$arrAlert['CONDITION'] = $arrCond;
		alert_mail(INDEED_PARSER_ALERT_MAIL_TITLE, $arrAlert);
	}

	return array(
		'result_list'	=> $arrResult,
		'total_count'	=> $total_count,
		'loaded_count'	=> $loaded_count,
		'has_result'	=> $has_result,
		'has_next'		=> $has_next,
		'max_result'	=> $max_result
	);
}


/*----------------------------------------------------------------------------
機能概要: 
機能説明: 
引数:
戻り値: void
----------------------------------------------------------------------------*/
function make_request_context_array(&$arrServer, $method, $user_agent = null, $content = null) {
	$headers = array(
		'Accept'			=> 'HTTP_ACCEPT',
		'Accept-Charset'	=> 'HTTP_ACCEPT_CHARSET',
		'Accept-Language'	=> 'HTTP_ACCEPT_LANGUAGE'
	);
	
	$header = array();
	/*
	if (array_key_exists('HTTP_USER_AGENT', $arrServer))
		$header[] = "User-Agent: ".$arrServer['HTTP_USER_AGENT'];
	else
		$header[] = "User-Agent: ".DEFAULT_USER_AGENT;
	*/
	if (!is_null($user_agent)) $header[] = "User-Agent: ".$user_agent;
	else $header[] = "User-Agent: ".DEFAULT_USER_AGENT;
	foreach ($headers as $name => $server_key) {
		if (array_key_exists($server_key, $arrServer)) {
			$header[] = $name.": ".$arrServer[$server_key];
		}
	}
	$header[] = "Accept-Encoding: identity"; // レスポンスを圧縮させない
	$header[] = "Cache-Control: max-age=0";

	$context = array('http' => array('method' => $method));
	if (is_array($content)) {
		$context['http']['content'] = http_build_query($content, "", "&");
		$header[] = "Content-Type: application/x-www-form-urlencoded";
		$header[] = "Content-Length: ".strlen($context['http']['content']);
	}
	$context['http']['header'] = implode("\r\n", $header);
	return $context;
}


/*----------------------------------------------------------------------------
機能概要: アラートメール送信
機能説明: アラートメール送信
引数:
	text	$subject	[IN]  メール件名
	array	$arrDetail	[IN]  詳細

戻り値: void
----------------------------------------------------------------------------*/
function alert_mail($subject, array $arrDetail) {
	$error_ini = load_error_ini();
	$mbody = "エラーの疑いが検出されました。\r\n";
	$mbody .= print_r($arrDetail, true);
	mb_send_mail($error_ini['mail_to'], $subject, $mbody, 'From: '.$error_ini['mail_from']);
}


/*----------------------------------------------------------------------------
機能概要: ロギング
機能説明: ロギング
引数:
戻り値: void
----------------------------------------------------------------------------*/
function request_log($res, $user_id, $server_id, $qid, $proc) {
    $project_home = dirname(dirname(__FILE__));
    $path = $project_home . '/logs/'.date('Ymd').'.log';
    $new = false;
    if (!file_exists($path)) {
        $new = true;
	}
	$row = array(date('H:i:s'), $res, $user_id, $server_id, $qid, $proc);
	$fp = fopen($path, 'a');
	if (flock($fp, LOCK_EX)) {
		fwrite($fp, implode(',', $row)."\r\n");
		flock($fp, LOCK_UN);
	}
	fclose($fp);
	if ($new) {
        chmod($path, 0777);
	}
}


/*----------------------------------------------------------------------------
機能概要: バックエンドサーバ一覧の取得・キャッシュ作成
機能説明: バックエンドサーバ一覧の取得・キャッシュ作成
引数:
戻り値: void
----------------------------------------------------------------------------*/
/*
function get_backend_server_list() {
	if (file_exists(CACHE_BACKEND_SERVER_LIST)) {
		// キャッシュが見つかった
		$data = file_get_contents(CACHE_BACKEND_SERVER_LIST);
		$data = unserialize($data);
	}
	else {
		// キャッシュが無い
		$db = new mysql_db();
		$cols = 'id, name, proxy_script_url, use_random, status';
		$from = 'backend_server';
		$rows = $db->select($cols, $from);
		$data = array(
			'use_random_id_list' => array(),
			'server_list'		 => array()
		);
		foreach ($rows as $row) {
			$data['server_list'][$row['id']] = $row;
			if ($row['use_random'] == 1 && $row['status'] == 1) {
				$data['use_random_id_list'][] = $row['id'];
			}
		}
		if (!file_put_contents(CACHE_BACKEND_SERVER_LIST, serialize($data))) {
			trigger_error('バックエンドサーバのキャッシュファイル作成に失敗しました。', E_USER_ERROR);
		}
	}
	return $data;
}
*/

/*----------------------------------------------------------------------------
機能概要: iタウンページにアクセスし、情報の取得
機能説明: iタウンページにアクセスし、情報の取得
引数:
	int		$qid	    [IN]  セッションID
	int		$target_idx	[IN]  対象となる行番号
	int		$type	    [IN]  1：社名のみ／2：社名・所在地
	int		$pgno	    [IN]  ページ番号
	int		$user_id	[IN]  ユーザーID
	string	$kw			[IN]  検索キーワード
	string	$user_agent	[IN]  User-Agent
戻り値: array iタウンページ情報
----------------------------------------------------------------------------*/
function request_itown($qid, $target_idx, $type, $pgno, $user_id, $kw, $user_agent) {
	
	$max_pg = ceil(ITOWN_MAX_RESULT / 50);
	if ($pgno > $max_pg) {
		error('アクセスエラー', '不正なアクセスです', 'index.php');
	}
	
	//リストに情報の無いIDが指定されたら空で返す
	if (!array_key_exists($qid, $_SESSION['q']) || !array_key_exists($target_idx, $_SESSION['q'][$qid]['result_list'])) {
		return array(
			'kw'			=> '',
			'result_list'	=> array(),
			'total_count'	=> '',
			'loaded_count'	=> '',
			'current_count'	=> '',
		);
	}
	
	//バックエンドサーバー
	if (array_key_exists('backend_server_group', $_SESSION['q'][$qid])) {
        $backend_server_group = $_SESSION['q'][$qid]['backend_server_group'];
	} else {
        $BackendServer = new BackendServer();
        $backend_server_group = $BackendServer->get_backend_server_group();
	}

	if (is_null($kw)) {
		// リストページからの自動検索
		$query_data = $_SESSION['q'][$qid]['result_list'][$target_idx];
		$company_name = html_entity_decode($query_data['company_name']);
		$company_name = str_replace(array("\r\n","\r","\n"), " ", $company_name);
		$company_name = preg_replace('/<br[[:space:]]*\/?[[:space:]]*>/iu', " ", $company_name);
		$company_name = trim(strip_tags($company_name));
	
		//所在名
		preg_match('/^(\S+?)( (\S+))?$/u', $query_data['location'], $ma);
		$location = $ma[1];
		
		//株式会社に置換
		$company_name = preg_replace('/（株）|\(株\)|㈱/u', '株式会社', $company_name);
		//有限会社に置換
		$company_name = preg_replace('/（有）|\(有\)|㈲/u', '有限会社', $company_name);
		$arrRemoval = array(
			'株式会社', '有限会社', '法人'
		);
		foreach ($arrRemoval as $removal) {
			if ($removal == '法人') {
				$pattern = '/\s*(\w*' . $removal . '\w*)\s*/u';
			}
			else {
				$pattern = '/\s*(' . $removal . ')\s*/u';
			}
				//$pattern = '/\s*(' . $removal . ')\s*/u';
			if (preg_match($pattern, $company_name)) {
				$company_name = preg_replace($pattern, "$1", $company_name);
				break;
			}
		}
		
		if ($type == 1) {
			//社名のみ
			$query_string = $company_name;
			$arrCond = array(
				'company_name' => $company_name
			);
		}
		else {
			//社名・所在地	
			$query_string = $company_name . ' ' . $location;
			$arrCond = array(
				'company_name' => $company_name,
				'location' => $location,
			);
		}
		$query_string = trim($query_string);
		
		if ($query_string == '') {
			return array(
				'kw'			=> $query_string,
				'result_list'	=> $arrResult,
				'total_count'	=> $total_count,
				'loaded_count'	=> $loaded_count,
				'current_count'	=> $current_count,
				'has_result'	=> '',
			);
		}
	}
	else {
		// 手動検索
		$arrCond = array(
			'company_name' => $kw
		);
		
		$query_string = $kw;
	}

	$arrCond['pgno'] = $pgno;
	$arrParse = parse_itown($backend_server_group, $user_agent, $qid, $user_id, $arrCond, 'completion');

	$has_next = false;
	if ($arrParse['has_result']) {
		
		$max_pg = ceil(ITOWN_MAX_RESULT / 50);
		$total_count = $arrParse['total_count'];
		$max_pager = ceil($total_count / 50);
		if (preg_match('/<div class="bottomNav">\s*(.+?)\s*<span>/su', $arrParse['html_data'], $ma)) {
			$pager = $ma[1];
			$pattern ='/(<a href="javascript\:void\(0\)" style="cursor\: default">)(\d+)(<\/a>)/';
			$replace_url = '$2';
			$pager =  preg_replace($pattern, $replace_url, $pager);
			$pattern ="/(\/result\/.+)(pg=\d+)/";
			if (!$kw) $replace_url = "completion.php?qid={$qid}&idx={$target_idx}&type={$type}&$2";
			else $replace_url = "completion.php?kw={$kw}&qid={$qid}&idx={$target_idx}&type={$type}&$2";
			$pager =  preg_replace($pattern, $replace_url, $pager);
			
			$pattern = "/($max_pg<\/a><\/li>)(.+)(<\/ul>)/su";
			$pager =  preg_replace($pattern, '$1$3', $pager);
			$pattern = "/($max_pg<\/li>)(.+)(<\/ul>)/su";
			$pager =  preg_replace($pattern, '$1$3', $pager);

			$has_next['pager'] = $pager;
		}
	}

	$arrParse['kw'] = $query_string;
	$arrParse['has_next'] = $has_next;

	return $arrParse;
}

/*----------------------------------------------------------------------------
機能概要: iタウンページにアクセスし、情報の取得
機能説明: iタウンページにアクセスし、情報の取得
引数:
	string		$proxy_script_url    [IN]  バックエンドサーバーURL
	string		$url                 [IN]  iタウンページURL
	string		$user_agent          [IN]  User-Agent
戻り値: array リクエスト結果
----------------------------------------------------------------------------*/
function access_itown($proxy_script_url, $url, $user_agent) {
	
	$content = array('stream_option' => make_request_context_array($_SERVER, 'GET', $user_agent), 'mode' => 'c', 'url' => $url);
	
	$post_context_stream = stream_context_create(make_request_context_array($_SERVER, 'POST', $user_agent, $content));
	$data = @file_get_contents($proxy_script_url, false, $post_context_stream);

	if ($data) $data = mb_convert_encoding($data, 'UTF-8', 'SJIS-WIN');
	if (!isset($http_response_header)) $http_response_header = array();
	return array($data, $http_response_header);
	
}


/*----------------------------------------------------------------------------
機能概要: iタウンページ情報の解析
機能説明: iタウンページ情報を解析する
引数:
	int		$backend_server	[IN]  バックエンドサーバ情報
	int		$query	    [IN]  検索クエリ
	string	$user_agent	[IN]  User-Agent
	int		$qid	    [IN]  セッションID
	int		$pgno	    [IN]  ページ番号
	int		$user_id	[IN]  ユーザーID
	string	$mode		[IN]  itown利用元（completion,book_search）
戻り値: array リクエスト結果
----------------------------------------------------------------------------*/
function parse_itown($backend_server_group, $user_agent, $qid, $user_id, $arrCond, $mode) {
	$arrAlert = array(); // 正規表現によるマッチに失敗した可能性など・アラート項目
	$arrResult = array();
	$current_count	= 0;
	$total_count	= 0;
	$loaded_count	= 0;
    $idx = 0;
	
	if ($mode == 'completion') {
		$query_string = $arrCond['company_name'];
		if (array_key_exists('location', $arrCond)) $query_string .= ' ' . $arrCond['location'];
		$query = 'http://itp.ne.jp/result/?kw='.urlencode(mb_convert_encoding($query_string, 'SJIS-WIN', 'UTF-8'));
		$pgno = $arrCond['pgno'];
		if ($pgno) $query .= '&pg='.$pgno;
		if ($query_string) $query .= '&num=50';
	}
	else {
		$kw = $arrCond['kw'];
		$kw2 = $arrCond['kw2'];
		$query_string = $kw . '	' . $kw2;
	
		$query = 'http://itp.ne.jp/result/?kw='.urlencode(mb_convert_encoding($query_string, 'SJIS-WIN', 'UTF-8'));
		$pgno = $arrCond['pgno'];
		if ($pgno) $query .= '&pg='.$pgno;
		if ($query_string) $query .= '&num=50';
	}
	

	$BackendServer = new BackendServer();
	$backend_server = $BackendServer->get_backend_server($backend_server_group);
	list($data, $http_response_header) = access_itown($backend_server['proxy_script_url'], $query, $user_agent);
	if ($data === false) {
		// リクエストエラー
		$arrAlert['BACKEND_SERVER_ABNORMAL_RESPONSE'] = $http_response_header;
	}
	$res = array_key_exists(0, $http_response_header) ? $http_response_header[0] : null;
	if ($res) {
		if (preg_match('#^HTTP/1.1 4\d{2}#', $res)) {
			$res_info = array('バックエンドサーバ' => $backend_server);
			$res_info['レスポンスヘッダー'] = $http_response_header;
			alert_mail(REQUEST_ERROR_ALERT_MAIL_TITLE, $res_info);
		}
	}
	
	request_log($res, $user_id, $backend_server['id'], $qid, $mode . ' '.$pgno);

	// 各項目を抽出するための正規表現と、異常と判定するマッチ成功率の設定
	$regex_list = array(
		'i_company_name' => array(
			'regex'	=> '/<h4 [^>]*>\s*<a [^>]*href="\/shop\/.+?" target="_blank">(.+?)<\/a>/us',
			'match_rate'	=> 80,
			'match_count'	=> 0
		),
		'i_url_code' => array(
			'regex'	=> '/href="\/shop\/(.+?)" target="_blank">\s*<img/us',
			'match_rate'	=> 0,
			'match_count'	=> 0
		),
		'i_zip' => array(
			'regex' => '/〒(\d{3}\-\d{4})/us',
			'match_rate'	=> 0,
			'match_count'	=> 0
		),
		'i_location_detail' => array(
			'regex' => '/<span .+>住所<\/span>\s〒\d{3}\-\d{4}　(.+?)\s?(<a.*)?<\/p>/us',
			'match_rate'	=> 0,
			'match_count'	=> 0
		),
		'i_tel_icon' => array(
			'regex' => '/(F専|F兼)/us',
			'match_rate'	=> 0,
			'match_count'	=> 0
		),
		'i_tel' => array(
			'regex' => '/(\d{2,4}-\d{2,4}-\d{4}|0120\-\d{6})/us',
			'match_rate'	=> 0,
			'match_count'	=> 0
		),
		'i_email' => array(
			'regex'	=> '/<span .+>EMAIL<\/span>\s*([^<>]+).*?<\/p>/us',
			'match_rate'	=> 0,
			'match_count'	=> 0
		),
		'i_site_url' => array(
			'regex'	=> '/<span .+>URL<\/span>\s*([^<>]+).*?<\/p>/us',
			'match_rate'	=> 0,
			'match_count'	=> 0
		),
	);
	$no_detail_regex_list = array(
		/*
		'i_business' => array(
			'regex'	=> '/<th>業種<\/th>\s*<td>(.+?)<\/td>/us',
		),
		*/
		'i_site_url' => array(
			'regex'	=> '/<th>URL<\/th>\s*<td>\s+([^<>]+).*?<\/td>/us',
		),
		'i_email' => array(
			'regex'	=> '/<th>E-\mail<\/th>\s*<td>\s+([^<>]+).*?<\/td>/us',
		),
	);
	$ad_detail_regex_list = array(
		/*
		'i_business' => array(
			'regex'	=> '/業種.*?(.+?)<\/td>/us',
		),
		*/
		'i_fax' => array(
			'regex'	=> '/FAX番号.*?((\d{2,4}-\d{2,4}-\d{4}))/us',
		),
		'i_site_url' => array(
			'regex'	=> '/ＵＲＬ.*?<A[^>]*>(.+?)<\/A>\s+.*?E\-mail/us',
		),
		'i_email' => array(
			'regex'	=> '/E-\mail.*?<A[^>]*>(.+?)<\/A>/us',
		),
	);
	if ($data === false) {
		// NOP（HTTPリクエストにそもそも失敗した）
	}
	elseif (preg_match_all('/<div class="normalResultsBox.*?お気に入りに登録<\/a>/su', $data, $arrMatches, PREG_SET_ORDER)) {
		foreach ($arrMatches as $idx => $arrMatch) {
			$gotInfo = array();
			foreach ($regex_list as $key => $regex) {
				$gotInfo[$key] = null;
				if (array_key_exists('regex', $regex) && preg_match($regex['regex'], $arrMatch[0], $ma)) {
					$gotInfo[$key] = $ma[1];
					$regex_list[$key]['match_count']++;
				}
			}
			$gotInfo['done'] = 1;
			$gotInfo['i_idx']	 = $idx + 1;

			if ($gotInfo['i_tel_icon'] == 'F専') {
				$gotInfo['i_fax'] = $gotInfo['i_tel'];
				$gotInfo['i_tel'] = '';
			}
			else if ($gotInfo['i_tel_icon'] == 'F兼') {
				$gotInfo['i_fax'] = $gotInfo['i_tel'];
			}
			
			if (!array_key_exists('i_fax', $gotInfo)) $gotInfo['i_fax'] = '';
			$arrResult[$idx] = $gotInfo;
		}

		// 取得できた結果が10件以上なら、マッチの成功率を確認
		$result_count = count($arrMatches);
		if ($result_count >= 10) {
			foreach ($regex_list as $key => $regex) {
				if ($key == 'i_url_code') continue;
				if (array_key_exists('match_rate', $regex) &&
				    (($regex['match_count'] * 100 / $result_count) < $regex['match_rate'])) {
						if (!array_key_exists('MATCH_RATE_ERROR', $arrAlert)) {
							$arrAlert['MATCH_RATE_ERROR'] = array();
						}
						$arrAlert['MATCH_RATE_ERROR'][] = array(
							'key'			 => $key,
							'result_count'	 => $result_count,
							'match_count'	 => $regex['match_count'],
							'min_match_rate' => $regex['match_rate']
						);
				}
			}
		}
		
		// 検索結果件数
		if (preg_match('/<span>(\d+)\-(\d+)件\/(\d+)件<\/span>/u', $data, $ma)) {
			$current_count	= $ma[1];
			$loaded_count	= $ma[2];
			$total_count	= $ma[3];
		}
		else {
			$arrAlert['COUNT_MATCH_ERROR'] = COUNT_MATCH_ERROR_MSG;
		}
	}
	elseif (strpos($data, '該当する情報は見つかりませんでした。') === false) {
		// HTMLは取れたのに、「見つかりませんでした」でも無く、データが取れなかった → 解析失敗の可能性
	    if (preg_match_all('/<div class="highlightedResultsBox.*?お気に入りに登録<\/a>/su', $data, $arrMatches, PREG_SET_ORDER)) {
			//取得結果は0件だが広告のみ表示される場合がある
			//NOP
		}
		else {
			$arrAlert['PRIMITIVE_MATCH_ERROR'] = PRIMITIVE_MATCH_ERROR_MSG;
		}
	}

	$has_result = count($arrResult) ? true : false;
	if ($mode == 'completion') {
		// データが取得できたら、結果1件ずつに対して、もう一度iタウンページに情報取得元URLを問い合わせに行く
		$idx = 0;
		if ($data !== false) {
			while (isset($arrResult[$idx])) {
				$backend_server = $BackendServer->get_backend_server($backend_server_group);
				$row = get_itown_detail($arrResult, $idx, $ad_detail_regex_list, $backend_server, $user_agent, false);
				$arrResult[$idx] = $row;
				$idx++;
			}
		}
	}

	// 正規表現による解析に失敗した可能性があれば通知を行う
	if ($arrAlert) {
		$arrAlert['CONDITION'] = $arrCond;
		$arrAlert['SERVER'] = $_SERVER;
		
		alert_mail(ITOWN_PARSER_ALERT_MAIL_TITLE, $arrAlert);
	}
	return array(
		'result_list'	=> $arrResult,
		'total_count'	=> $total_count,
		'loaded_count'	=> $loaded_count,
		'current_count'	=> $current_count,
		'has_result'	=> $has_result,
		'idx'			=> $idx,
		'html_data'		=> $data
	);
}

/*----------------------------------------------------------------------------
機能概要: iタウンページにアクセスし、詳細ページ情報の取得
機能説明: iタウンページにアクセスし、詳細ページ情報の取得
引数:
	int		$arrResult		        [IN]  検索一覧
	int		$idx					[IN]  対象となる行番号
	int		$ad_detail_regex_list	[IN]  正規表現リスト
	int		$backend_server			[IN]  バックエンドサーバ
	string	$user_agent				[IN]  User-Agent
戻り値: array iタウンページ情報
----------------------------------------------------------------------------*/
function get_itown_detail($arrResult, $idx, $ad_detail_regex_list, $backend_server, $user_agent, $detail = true) {
	$row = $arrResult[$idx];
	
	if (!$detail) return $row;
	
	//$row['idx'] = $idx;
	if ($row['i_url_code']) {
		if (preg_match('/^[a-zA-Z0-9]+\/$/', $row['i_url_code'])) {
			//NOP
		}
		else {
			$detail_regex_list = $ad_detail_regex_list;
			$query = 'http://itp.ne.jp/shop/' . $row['i_url_code'];
			list($detail_data, $http_response_header) = access_itown($backend_server['proxy_script_url'], $query, $user_agent);
			$res = array_key_exists(0, $http_response_header) ? $http_response_header[0] : null;
			if ($res) {
				if (preg_match('#^HTTP/1.1 4\d{2}#', $res)) {
					$res_info = array('バックエンドサーバ' => $backend_server);
					$res_info['レスポンスヘッダー'] = $http_response_header;
					alert_mail(REQUEST_ERROR_ALERT_MAIL_TITLE, $res_info);
				}
			}

			foreach ($detail_regex_list as $key => $regex) {
				$row[$key] = '';
				if (array_key_exists('regex', $regex) && preg_match($regex['regex'], $detail_data, $ma)) {
					$row[$key] = $ma[1];
				}
			}
		}
	}

	return $row;
}

/*----------------------------------------------------------------------------
機能概要: 編集された行を元に戻す
機能説明: 編集された行を元に戻す
引数:
	int		$qid		        [IN]  問い合わせID
	int		$idx				[IN]  対象となる行番号
戻り値: array 元に戻されたデータ
----------------------------------------------------------------------------*/
function get_rest_row($qid, $idx) {
	$arrCompletionKeys = array(
		'company_name', 'location_detail', 'zip', 'email', 'tel', 'fax',
		'employment_type', 'job_type', 'salary', 'location',
		'representative', 'employees', 'capital', 'turnover', 'establishment',
		'remarks'
	);
		
	$client_type = $_SESSION['q'][$qid]['client_type'];
	$u_idx = $idx - 1;
	$result_row = $_SESSION['q'][$qid]['result_list'][$u_idx];

	/*
	foreach($arrCompletionKeys as $completion_key) {
		if (!array_key_exists('d_' . $completion_key, $result_row)) continue;

		if ($completion_key == 'company_name') {
			$result_row['ppt_query']['name'] = urlencode($result_row['d_company_name']);
		}

		if ($completion_key == 'tel') {
			if ($result_row['d_tel'] && $client_type == CLIENT_TYPE_SP)
				$result_row['tel_n'] = preg_replace('/\D/', '', $result_row['d_tel']);
			else
				$result_row['tel_n'] = null;
		}
		$result_row[$completion_key] = $result_row['d_' . $completion_key];
		$result_row[$completion_key . '_completion'] = 0;
	}
	*/
	$result_row['completion'] = 0;

	return $result_row;
}

endif/* SUB_APP_INC */?>
