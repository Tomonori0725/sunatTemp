<?php declare(encoding = 'UTF-8');
/*----------------------------------------------------------------------------
機能概要:
	注目商品のチェックを行う
機能説明:

引数:

戻り値:
	エラーがひとつでも見つかれば true 、見つからなければ false
----------------------------------------------------------------------------*/
if (!defined('FORM_FIELD_STRING_GOODS_CODE_CLASS_INC')): define('FORM_FIELD_STRING_GOODS_CODE_CLASS_INC', 1);

class FormFieldStringGoodsCode extends FormFieldString {

	protected $db;
	protected $ary_tmp;

	public function __construct($flags = 0, $pattern = NULL, $min = NULL, $max = NULL, db $db) {
		parent::__construct($flags, $pattern, $min, $max);
		$this->db = $db;
		$this->ary_tmp = array();
	}

	public function check_value($value, array &$errors) {
		parent::check_value($value, $errors);
		if (!$errors) {
$sql =<<<SQL
SELECT
	goods.id, goods.name, goods_code.code
FROM
	goods INNER JOIN goods_code ON goods_code.id = goods.goods_code
SQL;
			$rows = $this->db->selectall_hash($sql);

			//商品コードTrim&Unique
			$ary_code = $this->code_reformat($value);

			//商品チェック
			$error_flg = 0;
			foreach ($ary_code as $code) {
				if ($code == '') {
					continue;
				}
				$item_not_found = 1;
				$code_error = !preg_match(PRODUCT_CODE_FORMAT_MATCH, $code);	//コードフォーマット照合
				foreach ($rows as $idx => $row) {
					if ($row['code'] == $code) {
						//見つかった
						$item_not_found = 0;
						$this->ary_tmp[] = array(
							'goods_code' => $row['code'],
							'goods_name' => $row['name'],
							'pickup_item_input:error' => array()
						);
						break;
					}
				}
				if ($item_not_found) {
					//見つからない
					$this->ary_tmp[] = array(
						'goods_code' => $code,
						'goods_name' => NULL,
						'pickup_item_input:error' => array(
							'code_invalid' => ($code_error ? TRUE : FALSE),
							'no_item' => (!$code_error ? TRUE : FALSE)
						)
					);
					$error_flg = 1;
				}
			}
			if ($error_flg) {
				$errors['pickup_item_list'] = array('row_pickup_item' => $this->ary_tmp);
			}
		}
	}

	public static function code_reformat($value)
	{
		$ary_code = array();
		$ary_code = array_unique(array_map(function($val) {
			return trim($val);
		}, explode(PHP_EOL, $value)));

		return $ary_code;
	}
};

endif/* FORM_FIELD_STRING_GOODS_CODE_CLASS_INC */?>
