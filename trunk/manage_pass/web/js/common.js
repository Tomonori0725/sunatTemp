;jQuery(document).ready(function ($) {



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


function create_pass(areaId){
    //使用文字の定義
    var str = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    
    //桁数の定義(8文字以上16文字)
    var len = Math.floor(Math.random() * 8) + 8;

    //ランダムな文字列の生成
    var result = "";
    for(var i=0;i<len;i++){
        result += str.charAt(Math.floor(Math.random() * str.length));
    }
    document.getElementById(areaId).value = result;
}
