<?php

require 'includes/tags/tags.php';
require 'includes/class/base/ThemeClassBase.php';
require 'includes/class/ThemeDataList.php';
require 'includes/class/ThemeRefactoring.php';
require 'includes/class/ThemeBasic.php';
require 'includes/class/ThemeLogin.php';
require 'includes/class/ThemePostType.php';
require 'includes/class/ThemeMenuPage.php';
require 'includes/class/ThemeFields.php';
require 'includes/class/ThemeMetabox.php';
require 'includes/class/ThemeWidgets.php';
require 'includes/class/ThemeResource.php';

// 特定の文字列をエンティティ化
function antispamFunc($atts)
{
    extract(shortcode_atts(array(
        'email' => get_bloginfo('admin_email')
    ), $atts));

    return antispambot($email);
}
add_shortcode('antispam', 'antispamFunc');

// 更新通知非表示
// コア
remove_action('wp_version_check', 'wp_version_check');
remove_action('admin_init', '_maybe_update_core');
add_filter('pre_site_transient_update_core', 'remove_core_updates');
// プラグイン
remove_action('load-update-core.php', 'wp_update_plugins');
add_filter('pre_site_transient_update_plugins', 'remove_core_updates');
// テーマ(不要だがいちおう)
remove_action('load-update-core.php', 'wp_update_themes');
add_filter('pre_site_transient_update_themes', 'remove_core_updates');

function remove_core_updates()
{
    global $wp_version;
    return (object)array(
        'last_checked' => time(),
        'version_checked' => $wp_version,
        'updates' => array()
    );
}
