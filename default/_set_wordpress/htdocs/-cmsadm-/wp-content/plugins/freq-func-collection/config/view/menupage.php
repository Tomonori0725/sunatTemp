<div class="menupageList">
    <?php
    global $ffCollection;
    if (is_array($value)) :
        foreach ($value as $menupageNo => $menupage) :
            $menupageFields = array();
            foreach($this->fieldKeyList as $field) {
                $field['name'] = 'ffc-config-' . $pageName . '[' . $menupageNo . '][' . $field['key'] . ']';
                $menupageFields[] = $field;
            }
            $menupageFields = $ffCollection->field->setDefaultConfig($menupageFields);
            ?>
            <div class="item menupageItem" data-menupage-no="<?php echo esc_html($menupageNo); ?>">
                <h3 class="handle">
                    <button type="button" class="removeItemButton"><span class="dashicons dashicons-dismiss"></span></button>
                    <span class="menupageHandle dashicons dashicons-leftright"></span>
                    <span class="title"><?php echo esc_html($value[$menupageNo]['title']); ?></span>&nbsp;<span class="slug">(<?php echo esc_html($value[$menupageNo]['slug']); ?>)</span>
                </h3>
                <div class="group">
                    <table class="form-table table-definelist mb-0">
                        <?php foreach ($menupageFields as $field) : ?>
                            <tr>
                                <th scope="row"><?php echo esc_html($field['title']); ?></th>
                                <td><?php $field['instance']->createField($value[$menupageNo][$field['key']]); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>

                    <div class="metaboxFieldList">
                        <?php foreach ($value[$menupageNo]['fields'] as $fieldNo => $fieldValue) :
                            $metaboxFields = array();
                            foreach($this->metaboxFieldKeyList as $field) {
                                $field['name'] = 'ffc-config-' . $pageName . '[' . $menupageNo . '][fields][' . $fieldNo . '][' . $field['key'] . ']';
                                $metaboxFields[] = $field;
                            }
                            $metaboxFields = $ffCollection->field->setDefaultConfig($metaboxFields);
                            ?>
                            <div class="item metaboxFieldItem" data-metabox-field-no="<?php echo esc_html($fieldNo); ?>">
                                <h4 class="handle">
                                    <button type="button" class="removeItemButton"><span class="dashicons dashicons-dismiss"></span></button>
                                    <span class="metaboxFieldHandle dashicons dashicons-leftright"></span>
                                    <span class="title"><?php echo esc_html($fieldValue['title']); ?></span>&nbsp;<span class="key">(<?php echo esc_html($fieldValue['key']); ?>)</span>
                                </h4>
                                <div class="group">
                                    <table class="form-table table-definelist mb-0">
                                        <?php foreach ($metaboxFields as $field) : ?>
                                            <?php
                                            $class = '';
                                            if (!in_array($field['key'], $this->typeKeyList[$fieldValue['type']], true)) {
                                                if ('group' === $field['type']) {
                                                    $fieldValue[$field['key']] = array();
                                                    foreach ($field['item'] as $item) {
                                                        $fieldValue[$field['key']][$item['key']] = '';
                                                    }
                                                } else {
                                                    $fieldValue[$field['key']] = '';
                                                }
                                                $class = 'hide';
                                            } else {
                                                if (!$this->exists($field['key'], $fieldValue)) {
                                                    if ('group' === $field['type']) {
                                                        $fieldValue[$field['key']] = array();
                                                        foreach ($field['item'] as $item) {
                                                            $fieldValue[$field['key']][$item['key']] = '';
                                                        }
                                                    } else {
                                                        $fieldValue[$field['key']] = '';
                                                    }
                                                }
                                            }
                                            ?>
                                            <tr data-field-key="<?php echo esc_attr($field['key']); ?>" class="<?php echo $class; ?>">
                                                <th scope="row"><?php echo esc_html($field['title']); ?></th>
                                                <td><?php $field['instance']->createField($fieldValue[$field['key']]); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <tr>
                                            <td colspan="2"><button type="button" class="button button-primary viewExampleCode" data-view-code="menupage">使用例を表示する</button></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="button addButton button-primary addMetaboxFieldButton">フィールドを追加する</button>
                </div>
            </div>
            <?php
        endforeach;
    endif;
    ?>
