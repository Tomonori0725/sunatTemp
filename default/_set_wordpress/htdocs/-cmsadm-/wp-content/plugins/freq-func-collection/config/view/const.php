<?php
$name = 'ffc-config-' . $pageName;
?>
<table class="form-table table-addablelist table-striped">
    <caption>
        <p class="description">キー(定数名)と値をセットで入力してください<br>カスタム投稿タイプ・メニューページのフィールドにmapを使用する際はキー「GOOGLE_MAPS_API_KEY」として値にAPIキーを設定してください</p>
    </caption>
    <thead>
        <tr>
            <th></th>
            <th>キー</th>
            <th>値</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($value['const'] as $varValue) : ?>
            <tr>
                <td><button type="button" class="removeItemButton"><span class="dashicons dashicons-dismiss"></span></button></td>
                <td data-colname="キー"><input type="text" class="widefat" name="<?php echo esc_attr($name); ?>[var][]" value="<?php echo esc_attr($varValue['var']); ?>"></td>
                <td data-colname="値"><input type="text" class="widefat" name="<?php echo esc_attr($name); ?>[value][]" value="<?php echo esc_attr($varValue['value']); ?>"></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<button type="button" id="addConstFieldButton" class="button button-primary addButton">定数を追加する</button>

<table class="form-table table-checklist table-striped">
    <tbody>
        <tr>
            <td><input type="checkbox" id="<?php echo esc_attr($name); ?>" name="<?php echo esc_attr($name); ?>[define]" value="1"<?php checked($value['define'], true); ?>></td>
            <th>
                <label for="<?php echo esc_attr($name); ?>">PHPの定数として定義する</label>
                <p class="description"><label for="<?php echo esc_attr($name); ?>">キーのみで取得ができるようになります。</label></p>
            </th>
        </tr>
</tbody>
</table>

<script type="text/html" id="templateConstFieldItem">
<tr>
    <td><button type="button" class="removeItemButton"><span class="dashicons dashicons-dismiss"></span></button></td>
    <td data-colname="キー"><input type="text" class="widefat" name="<?php echo esc_attr($name); ?>[var][]" value=""></td>
    <td data-colname="値"><input type="text" class="widefat" name="<?php echo esc_attr($name); ?>[value][]" value=""></td>
</tr>
</script>
