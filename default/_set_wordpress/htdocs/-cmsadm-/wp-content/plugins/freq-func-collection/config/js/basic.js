;jQuery(function ($) {
    $('#addMenuFieldButton').on('click', function (e) {
        var $element = $($('#templateMenuFieldItem').html());
        $element.appendTo($(this).prev('table'));
    });
    $('body').on('click', '.removeItemButton', function (e) {
        $(this).parents('tr').first().remove();
    });
});
