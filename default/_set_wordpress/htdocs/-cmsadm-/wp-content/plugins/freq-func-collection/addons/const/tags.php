<?php

/**
 * configに設定された定数を表示する.
 *
 * @param string $key 取得する定数のキー
 *
 * @return void
 */
function the_ffc_const($key)
{
    echo esc_html(get_the_ffc_const($key));
}

/**
 * configに設定された定数を取得する.
 *
 * @param string $key 取得する定数のキー
 *
 * @return mixed 定数 / キーが存在しなければnull
 */
function get_the_ffc_const($key)
{
    global $ffCollection;
    return $ffCollection->const->getConst($key);
}

/**
 * dataからデータを表示する.
 *
 * @param string $type データタイプ
 * @param string $key  データキー
 *
 * @return void
 */
function the_ffc_data($type, $key = null)
{
    echo esc_html(get_the_ffc_data($type, $key));
}

/**
 * dataからデータを取得する.
 *
 * @param string $type データタイプ
 * @param string $key  データキー
 *
 * @return mixed データ
 */
function get_the_ffc_data($type, $key = null)
{
    global $ffCollection;
    return $ffCollection->const->getData($type, $key);
}
