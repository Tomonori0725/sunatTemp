<input type="text" id="<?php echo esc_attr($setting['id']); ?>" class="widefat" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>" placeholder="<?php echo esc_attr($setting['placeholder']); ?>">
<?php if ($setting['description']) : ?>
<p class="description"><?php echo $setting['description']; ?></p>
<?php endif; ?>
