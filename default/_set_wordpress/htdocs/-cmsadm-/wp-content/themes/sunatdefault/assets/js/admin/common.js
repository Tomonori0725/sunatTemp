// ページトップ
jQuery(function ($) {
    // textareaの高さ自動調整
    if ($('textarea.autosize').length) {
        autosize($('textarea.autosize'));
    }

    // 必須項目チェック
    $('#post').submit(function (e) {
        if ($('.required').length) {
            var isError = false,
                color;

            $('.required').each(function () {
                if (isEmpty(this)) {
                    $(this).addClass('error');
                    isError = true;
                } else {
                    $(this).removeClass('error');
                }
            });
            if (isError) {
                alert('必須項目が未入力もしくは未選択です。');
                return false;
            }
        }
    });

    // 未入力・未選択チェック
    // TODO:これではチェックできないものもある
    function isEmpty(element) {
        var value = $(element).val();
        if (value.length) {
            return false;
        } else {
            return true;
        }
    }
});
