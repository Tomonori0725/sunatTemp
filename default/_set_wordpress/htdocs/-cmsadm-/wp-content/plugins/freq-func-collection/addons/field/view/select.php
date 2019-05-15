<select name="<?php echo esc_attr($name); ?>">
    <?php if ($setting['unselected']) : ?>
        <option value=""><?php echo esc_html($setting['unselected']); ?></option>
    <?php endif; ?>
    <?php foreach ($setting['options'] as $key => $option) : ?>
        <option value="<?php echo esc_attr($key); ?>"<?php selected($value, $key); ?>><?php echo esc_html($option); ?></option>
    <?php endforeach; ?>
</select>
<?php if ($setting['description']) : ?>
<p class="description"><?php echo $setting['description']; ?></p>
<?php endif; ?>
