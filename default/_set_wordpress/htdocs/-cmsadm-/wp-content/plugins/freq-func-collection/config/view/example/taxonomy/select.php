<?php
$options = get_ffc_term_options(get_queried_object_id(), '{{KEY}}');
$value = get_term_meta(get_queried_object_id(), '{{KEY}}', true);
if (strlen($value)) {
    echo esc_html($options[$value]);
}
?>
