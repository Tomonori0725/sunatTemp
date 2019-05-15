<?php
$values = get_option('{{KEY}}', '');
if ($values) :
    ?>
    <ul>
        <?php foreach ($values as $value) : ?>
            <li><?php echo wp_get_attachment_link($value); ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
