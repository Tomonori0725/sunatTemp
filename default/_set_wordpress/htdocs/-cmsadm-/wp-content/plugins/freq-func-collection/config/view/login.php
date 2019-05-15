<table class="form-table table-definelist table-striped">
    <caption><h4>URL</h4></caption>
    <?php
    $arrValue = $value;
    $key = 'url';
    $setting = $this->fieldKeyList[$key];
    ?>
    <tr class="<?php echo esc_attr($setting['type']); ?>">
        <th>
            <?php echo esc_html($setting['label']); ?>
        </th>
        <td>
            <?php
            $name = 'ffc-config-' . $pageName . '[' . $key . ']';
            include FFCOLLECTION_PLUGIN_DIR_PATH . 'config/view/field.php';
            ?>
            <p class="description">
            <?php the_ffc_url('wp-top'); ?> 以降を入力してください。<br>
            <?php if ($this->exists('description', $setting, false)) : ?>
                <?php echo $setting['description']; ?>
            <?php endif; ?>
            </p>
        </td>
    </tr>
</table>

<table class="form-table table-definelist table-striped">
    <caption><h4>ロゴ</h4></caption>
    <?php
    $arrValue = array();
    if (array_key_exists('header', $value)) {
        $arrValue = $value['header'];
    }
    foreach ($this->fieldKeyList['header'] as $key => $setting) {
        ?>
        <tr class="<?php echo esc_attr($setting['type']); ?>">
            <th><?php echo esc_html($setting['label']); ?></th>
            <td>
                <?php
                $name = 'ffc-config-' . $pageName . '[header][' . $key . ']';
                include FFCOLLECTION_PLUGIN_DIR_PATH . 'config/view/field.php';
                ?>
                <?php if ($this->exists('description', $setting, false)) : ?>
                    <p class="description"><?php echo $setting['description']; ?></p>
                <?php endif; ?>
            </td>
        </tr>
        <?php
    }
    ?>
</table>

<table class="form-table table-definelist table-striped">
    <caption><h4>背景</h4></caption>
    <?php
    $arrValue = array();
    if (array_key_exists('background', $value)) {
        $arrValue = $value['background'];
    }
    foreach ($this->fieldKeyList['background'] as $key => $setting) {
        ?>
        <tr class="<?php echo esc_attr($setting['type']); ?>">
            <th><?php echo esc_html($setting['label']); ?></th>
            <td>
                <?php
                $name = 'ffc-config-' . $pageName . '[background][' . $key . ']';
                include FFCOLLECTION_PLUGIN_DIR_PATH . 'config/view/field.php';
                ?>
                <?php if ($this->exists('description', $setting, false)) : ?>
                    <p class="description"><?php echo $setting['description']; ?></p>
                <?php endif; ?>
            </td>
        </tr>
        <?php
    }
    ?>
</table>

<table class="form-table table-definelist table-striped">
    <caption><h4>CSS</h4></caption>
    <?php
    $arrValue = $value;
    $key = 'css';
    $setting = $this->fieldKeyList[$key];
    ?>
    <tr class="<?php echo esc_attr($setting['type']); ?>">
        <th><?php echo esc_html($setting['label']); ?></th>
        <td>
            <?php
            $name = 'ffc-config-' . $pageName . '[' . $key . ']';
            include FFCOLLECTION_PLUGIN_DIR_PATH . 'config/view/field.php';
            ?>
            <?php if ($this->exists('description', $setting, false)) : ?>
                <p class="description"><?php echo $setting['description']; ?></p>
            <?php endif; ?>
        </td>
    </tr>
</table>

<table class="form-table table-definelist table-striped">
    <caption><h4>メッセージ</h4></caption>
    <?php
    $arrValue = array();
    if (array_key_exists('error', $value)) {
        $arrValue = $value['error'];
    }
    foreach ($this->fieldKeyList['error'] as $key => $setting) {
        ?>
        <tr class="<?php echo esc_attr($setting['type']); ?>">
            <th><?php echo esc_html($setting['label']); ?></th>
            <td>
                <?php
                $name = 'ffc-config-' . $pageName . '[error][' . $key . ']';
                include FFCOLLECTION_PLUGIN_DIR_PATH . 'config/view/field.php';
                ?>
                <?php if ($this->exists('description', $setting, false)) : ?>
                    <p class="description"><?php echo $setting['description']; ?></p>
                <?php endif; ?>
            </td>
        </tr>
        <?php
    }
    ?>
</table>
