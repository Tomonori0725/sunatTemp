<?php

switch ($setting['type']) {
    case 'checkbox':
        if ($this->exists($key, $arrValue)
            && $arrValue[$key]
        ) {
            $checked = true;
        } else {
            $checked = false;
        }
        ?>
        <input type="checkbox" id="<?php echo esc_attr($name); ?>" name="<?php echo esc_attr($name); ?>" value="1"<?php checked($checked, true); ?>>
        <?php if (array_key_exists('checklabel', $setting)) : ?>
            <label for="<?php echo esc_attr($name); ?>"><?php echo esc_html($setting['checklabel']); ?></label>
        <?php endif;
        break;
    case 'textarea':
        if ($this->exists($key, $arrValue)
            && is_string($arrValue[$key])
        ) {
            $val = $arrValue[$key];
        } else {
            $val = '';
        }
        ?>
        <textarea class="widefat autosize" name="<?php echo esc_attr($name); ?>" rows="5"><?php echo esc_html($val); ?></textarea>
        <?php
        break;
    case 'image':
        if (!$this->exists($key, $arrValue)) {
            $arrValue[$key] = array();
        }
        uploadImageTemplate(array(
            'imageName' => $name,
            'imageId'   => $arrValue[$key],
            'multi'     => 0,
            'sortable'  => 0,
            'filetype'  => 'image',
            'size'      => 'medium'
        ));
        break;
    case 'color':
        if ($this->exists($key, $arrValue)
            && is_string($arrValue[$key])
        ) {
            $val = $arrValue[$key];
        } else {
            $val = '';
        }
        ?>
        <input type="text" id="<?php echo esc_attr($name); ?>" class="colorpicker" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($val); ?>">
        <?php
        break;
    case 'text':
    default:
        if ($this->exists($key, $arrValue)
            && is_string($arrValue[$key])
        ) {
            $val = $arrValue[$key];
        } else {
            $val = '';
        }
        ?>
        <input type="text" class="widefat" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($val); ?>">
        <?php
        break;
}
