jQuery(function ($) {
    if ('undefine' !== loginLogo) {
        $logo = $('<img>').attr('src', loginLogo);
        $('#login h1 a').addClass('customLogo').html($logo);
    }
});
