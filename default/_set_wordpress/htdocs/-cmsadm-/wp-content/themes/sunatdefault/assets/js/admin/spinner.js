;jQuery(function ($) {
  $('.numSpinner').each(function() {
    var $this = $(this);
    var args = {};
    var max = $this.data('numMax');
    if (typeof max !== 'undefined') {
      args.max = max;
    }
    var min = $this.data('numMin');
    if (typeof min !== 'undefined') {
      args.min = min;
    }
    var step = $this.data('numStep');
    if (typeof step !== 'undefined') {
      args.step = step;
    }
    $(this).spinner(args);
  });
});
