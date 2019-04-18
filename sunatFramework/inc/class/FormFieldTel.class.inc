<?php declare(encoding = 'UTF-8');
if (!defined('FORM_FIELD_TEL_CLASS_INC')): define('FORM_FIELD_TEL_CLASS_INC', 1);

class FormFieldTel extends FormField {
	const FIXED       =     1;
	const POCKETBELL  =     2; // ポケベル       020-CDE-FGHJK
	const IP          =     4; // IP電話         050-CDEF-GHJK
	const FMC         =     8; // FMC電話番号    060-CDEF-GHJK
	const PHS         =    16; // PHS            070-CDE-FGHJK
	const MOBILE      =    32; // 携帯電話       080-CDE-FGHJK
	                           //                090-CDE-FGHJK
	const MOBILE_PHP  =    48; // PHP + 携帯
	const FREE        =    64; // フリーダイヤル 0120-DEF-GHJ
	                           //                0800-DEF-GHJ
	const TOTAL       =   128; // 統一番号       0570-DEF-GHJ
	const Q2          =   256; // Q2             0990-DEF-GHJ
	const ALL         =   511; // すべての番号を許可
                     
	const STRICT      =   512; // 番号のチェックを厳しく
	const HYPHENATION =  1024; // ハイフンで区切る

	protected $tel_type;
	protected $info;

	public function __construct($tel_type, $flags = 0) {
		parent::__construct($flags);
		$this->tel_type = $tel_type;
		$this->info = NULL;
	}

	public function pre_convert($value, array $file) {
		$value = parent::pre_convert($value, $file);
		if (is_string($value)) {
			$value = preg_replace('/[^\d]/s', '', $value);
		}
		return $value;
	}

	public function check_value($value, array &$errors) {
		parent::check_value($value, $errors);
		if (is_null($value)) {
			// NOP
		}
		elseif (!is_string($value)) {
			$errors['not_tel_number'] = TRUE;
		}
		else {
			if (!preg_match('/^0([1-9]\d{0,3})(?:-(\d{1,4})-|\((\d{1,4})\)|(\d{1,4}))(\d{3,5})$/s',$value, $m)) {
				$errors['bad_format'] = TRUE;
			}
			else {
				$value = $m[1].$m[2].$m[3].$m[4].$m[5];
				$t = (int)substr($value, 0, 5);
				$d = (int)substr($value, 5, 1);
				$ok = false;
				$this->info = self::load_data($t, $d);
				for ($i = 0; $i < 16; $i++) if (($this->tel_type & (1 << $i)) && $this->info->type == $i + 1) $ok = true;
				if (strlen($value) != $this->info->high_len + $this->info->mid_len + $this->info->low_len
				|| (($this->tel_type & self::STRICT) && !$this->info->used) || !$ok) {
					$errors['bad_format'] = TRUE;
				}
			}
		}
	}

	public function convert_value($value) {
		if (is_null($value)) return NULL;
		preg_match('/^(0[1-9]\d{0,3})(?:-(\d{1,4})-|\((\d{1,4})\)|(\d{1,4}))(\d{3,5})$/s',$value, $m);
		$value = $m[0].$m[1].$m[2].$m[3].$m[4].$m[5];
		if (!($this->tel_type & self::HYPHENATION)) return $value;
		$h = $this->info->high_len;
		$m = $this->info->mid_len;
		$l = $this->info->low_len;
		if ($l > 4) { $m += $l - 4; $l = 4; }
		return substr($value, 0, 1 + $h).'-'.substr($value, 1 + $h, $m).'-'.substr($value, 1 + $h + $m, $l);
	}

	protected static function load_data($idx, $d) {
		$fp = fopen('tel.dat', 'rb', TRUE);
		if ($fp === FALSE) return FALSE;

		fseek($fp, 26 + $idx * 2, SEEK_SET);
		list(, $dat) = unpack('n', fread($fp, 2));

		$pattern = $dat & 31;
		$used = (bool)($dat & (1 << (($d % 10) + 6)));

		fseek($fp, 200026 + $pattern * 2, SEEK_SET);
		$dat = fread($fp, 2);
		list(, $dat) = unpack('n', $dat);

		fclose($fp);

		$type     = ($dat >> 9) & 31;
		$high_len = ($dat >> 6) &  7;
		$mid_len  = ($dat >> 3) &  7;
		$low_len  = ($dat >> 0) &  7;

		return (object)array(
			'type'     => $type,
			'high_len' => $high_len,
			'mid_len'  => $mid_len,
			'low_len'  => $low_len,
			'used'     => $used,
		);
	}
};

endif/* FORM_FIELD_TEL_CLASS_INC */?>
