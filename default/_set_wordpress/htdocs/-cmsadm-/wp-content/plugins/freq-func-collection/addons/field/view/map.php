<div class="mapField" data-latlng="[<?php echo esc_attr(implode(',', $setting['center'])); ?>]">
    <p>
        <?php _e('Please drag the marker.', FFCOLLECTION_PLUGIN_DIR_NAME); ?><br>
        <?php _e('Use the mouse wheel or [<span class="dashicons dashicons-plus"></span>] and [<span class="dashicons dashicons-minus"></span>] to zoom in and out.', FFCOLLECTION_PLUGIN_DIR_NAME); ?><br>
        <?php _e('In the actual display, the center is the position of the marker.', FFCOLLECTION_PLUGIN_DIR_NAME); ?>
    </p>
    <input type="text" class="lat" name="<?php echo esc_attr($name); ?>[lat]" value="<?php echo esc_attr($value['lat']); ?>" placeholder="<?php echo esc_attr($setting['placeholder']['lat']); ?>">
    <input type="text" class="lng" name="<?php echo esc_attr($name); ?>[lng]" value="<?php echo esc_attr($value['lng']); ?>" placeholder="<?php echo esc_attr($setting['placeholder']['lng']); ?>">
    <input type="text" class="zoom" name="<?php echo esc_attr($name); ?>[zoom]" value="<?php echo esc_attr($value['zoom']); ?>" placeholder="<?php echo esc_attr($setting['placeholder']['zoom']); ?>">
    <p><label><input class="visible" type="checkbox" checked="checked"><?php _e('Hide map', FFCOLLECTION_PLUGIN_DIR_NAME); ?></label></p>
    <div class="mapCanvas" style="height: 500px;"></div>
</div>
<?php if ($setting['description']) : ?>
<p class="description"><?php echo $setting['description']; ?></p>
<?php endif; ?>
