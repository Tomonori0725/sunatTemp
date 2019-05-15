<?php
$values = get_term_meta(get_queried_object_id(), '{{KEY}}');
if (!empty($values)) :
    ?>
    <ul>
        <?php foreach ($values as $value) : ?>
            <li><?php echo wp_get_attachment_link($value); ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
