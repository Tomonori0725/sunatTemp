<?php
$options = get_ffc_term_options(get_queried_object_id(), '{{KEY}}');
$values = get_term_meta(get_queried_object_id(), '{{KEY}}');
if (!empty($values)) :
    ?>
    <ul>
        <?php foreach ($values as $value) : ?>
            <li><?php echo esc_html($options[$value]); ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
