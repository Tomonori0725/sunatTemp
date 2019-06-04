;jQuery(document).ready(function ($) {


    $('#Toggle-secret').click(function(){
        var input = $(this).prev('input');
        if(input.attr('type') == 'password'){
            input.attr('type', 'text');
        }else{
            input.attr('type', 'password');
        }
    });





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



});

