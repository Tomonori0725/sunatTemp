<?php
$values = get_option('{{KEY}}', '');
if ($values) :
    ?>
    <ul>
        <?php foreach ($values as $value) : ?>
            <li><?php echo wp_get_attachment_image($value, 'large', true); ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
