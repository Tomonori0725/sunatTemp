;jQuery(function ($) {
    function getNextNo($element, type) {
        var noList = [],
            no = 0,
            $targetList = $element.prev('.' + type + 'List');
        $targetList.find('.' + type + 'Item').each(function () {
            noList.push($(this).data(type + 'No'));
        });
        if (noList.length > 0) {
            no = Math.max.apply(null, noList) + 1;
        }
        return no;
    }

    $('#addAreaFieldButton').on('click', function (e) {
        var $element = $($('#templateAreaFieldItem')
            .html()
            .replace(/\{\{AREA_NO\}\}/g, getNextNo($(this), 'area')));
        $element.appendTo($(this).prev('table'));
    });
    $('body').on('click', '.removeItemButton', function (e) {
        $(this).parents('tr').first().remove();
    });
});
