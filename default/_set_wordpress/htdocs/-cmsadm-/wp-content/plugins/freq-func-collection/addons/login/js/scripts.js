jQuery(function ($) {
    var $userLabel = $('label[for="user_login"'),
        $user = $('#user_login'),
        $userIcon = $('<span>').addClass('dashicons dashicons-admin-users'),
        $passwordLabel = $('label[for="user_pass"'),
        $password = $('#user_pass'),
        $passwordIcon = $('<span>').addClass('dashicons dashicons-admin-network');

    $user.attr('placeholder', $userLabel.text().trim());
    $userLabel.empty().append($user);
    $user.before($userIcon);

    $password.attr('placeholder', $passwordLabel.text().trim());
    $passwordLabel.empty().append($password);
    $password.before($passwordIcon);
});
