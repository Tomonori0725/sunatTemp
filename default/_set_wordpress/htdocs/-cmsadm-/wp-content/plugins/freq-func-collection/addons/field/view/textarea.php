<textarea id="<?php echo esc_attr($setting['id']); ?>" class="widefat autosize" name="<?php echo esc_attr($name); ?>" rows="<?php echo esc_attr($setting['rows']); ?>" placeholder="<?php echo esc_attr($setting['placeholder']); ?>"><?php echo "\n" . esc_html($value); ?></textarea>
<?php if ($setting['description']) : ?>
<p class="description"><?php echo $setting['description']; ?></p>
<?php endif; ?>
