;jQuery(function ($) {
    $('#addConstFieldButton').on('click', function (e) {
        var $element = $($('#templateConstFieldItem').html());
        $element.appendTo($(this).prev('table'));
    });
    $('body').on('click', '.removeItemButton', function (e) {
        $(this).parents('tr').first().remove();
    });
});
