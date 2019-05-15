<?php
$values = get_post_meta(get_the_ID(), '{{KEY}}');
if (!empty($values)) :
    ?>
    <ul>
        <?php foreach ($values as $value) : ?>
            <li><?php echo wp_get_attachment_image($value, 'large', true); ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
