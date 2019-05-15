<?php
$options = get_ffc_post_options(get_the_ID(), '{{KEY}}');
$value = get_post_meta(get_the_ID(), '{{KEY}}', true);
if (strlen($value)) {
    echo esc_html($options[$value]);
}
?>
