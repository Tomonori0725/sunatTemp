<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<?php get_template_part('parts/head'); ?>
<body <?php body_class(); ?>>
<div class="pageTopFix">ページの先頭へ</div>

<div id="SlideMenu" class="slideRight">
    <nav class="slideNavi">
        <div id="SlideHeader"><a href="/">ホーム</a></div>
        <div id="SlideBody">
            <div id="SlideContent">
                <?php
                    wp_nav_menu(array(
                        'theme_location' => 'global1',
                        'container'      => false,
                        'items_wrap'     => '<ul>%3$s</ul>'
                    ));
                ?>
            </div>
        </div>
    </nav>
</div>

<header class="header">
    <div class="contents">
        <?php if (is_front_page()) : ?>
            <h1 class="logo"><a href="<?php echo getUrl('site-top'); ?>/"><img src="<?php echo getUrl('wp-style'); ?>/assets/img/header/logo.png" alt="<?php bloginfo('name'); ?>" width="304" height="55"></a></h1>
        <?php else : ?>
            <p class="logo"><a href="<?php echo getUrl('site-top'); ?>/"><img src="<?php echo getUrl('wp-style'); ?>/assets/img/header/logo.png" alt="<?php bloginfo('name'); ?>" width="304" height="55"></a></p>
        <?php endif; ?>
        <a id="SlideToggle">MENU</a>
    </div>
</header>

<nav class="gnav">
    <div class="contents">
        <?php
            wp_nav_menu(array(
                'theme_location' => 'global1',
                'container'      => false,
                'items_wrap'     => '<ul>%3$s</ul>'
            ));
        ?>
    </div>
</nav>

<div id="ContentsBase">
    <div class="wallBelt zero">
        <div class="contents">
