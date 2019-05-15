;jQuery(function ($) {
    var defaultColor = '#000000';
    var $colorpicker = $('.colorpicker');
    if ($colorpicker.length) {
        $('.viewColor').on('click', function(e) {
            if ($(this).next().data('view') == false) {
                $(this).find('span').text('閉じ').end().next().iris('toggle');
                $(this).next().data('view', true)
            } else {
                $(this).find('span').text('開き').end().next().iris('toggle');
                $(this).next().data('view', false)
            }
        });
        $colorpicker.iris({
            border: true,
            width: 400,
            palettes: ['#000000', '#ffffff', '#ff0000', '#fff000', '#00ff00', '#00ffff', '#0000ff', '#ff00ff'],
            change: function(e, ui) {
                setColor($(this));
            }
        });

        $colorpicker.each(function () {
            var $this = $(this);
            if ($this.val() == '') {
                $this.iris('color', defaultColor);
            } else {
                setColor($this);
            }
        });
    }

    function setColor($elem)
    {
        $elem.prev().css({
            'background-color': $elem.iris('color')
        });
    }
});
