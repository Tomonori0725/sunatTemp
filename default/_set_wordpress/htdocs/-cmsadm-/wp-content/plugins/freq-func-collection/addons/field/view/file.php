<?php
uploadImageTemplate(array(
    'imageName' => $setting['name'],
    'imageId'   => $value,
    'multi'     => $setting['multi'],
    'sortable'  => $setting['sortable'],
    'filetype'  => $setting['filetype'],
    'button'    => __('Add File', FFCOLLECTION_PLUGIN_DIR_NAME)
));
?>
<?php if ($setting['description']) : ?>
<p class="description"><?php echo $setting['description']; ?></p>
<?php endif; ?>
