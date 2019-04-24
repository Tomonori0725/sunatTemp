;jQuery(document).ready(function ($) {

    //////////////////////////////////////////////
    //
    //    「ページトップへ」の表示
    //
    //////////////////////////////////////////////
    $('.pageTopFix').hide();
    $(window).scroll(function () {
        var scrollHeight = $(document).height();
        var scrollPosition = $(window).height() + $(window).scrollTop();
        var topSpan = 100;    //上端から○pxで表示
        var bottomSpan = 100;    //下端から○pxで非表示

        //「上端から○pxで表示」
        if ($(this).scrollTop() > topSpan ) {
            $('.pageTopFix').fadeIn();
        } else {
            $('.pageTopFix').fadeOut();
        }
/*
        //「上端から○pxで表示」＋「下端から○pxで非表示」
        if ($(this).scrollTop() > topSpan ) {
            if ((scrollHeight - scrollPosition) < bottomSpan ) {
                $('.pageTopFix').fadeOut();
            } else {
                $('.pageTopFix').fadeIn();
            }
        } else {
            $('.pageTopFix').fadeOut();
        }
*/
    });

    ///////////////////////////////////////////////////
    //
    //    _widget フォルダから必要なjsだけコピペ
    //
    ///////////////////////////////////////////////////
    if ($('.wysiwyg').length) {
        tinymce.init({selector:'textarea.wysiwyg'});
    }


    //////////////////////////////////////////////
    //
    //    ハンバーガーメニュー：上から降りる
    //
    //////////////////////////////////////////////
    if ($('.meanMenu').length) {
        $('.meanMenu').meanmenu({
            // 高さをpxで指定。em % は不可　ヘッダ固定にしない場合は[0]
            meanNavPush: '50px', 
            
            // 指定した要素の内側直下にメニューを配置
            meanMenuContainer: 'body',
            
            // [×] 閉じるボタンのマークアップ
            meanMenuClose: '<span></span><span></span><span></span><em>CLOSE</em>',
            
            // [≡] メニューボタンのマークアップ
            meanMenuOpen: '<span></span><span></span><span></span><em>MENU</em>',
            
            // [≡] の表示位置
            meanRevealPosition: 'right',
            
            // メニュー位置調整「≡の表示位置」からのpx値
            meanRevealPositionDistance: '0' ,
            
            // [≡] の背景色
            meanRevealColour: '',
            
            // 表示する画面サイズ：●以下
            meanScreenWidth: '767',
            
            // 子要素を表示する
            meanShowChildren: true,
            
            // 子要素を折りたたむ
            meanExpandableChildren: true,
            
            // メニューを閉じた時のアイコン
            meanExpand: '', 
            
            // メニューを開いた時のアイコン
            meanContract: '',
            
            // ClassIDを削除する
            meanRemoveAttrs: false,
            
            // Pageクリック時に閉じる【.mean-nav:not(.meanMenuAcc) ul > li > a:first-child】
            onePage: true,
            
            // メニュー内で消したい要素
            removeElements: '', 
            
            // 表示形式
            meanDisplay: 'block'
        });
    }

    //////////////////////////////////////////////
    //
    //   スクロール関連　※高さ揃える処理より後に入れる
    //
    //////////////////////////////////////////////
    // ページトップへ
    $('.pageTopFix, .pageTop, .pageTopBlock').targetScroller({
        target: 'html',
        duration: 800,
        easing: 'easeOutQuint'
    });
    // サイト内リンク
    $('a[href^="#"]').targetScroller({
        duration: 800,
        easing: 'easeOutQuint',
        header: 'header.header'
    });
    // サイト内リンク
    $('a[href^="/#"]').targetScroller({
        duration: 800,
        easing: 'easeOutQuint',
        header: 'header.header'
    });
    // ハッシュによるロード時のスクロール
    $.targetScroller({
        duration: 800,
        easing: 'easeOutQuint',
        header: 'header.header'
    });

});

///////////////////////////////////
//
//    「js-loader」はここに転記
//
///////////////////////////////////


