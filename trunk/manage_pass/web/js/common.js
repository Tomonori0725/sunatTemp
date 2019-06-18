;jQuery(document).ready(function ($) {


    //////////////////////////////////////////////
    //
    //    パスワードの表示
    //
    //////////////////////////////////////////////
    if($('#form_password').prop('checked')){
        $('#form_password').prop('checked', false);
    }
    $('#Toggle-secret').click(function(){
        if($(this).prop('checked')){
            $(this).prev('input').attr('type', 'text');
        }else{
            $(this).prev('input').attr('type', 'password');
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
    });

    //////////////////////////////////////////////
    //
    //    削除するか確認する
    //
    //////////////////////////////////////////////
    $('.actionDelete').submit(function(){
        var result = confirm('このアカウントを削除しますか。');
        return result;
    });

    //////////////////////////////////////////////
    //
    //    パスワード作成
    //
    //////////////////////////////////////////////
    if($('#form_password').length){
        $('#CreatePassword').click(function(){
            create_pass('form_password');
        });
    }
    if($('#appbundle_account_password').length){
        $('#CreatePassword').click(function(){
            create_pass('appbundle_account_password');
        });
    }



});

//////////////////////////////////////////////
//
//    パスワード作成
//
//////////////////////////////////////////////
function create_pass(areaId){
    $.ajax({
        type: 'POST',
        url: '/lib/ajax_getPass.php',
    })
    // Ajaxリクエストが成功した時発動
    .done( (data) => {
        $(areaId).attr('value', data);
        document.getElementById(areaId).value = data;
    })
    // Ajaxリクエストが失敗した時発動
    .fail( (data) => {
        console.log(data);
    })
}
