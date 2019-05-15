;jQuery(function ($) {
    $('.viewDescription').on('click', function () {
        var $target = $(this).next('.funcDescription');
        if ($target.is(':visible')) {
            $(this).text('説明を表示する');
            $target.slideUp();
        } else {
            $(this).text('説明を隠す');
            $target.slideDown();
        }
    });
});
