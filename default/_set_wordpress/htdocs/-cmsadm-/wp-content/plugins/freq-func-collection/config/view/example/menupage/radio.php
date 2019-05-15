<?php
$options = get_ffc_menupage_options('{{SLUG}}', '{{KEY}}');
$value = get_option('{{KEY}}', '');
if (strlen($value)) {
    echo esc_html($options[$value]);
}
?>
