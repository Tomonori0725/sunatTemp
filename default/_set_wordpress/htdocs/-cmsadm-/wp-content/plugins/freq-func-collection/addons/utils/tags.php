<?php

/**
 * 各種URLを取得する.
 * 最後にスラッシュを付加しない。
 *
 * @param string $type URL種別
 *                     wp-top：WordPressのトップページにアクセスするためのURL
 *                     wp-url：WordPressがインストールされているURL
 *                     wp-content：wp-contentのURL
 *                     wp-parent：親テーマがあれば親テーマ、なければ現在のテーマのURL
 *                     wp-theme：現在のテーマのURL
 *
 * @return string URL
 */
function the_ffc_url($type = 'wp-top')
{
    echo esc_url(get_the_ffc_url($type));
}

/**
 * 各種URLを取得する.
 * 最後にスラッシュを付加しない。
 *
 * @param string $type URL種別
 *                     wp-top：WordPressのトップページにアクセスするためのURL
 *                     wp-url：WordPressがインストールされているURL
 *                     wp-content：wp-contentのURL
 *                     wp-parent：親テーマがあれば親テーマ、なければ現在のテーマのURL
 *                     wp-theme：現在のテーマのURL
 *
 * @return string URL
 */
function get_the_ffc_url($type = 'wp-top')
{
    global $ffCollection;
    return $ffCollection->utils->getUrl($type);
}

/**
 * 各種パスを取得する.
 * 最後にスラッシュを付加する。
 *
 * @param string $type パス種別
 *                     wp-path：WordPressがインストールされているパス
 *                     wp-content：wp-contentのパス
 *                     wp-parent：親テーマがあれば親テーマ、なければ現在のテーマのパス
 *                     wp-theme：現在のテーマのパス
 *
 * @return string パス
 */
function the_ffc_path($type)
{
    echo esc_html(get_the_ffc_path($type));
}

/**
 * 各種パスを取得する.
 * 最後にスラッシュを付加する。
 *
 * @param string $type パス種別
 *                     wp-path：WordPressがインストールされているパス
 *                     wp-content：wp-contentのパス
 *                     wp-parent：親テーマがあれば親テーマ、なければ現在のテーマのパス
 *                     wp-theme：現在のテーマのパス
 *
 * @return string パス
 */
function get_the_ffc_path($type)
{
    global $ffCollection;
    return $ffCollection->utils->getPath($type);
}
