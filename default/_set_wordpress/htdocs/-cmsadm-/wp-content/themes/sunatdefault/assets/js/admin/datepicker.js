;jQuery(function ($) {
    $('.datepicker').each(function () {
        var $this = $(this);
        var format = $this.data('dateFormat');
        if ('undefined' === typeof format) {
            format = 'yy年mm月dd日';
        }
        $this.datepicker({
            showMonthAfterYear: true,
            changeYear: true,
            changeMonth: true,
            yearSuffix: '年',
            dateFormat: format,
            dayNames: ['日曜日','月曜日','火曜日','水曜日','木曜日','金曜日','土曜日'],
            dayNamesMin: ['日','月','火','水','木','金','土'],
            dayNamesShort: ['日曜','月曜','火曜','水曜','木曜','金曜','土曜'],
            monthNames: ['1月','2月','3月','4月','5月','6月','7月','8月','9月','10月','11月','12月'],
            monthNamesShort: ['1月','2月','3月','4月','5月','6月','7月','8月','9月','10月','11月','12月']
        });
    });
});
