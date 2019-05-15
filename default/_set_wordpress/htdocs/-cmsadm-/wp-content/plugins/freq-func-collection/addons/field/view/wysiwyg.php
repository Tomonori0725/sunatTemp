<?php
wp_editor($value, $setting['id'], array(
    'textarea_name' => $setting['name']
));
?>
<?php if ($setting['description']) : ?>
<p class="description"><?php echo $setting['description']; ?></p>
<?php endif; ?>
