<?php
$options = get_ffc_post_options(get_the_ID(), '{{KEY}}');
$values = get_post_meta(get_the_ID(), '{{KEY}}');
if (!empty($values)) :
    ?>
    <ul>
        <?php foreach ($values as $value) : ?>
            <li><?php echo esc_html($options[$value]); ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
