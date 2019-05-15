<div class="widefat">
    <?php
    $arrField = array();
    foreach ($setting['options'] as $key => $option) {
        $arrField[] = '<input type="radio" id="' . esc_attr($setting['id'] . '_' . $key) . '" name="' . esc_attr($name) . '" value="' . esc_attr($key) . '"' . checked($value, $key, false) . '><label for="' . esc_attr($setting['id'] . '_' . $key) . '">' . esc_html($option) . '</label>';
    }
    echo implode($setting['delimiter'], $arrField);
    ?>
</div>
<?php if ($setting['description']) : ?>
<p class="description"><?php echo $setting['description']; ?></p>
<?php endif; ?>
