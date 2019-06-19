;jQuery(function ($) {

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
        .then(
            function (date) {
                $(areaId).attr('value', date);
                document.getElementById(areaId).value = date;
            },
            function () {
                console.log(date);
        });
    }

});
