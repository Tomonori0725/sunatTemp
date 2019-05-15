<table class="form-table table-addablelist table-striped areaList">
    <caption>
        <h4>ウィジェットエリア</h4>
        <button type="button" class="viewDescription">説明を表示する</button>
        <div class="funcDescription">
            <p>「外観 > ウィジェット」にウィジェットを配置するエリアを追加できます。<br>例えばIDを「sidebar01」とし、追加されたエリアにウィジェットを配置し、テンプレート上で<pre><code>&lt;?php if (is_active_sidebar('sidebar01')) : ?&gt;
        &lt;?php dynamic_sidebar('sidebar01'); ?&gt;
    &lt;?php endif; ?&gt;</code></pre>とすると配置したウィジェット画面表示されます。</p>
        </div>
    </caption>
    <?php
    global $ffCollection;

    $arrValue = $value['area'];
    ?>
    <thead>
        <tr>
            <th></th>
            <?php foreach ($this->fieldKeyList as $field) : ?>
                <th scope="row"><?php echo esc_html($field['title']); ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($arrValue as $no => $area) : ?>
            <?php
                $fields = array();
                foreach($this->fieldKeyList as $field) {
                    $field['name'] = 'ffc-config-' . $pageName . '[area][' . $no . '][' . $field['key'] . ']';
                    $fields[] = $field;
                }
                $fields = $ffCollection->field->setDefaultConfig($fields);
            ?>
            <tr class="areaItem" data-area-no="<?php echo esc_attr($no); ?>">
                <td><button type="button" class="removeItemButton"><span class="dashicons dashicons-dismiss"></span></button></td>
                <?php foreach ($fields as $field) : ?>
                    <td data-colname="<?php echo esc_attr($field['title']); ?>"><?php $field['instance']->createField($area[$field['key']]); ?></td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<button type="button" id="addAreaFieldButton" class="button button-primary addButton">ウィジェットエリアを追加する</button>

<table class="form-table table-checklist table-striped">
    <caption>
        <h4>追加ウィジェット</h4>
        <button type="button" class="viewDescription">説明を表示する</button>
        <div class="funcDescription">
            <p>当プラグインのウィジェットで追加するものにチェックをしてください。<br>「外観 > ウィジェット」の「利用できるウィジェット」に追加されます。</p>
        </div>
    </caption>
    <tbody>
        <?php
        $arrValue = $value['register'];
        foreach ($this->pluginWidgetList as $widget) {
            $name = 'ffc-config-' . $pageName . '[register]';
            $checked = in_array($widget['class'], $arrValue, true);
            ?>
            <tr>
                <td><input type="checkbox" id="<?php echo esc_attr($widget['class']); ?>" name="<?php echo esc_attr($name); ?>[]" value="<?php echo esc_attr($widget['class']); ?>"<?php checked($checked, true); ?>></td>
                <th>
                    <label for="<?php echo esc_attr($widget['class']); ?>"><?php echo esc_html($widget['name']); ?></label>
                    <?php if ($this->exists('description', $widget, false)) : ?>
                        <p class="description"><label for="<?php echo esc_attr($widget['class']); ?>"><?php echo esc_html($widget['description']); ?></label></p>
                    <?php endif; ?>
                </th>
            </tr>
            <?php
        }
        ?>
    </tbody>
</table>

<table class="form-table table-checklist table-striped">
    <caption>
        <h4>削除ウィジェット</h4>
        <button type="button" class="viewDescription">説明を表示する</button>
        <div class="funcDescription">
            <p>登録されているウィジェットで削除するものにチェックをしてください。<br>「外観 > ウィジェット」の「利用できるウィジェット」から削除されます。</p>
        </div>
    </caption>
    <tbody>
        <?php
        $arrValue = $value['unregister'];
        foreach ($this->otherWidgetList as $widget) {
            $name = 'ffc-config-' . $pageName . '[unregister]';
            $checked = in_array($widget['class'], $arrValue, true);
            ?>
            <tr>
                <td><input type="checkbox" id="<?php echo esc_attr($widget['class']); ?>" name="<?php echo esc_attr($name); ?>[]" value="<?php echo esc_attr($widget['class']); ?>"<?php checked($checked, true); ?>></td>
                <th>
                    <label for="<?php echo esc_attr($widget['class']); ?>"><?php echo esc_html($widget['name']); ?></label>
                    <?php if ($this->exists('description', $widget, false)) : ?>
                        <p class="description"><label for="<?php echo esc_attr($widget['class']); ?>"><?php echo esc_html($widget['description']); ?></label></p>
                    <?php endif; ?>
                </th>
            </tr>
            <?php
        }
        ?>
    </tbody>
</table>

<script type="text/html" id="templateAreaFieldItem">
<?php
    $fields = array();
    foreach($this->fieldKeyList as $field) {
        $field['name'] = 'ffc-config-' . $pageName . '[area][{{AREA_NO}}][' . $field['key'] . ']';
        $fields[] = $field;
    }
    $fields = $ffCollection->field->setDefaultConfig($fields);
?>
<tr class="areaItem" data-area-no="{{AREA_NO}}">
    <td><button type="button" class="removeItemButton"><span class="dashicons dashicons-dismiss"></span></button></td>
    <?php foreach ($fields as $field) : ?>
        <td data-colname="<?php echo esc_attr($field['title']); ?>"><?php $field['instance']->createField(''); ?></td>
    <?php endforeach; ?>
</tr>
</script>
