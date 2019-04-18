<?php declare(encoding = 'UTF-8');
if (!defined('DB_CLASS_INC')): define('DB_CLASS_INC', 1);

class db extends old_db {
	var $db;
	var $field_types;
	var $field_names;

	public function __construct($conn_str = NULL, $force_new=false) {
		if (is_null($conn_str)) {
			$env = new Env;
			$tmp = array(
				'dbname='.$env->database->dbname,
				'user='.$env->database->user,
			);
			if ($env->database->password != '') $tmp[] = 'password='.$env->database->password;
			if ($env->database->host     != '') $tmp[] = 'host='.$env->database->host;
			if ($env->database->port     != '') $tmp[] = 'port='.$env->database->port;
			$conn_str = implode(' ', $tmp);
		}
		$this->db = ($force_new) ? pg_connect($conn_str, PGSQL_CONNECT_FORCE_NEW) : pg_connect($conn_str);
		$this->field_types = array();
		$this->field_names = array();
	}

	public function client_encoding($enc = NULL) {
		$last_enc = pg_client_encoding($this->db);
		if (!is_null($enc)) pg_set_client_encoding($this->db, $enc);
		return $last_enc;
	}

	private function replace(&$i, $p, $args) {
		if ($p == '?') {
			ifthrow(!array_key_exists($i, $args), "{$i}番目の?に対応するデータが見つからない。", E_USER_ERROR);
			$v = $args[$i];
			$i++;
		}
		elseif (substr($p, 0, 1) == ':') {
			$ns = explode('.', substr($p, 1));
			$v = $args;
			foreach ($ns as $n) {
				ifthrow(!is_array($v) || !array_key_exists($n, $v), "プレースホルダ名'{$p}'に対応するデータがみつからない。", E_USER_ERROR);
				$v = $v[$n];
			}
		}
		else
			return $p;

		if (is_null($v))                $v = 'NULL';
		elseif (is_bool($v))            $v = $v? 'TRUE': 'FALSE';
		elseif (is_string($v))          $v = '\''.pg_escape_string($this->db, $v).'\'';
		elseif (is_int($v))             $v = (string)$v;
		elseif (is_float($v))           $v = (string)$v;

		elseif (is_array($v)) {
			if (array_key_exists('bytea', $v)) {
				$v = '\'' . pg_escape_bytea($this->db, $v['bytea']) . '\'';
			}
		}

		elseif ($v instanceof iDBValue) $v = $v->getDBString($this);
		else {
			$type = gettype($v);
			trigger_error("変換できないデータ型が指定されました($type)", E_USER_ERROR);
		}

		return $v;
	}

	private function query($args) {
		$this->field_types = array();
		$this->field_names = array();
		$sql = array_shift($args);
		if (count($args) == 1 && is_array($args[0])) $args = $args[0];
		$i = 0;
		$result = '';
		$sql = preg_replace('/(\'(?:[^\']|\\\\\'|\'\')*(?<!\\\\)\'|\?|(?<!:):[\w:.-]+)/se', '$this->replace($i, \'$1\', $args)', $sql);
		$result = pg_query($this->db, $sql);
		return $result;
	}

	final public function exec() {
		$args = func_get_args();
		$r = $this->query($args);
		$n = pg_affected_rows($r);
		pg_free_result($r);
		return $n;
	}
	final public function begin()    { $this->exec('BEGIN;'); }
	final public function commit()   { $this->exec('COMMIT;'); }
	final public function rollback() { $this->exec('ROLLBACK;'); }

	protected function fetchrow($is_hash, $row, $r) {
		$fn = pg_num_fields($r);
		$result = array();
		$v_ary = pg_fetch_assoc($r, $row);
		return $this->get_result($v_ary, $is_hash, $r);

	}


	protected function fetchall($is_hash, $r) {
		$fn = pg_num_fields($r);
		$result_ary = array();
		$v_arys = pg_fetch_all($r);
		foreach($v_arys as $v_key => $v_ary) {
			$result_ary[$v_key] = $this->get_result($v_ary, $is_hash, $r);
		}
		return $result_ary;
	}


	protected function get_result($v_ary, $is_hash, $r, $i = 0) {
		$result = array();
		foreach ($v_ary as $f_name => $v) {
			if (!array_key_exists($i, $this->field_types)) $this->field_types[$i] = pg_field_type($r, $i);
			$t = $this->field_types[$i];
			if (is_null($v)) {
				switch ($t) {
				case 'bytea':
					$v = new ByteArray;
					break;
				default:
				}
			}
			else {
				switch ($t) {
				case 'bool':
					$v = $v == 't';
					break;

				case 'int2':
				case 'int4':
					$v = (int)$v;
					break;

				case 'numeric':
					$v = (float)$v;
					break;

				case 'date':
				case 'timestamptz':
				case 'timestamp':
				case 'time':
					$v = new Timestamp($v);
					break;

				case 'bytea':
					$v = new ByteArray(pg_unescape_bytea($v));
					break;
				
				default:
				}
			}
			if ($is_hash) {
				if (strpos($f_name, '.') !== FALSE) {
					$a =&$result;
					foreach (explode('.', $f_name) as $n) {
						if (!is_array($a)) $a = array();
						if (!array_key_exists($n, $a)) $a[$n] = array();
						$a = &$a[$n];
					}
					$a = $v;
				}
				else {
					$result[$f_name] = $v;
				}
			}
			else {
				$result[$i] = $v;
			}
			$i++;
		}
		return $result;
	}

	protected function selectrow($is_hash, $args) {
		$r = $this->query($args);
		if (pg_num_rows($r) == 0) return FALSE;
		$result = $this->fetchrow($is_hash, 0, $r);
		return $result;
	}

	protected function selectall($is_hash, $args) {
		$r = $this->query($args);
		$result = array();
		$rn = pg_num_rows($r);
		if ($rn > 0) $result = $this->fetchall($is_hash, $r);
		
		return $result;
	}

	public function selectrow_array() { $args = func_get_args(); return $this->selectrow(FALSE, $args); }
	public function selectall_array() { $args = func_get_args(); return $this->selectall(FALSE, $args); }
	public function selectrow_hash()  { $args = func_get_args(); return $this->selectrow(TRUE, $args); }
	public function selectall_hash()  { $args = func_get_args(); return $this->selectall(TRUE, $args); }

	public function escapeString($s) { return pg_escape_string($this->db, $s); }
	public function escapeByteA($s)  { return pg_escape_bytea($this->db, $s); }

	public function selectall_hash_index() {
		$args = func_get_args();
		$sql = array_shift($args);
		$key = array_shift($args);
		array_unshift($args, $sql);
		$rows = $this->selectall(TRUE, $args);
		$result = array();
		foreach($rows as $row) {
			$i = $row[$key];
			$result[$i] = $row;
		}
		return $result;
	}
};

endif/* DB_CLASS_INC */?>
