<table class="form-table table-checklist table-striped">
    <caption><h4>サポート</h4></caption>
    <tbody>
        <?php
        $arrValue = array();
        if (array_key_exists('support', $value)) {
            $arrValue = $value['support'];
        }
        foreach ($this->fieldKeyList['support'] as $key => $setting) {
            $name = 'ffc-config-' . $pageName . '[support][' . $key . ']';
            ?>
            <tr class="<?php echo esc_attr($setting['type']); ?>">
                <td><input type="checkbox" id="<?php echo esc_attr($name); ?>" name="<?php echo esc_attr($name); ?>" value="1"<?php checked(in_array($key, $arrValue, true), true); ?>></td>
                <th>
                    <label for="<?php echo esc_attr($name); ?>"><?php echo esc_html($setting['label']); ?>
                    <?php if ($this->exists('description', $setting, false)) : ?>
                        <p class="description"><?php echo $setting['description']; ?></p>
                    <?php endif; ?>
                </th>
            </tr>
            <?php
        }
        ?>
    </tbody>
</table>

<table class="form-table table-addablelist table-striped sizeList">
    <caption>
        <h4>カスタム画像サイズ</h4>
        <p class="description">サイズ名()と表示名(管理画面上での名前)と画像サイズをセットで入力してください</p>
    </caption>
    <thead>
        <tr>
            <th></th>
            <th>サイズ名<br>表示名</th>
            <th>画像サイズ</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $arrValue = $value;
        foreach ($this->sizeFieldKeyList as $key => $setting) {
            $name = 'ffc-config-' . $pageName . '[' . $key . ']';
            if ($this->exists($key, $arrValue)
                && is_array($arrValue[$key])
            ) {
                foreach ($arrValue[$key] as $no => $var) {
                    ?>
                    <tr class="sizeItem" data-size-no="<?php echo esc_attr($no); ?>">
                        <td><button type="button" class="removeItemButton"><span class="dashicons dashicons-dismiss"></span></button></td>
                        <td data-colname=""><input type="text" class="widefat" name="<?php echo esc_attr($name); ?>[key][<?php echo esc_attr($no); ?>]" value="<?php echo esc_attr($var['key']); ?>"><br><input type="text" class="widefat" name="<?php echo esc_attr($name); ?>[label][<?php echo esc_attr($no); ?>]" value="<?php echo esc_attr($var['label']); ?>"></td>
                        <td><label for="<?php echo esc_attr($name); ?>[width][<?php echo esc_attr($no); ?>]">幅</label>
    <input type="text" id="<?php echo esc_attr($name); ?>[width][<?php echo esc_attr($no); ?>]" name="<?php echo esc_attr($name); ?>[width][<?php echo esc_attr($no); ?>]" value="<?php echo esc_attr($var['width']); ?>"><br><label for="<?php echo esc_attr($name); ?>[height][<?php echo esc_attr($no); ?>]">高さ</label><input type="text" id="<?php echo esc_attr($name); ?>[height][<?php echo esc_attr($no); ?>]" name="<?php echo esc_attr($name); ?>[height][<?php echo esc_attr($no); ?>]" value="<?php echo esc_attr($var['height']); ?>"><br><input type="checkbox" id="<?php echo esc_attr($name); ?>[crop][<?php echo esc_attr($no); ?>]" name="<?php echo esc_attr($name); ?>[crop][<?php echo esc_attr($no); ?>]" value="1"<?php checked($var['crop'], true); ?>><label for="<?php echo esc_attr($name); ?>[crop][<?php echo esc_attr($no); ?>]">サムネイルを実寸法にトリミングする</label></td>
                    </tr>
                    <?php
                }
            }
        }
        ?>
    </tbody>
</table>
<button type="button" id="addSizeFieldButton" class="button button-primary addButton">カスタム画像サイズを追加する</button>

<script type="text/html" id="templateSizeFieldItem">
<tr class="sizeItem" data-size-no="{{SIZE_NO}}">
    <td><button type="button" class="removeItemButton"><span class="dashicons dashicons-dismiss"></span></button></td>
    <td data-colname=""><input type="text" class="widefat" name="<?php echo esc_attr($name); ?>[key][{{SIZE_NO}}]" value=""><br><input type="text" class="widefat" name="<?php echo esc_attr($name); ?>[label][{{SIZE_NO}}]" value=""></td>
    <td><label for="<?php echo esc_attr($name); ?>[width][{{SIZE_NO}}]">幅</label><input type="text" id="<?php echo esc_attr($name); ?>[width][{{SIZE_NO}}]" name="<?php echo esc_attr($name); ?>[width][{{SIZE_NO}}]" value=""><br><label for="<?php echo esc_attr($name); ?>[height][{{SIZE_NO}}]">高さ</label><input type="text" id="<?php echo esc_attr($name); ?>[height][{{SIZE_NO}}]" name="<?php echo esc_attr($name); ?>[height][{{SIZE_NO}}]" value=""><br><input type="checkbox" id="<?php echo esc_attr($name); ?>[crop][{{SIZE_NO}}]" name="<?php echo esc_attr($name); ?>[crop][{{SIZE_NO}}]" value="1"><label for="<?php echo esc_attr($name); ?>[crop][{{SIZE_NO}}]">サムネイルを実寸法にトリミングする</label></td>
</tr>
</script>
