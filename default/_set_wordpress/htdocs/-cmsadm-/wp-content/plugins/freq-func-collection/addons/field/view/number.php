<div class="spinner-container widefat"<?php echo $min . $max . $step; ?>>
    <div class="button button-primary dashicons dashicons-minus spinner-down"></div>
    <input type="text" id="<?php echo esc_attr($setting['id']); ?>" class="spinner-field" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>">
    <div class="button button-primary dashicons dashicons-plus spinner-up"></div>
</div>
<?php if ($setting['description']) : ?>
<p class="description"><?php echo $setting['description']; ?></p>
<?php endif; ?>
