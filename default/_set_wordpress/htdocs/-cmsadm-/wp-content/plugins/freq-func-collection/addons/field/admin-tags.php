<?php

function uploadImageTemplate($options = array())
{
    $options = array_merge(array(
        'imageName' => '',
        'textName'  => '',
        'imageId'   => array(),
        'text'      => array(),
        'multi'     => true,
        'sortable'  => true,
        'filetype'  => 'image',
        'size'      => 'medium',
        'tag'       => 'div',
        'button'    => __('Add Image', FFCOLLECTION_PLUGIN_DIR_NAME)
    ), $options);
    echo '<' . esc_html($options['tag']) . ' class="uploadImageGroup" data-upload-multi="' . var_export($options['multi'], true) . '">';
    if ('image' === $options['filetype']) {
        ?>
        <ul class="uploadImageList" data-upload-name="<?php echo esc_attr($options['imageName']); ?>" data-upload-text="<?php echo esc_attr($options['textName']); ?>" data-upload-sortable="<?php var_export($options['sortable']); ?>" data-file-type="<?php echo esc_attr($options['filetype']); ?>" data-upload-size="<?php echo esc_attr($options['size']); ?>">
            <?php
            if ($options['imageId']) {
                foreach ($options['imageId'] as $key => $imageId) {
                    $srcList = wp_get_attachment_image_src($imageId, $options['size']);
                    if ($srcList && $srcList[0]) {
                        ?>
                        <li class="image" id="image_<?php echo esc_attr($imageId); ?>">
                            <div class="imageWrap">
                                <a class="removeImageButton dashicons dashicons-dismiss"></a>
                                <div><img src="<?php echo esc_attr($srcList[0]); ?>" width="<?php echo esc_attr($srcList[1]); ?>" height="<?php echo esc_attr($srcList[2]); ?>" class="sortHandle"></div>
                                <input type="hidden" name="<?php echo esc_attr($options['imageName']); ?>[]" value="<?php echo esc_attr($imageId); ?>">
                                <?php if (strlen($options['textName'])) : ?>
                                    <input type="text" class="widefat" name="<?php echo esc_attr($options['textName']); ?>[]" value="<?php echo esc_attr($options['text'][$key]); ?>">
                                <?php endif; ?>
                            </div>
                        </li>
                        <?php
                    }
                }
            } else {
                ?>
                <li class="noImage"><?php _e('There is no addition.', FFCOLLECTION_PLUGIN_DIR_NAME) ?></li>
                <?php
            }
            ?>
        </ul>
        <a class="uploadImageButton button"><?php echo esc_html($options['button']); ?></a>
        <?php
    } else {
        ?>
        <ul class="uploadImageList" data-upload-name="<?php echo esc_attr($options['imageName']); ?>" data-upload-text="<?php echo esc_attr($options['textName']); ?>" data-upload-sortable="<?php var_export($options['sortable']); ?>" data-file-type='<?php echo esc_attr($options['filetype']); ?>'>
            <?php
            if ($options['imageId']) {
                foreach ($options['imageId'] as $key => $imageId) {
                    $url = wp_get_attachment_url($imageId);
                    if ($url) {
                        ?>
                        <li class="image" id="image_<?php echo esc_attr($imageId); ?>">
                            <div class="imageWrap">
                                <a class="removeImageButton dashicons dashicons-dismiss"></a>
                                <span class="sortHandle"><?php echo esc_html(wp_basename($url)); ?></span>
                                <input type="hidden" name="<?php echo esc_attr($options['imageName']); ?>[]" value="<?php echo esc_attr($imageId); ?>">
                                <?php if (strlen($options['textName'])) : ?>
                                    <input type="text" class="widefat" name="<?php echo esc_attr($options['textName']); ?>[]" value="<?php echo esc_attr($options['text'][$key]); ?>">
                                <?php endif; ?>
                            </div>
                        </li>
                        <?php
                    }
                }
            } else {
                ?>
                <li class="noImage"><?php _e('There is no addition.', FFCOLLECTION_PLUGIN_DIR_NAME) ?></li>
                <?php
            }
            ?>
        </ul>
        <a class="uploadImageButton button"><?php echo esc_html($options['button']); ?></a>
        <?php
    }
    echo '</' . esc_html($options['tag']) . '>';
}
