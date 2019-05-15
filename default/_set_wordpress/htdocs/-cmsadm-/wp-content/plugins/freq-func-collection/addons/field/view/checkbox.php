<div class="widefat">
    <?php
    $arrField = array();
    foreach ($setting['options'] as $key => $option) {
        if ($setting['multiply']) {
            if (!is_array($value)) {
                $value = array($value);
            }
            $value = array_map('strval', $value);
            $checked = in_array(strval($key), $value, true);
        } else {
            $checked = strval($key) === strval($value);
        }
        $arrField[] = '<input type="checkbox" id="' . esc_attr($setting['id'] . '_' . $key) . '" name="' . esc_attr($name) . '" value="' . esc_attr($key) . '"' . checked($checked, true, false) . '><label for="' . esc_attr($setting['id'] . '_' . $key) . '">' . esc_html($option) . '</label>';
    }
    echo implode($setting['delimiter'], $arrField);
    ?>
</div>
<?php if ($setting['description']) : ?>
<p class="description"><?php echo $setting['description']; ?></p>
<?php endif; ?>
