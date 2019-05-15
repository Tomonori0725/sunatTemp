<?php
$options = get_ffc_menupage_options('{{SLUG}}', '{{KEY}}');
$values = get_option('{{KEY}}', '');
if ($values) :
    ?>
    <ul>
        <?php foreach ($values as $value) : ?>
            <li><?php echo esc_html($options[$value]); ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
