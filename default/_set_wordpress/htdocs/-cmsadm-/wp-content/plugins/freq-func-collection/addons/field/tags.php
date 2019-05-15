<?php

/**
 * カスタムフィールドの値を表示する.
 *
 * @param string $name 取得するカスタムフィールド名
 *
 * @return void
 */
function the_ffc_field($name)
{
    global $post;
    $value = get_the_ffc_field($name);
    if (!is_null($value)) {
        if (!$post->metaFields[$name]['setting']['html']) {
            // HTMLでなければエスケープして改行を<br>にして出力する
            $value = nl2br(esc_html($value));
        }
        echo $value;
    }
}

/**
 * カスタムフィールドの値を取得する.
 *
 * @param string $name 取得するカスタムフィールド名
 *
 * @return mixed 定数 / キーが存在しなければnull
 */
function get_the_ffc_field($name)
{
    global $post;
    if (property_exists($post, 'metaFields')
        && array_key_exists($name, $post->metaFields)
    ) {
        return $post->metaFields[$name]['value'];
    }
    return null;
}

function get_the_ffc_map($latlng = array(), $height = 0)
{
    global $ffCollection;
    $ffCollection->field->getElementGooglemap($latlng, $height);
}
