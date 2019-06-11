;jQuery(document).ready(function ($) {


    $('#Toggle-secret').click(function(){
        var input = $(this).prev('input');
        if(input.attr('type') == 'password'){
            input.attr('type', 'text');
        }else{
            input.attr('type', 'password');
        }
    });

    function add_password(){
        document.getElementById("").innerText = create_pass();
    }


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

});

function create_pass(areaId){
    //使用文字の定義
    var str = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    
    //桁数の定義(8文字以上16文字)
    var len = Math.floor(Math.random() * 8) + 8;
    console.log(len);

    //ランダムな文字列の生成
    var result = "";
    for(var i=0;i<len;i++){
        result += str.charAt(Math.floor(Math.random() * str.length));
    }
    document.getElementById(areaId).value = result;
}