</div>
<button type="button" class="button addButton button-primary addMenupageButton">メニューページを追加する</button>

<script>
    var typeKeyList = <?php echo json_encode($this->typeKeyList); ?>
</script>
<script type="text/html" id="templateMenupageItem">
<?php
$menupageFields = array();
foreach($this->fieldKeyList as $field) {
    $field['name'] = 'ffc-config-' . $pageName . '[{{MENUPAGE_NO}}][' . $field['key'] . ']';
    $menupageFields[] = $field;
}
$menupageFields = $ffCollection->field->setDefaultConfig($menupageFields);
?>
<div class="item menupageItem" data-menupage-no="{{MENUPAGE_NO}}">
    <h3 class="handle">
        <button type="button" class="removeItemButton"><span class="dashicons dashicons-dismiss"></span></button>
        <span class="menupageHandle dashicons dashicons-leftright ui-sortable-handle"></span>
        <span class="title"></span>&nbsp;<span class="slug"></span>
    </h3>
    <div class="group">
        <table class="form-table table-definelist mb-0">
            <?php foreach ($menupageFields as $field) : ?>
                <?php
                if ('group' === $field['type']) {
                    $fieldValue = array();
                    foreach ($field['item'] as $item) {
                        $fieldValue[$item['key']] = '';
                    }
                } else {
                    $fieldValue = '';
                }
                ?>
                <tr>
                    <th scope="row"><?php echo esc_html($field['title']); ?></th>
                    <td><?php $field['instance']->createField($fieldValue); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <div class="metaboxFieldList"></div>
        <button type="button" class="button addButton button-primary addMetaboxFieldButton">フィールドを追加する</button>
    </div>
</div>
</script>
<script type="text/html" id="templateMetaboxFieldItem">
<?php
$metaboxFields = array();
foreach($this->metaboxFieldKeyList as $field) {
    $field['name'] = 'ffc-config-' . $pageName . '[{{MENUPAGE_NO}}][fields][{{METABOX_FIELD_NO}}][' . $field['key'] . ']';
    $metaboxFields[] = $field;
}
$metaboxFields = $ffCollection->field->setDefaultConfig($metaboxFields);
?>
<div class="item metaboxFieldItem" data-metabox-field-no="{{METABOX_FIELD_NO}}">
    <h4 class="handle">
        <button type="button" class="removeItemButton"><span class="dashicons dashicons-dismiss"></span></button>
        <span class="metaboxFieldHandle dashicons dashicons-leftright ui-sortable-handle"></span>
        <span class="title"></span>&nbsp;<span class="key"></span>
    </h4>
    <div class="group">
        <table class="form-table table-definelist mb-0">
            <?php foreach ($metaboxFields as $field) : ?>
                <?php
                $class = '';
                if (!in_array($field['key'], $this->typeKeyList['text'], true)) {
                    $class = 'hide';
                }
                if ('group' === $field['type']) {
                    $fieldValue = array();
                    foreach ($field['item'] as $item) {
                        $fieldValue[$item['key']] = '';
                    }
                } else {
                    $fieldValue = '';
                }
                ?>
                <tr data-field-key="<?php echo esc_attr($field['key']); ?>" class="<?php echo $class; ?>">
                    <th scope="row"><?php echo esc_html($field['title']); ?></th>
                    <td><?php $field['instance']->createField($fieldValue); ?></td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="2"><button type="button" class="button button-primary viewExampleCode" data-view-code="menupage">使用例を表示する</button></td>
            </tr>
        </table>
    </div>
</div>
</script>
<script>
    var exampleCode = <?php echo json_encode($this->exampleCode); ?>;
</script>
