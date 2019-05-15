<div class="fieldGroup">
    <?php foreach ($setting['item'] as $item) : ?>
        <?php
        if ($this->exists('title-hide', $item) && $item['title-hide']) {
            $attr = 'class="screen-reader-text"';
        } else {
            $attr = 'for="' . esc_attr($item['id']) . '"';
        }
        ?>
        <label <?php echo $attr; ?>><?php echo esc_html($item['title']); ?></label>
        <?php
        $val = '';
        if (is_array($value)
            && array_key_exists($item['key'], $value)
        ) {
            $val = $value[$item['key']];
        }
        ?>
        <?php $item['instance']->createField($val); ?>
    <?php endforeach; ?>
</div>
<?php if ($setting['description']) : ?>
<p class="description"><?php echo $setting['description']; ?></p>
<?php endif; ?>
