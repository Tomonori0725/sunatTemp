<div class="posttypeList">
    <?php
    global $ffCollection;
    if (is_array($value)) :
        foreach ($value as $posttypeNo => $posttype) :
            $posttypeFields = array();
            foreach($this->fieldKeyList as $field) {
                $field['name'] = 'ffc-config-posttype[' . $posttypeNo . '][' . $field['key'] . ']';
                $posttypeFields[] = $field;
            }
            $posttypeFields = $ffCollection->field->setDefaultConfig($posttypeFields);
            ?>
            <div class="item posttypeItem" data-posttype-no="<?php echo esc_html($posttypeNo); ?>">
                <h3 class="handle">
                    <button type="button" class="removeItemButton"><span class="dashicons dashicons-dismiss"></span></button>
                    <span class="posttypeHandle dashicons dashicons-leftright"></span>
                    <span class="name"><?php echo esc_html($value[$posttypeNo]['name']); ?></span>&nbsp;<span class="slug">(<?php echo esc_html($value[$posttypeNo]['slug']); ?>)</span>
                </h3>
                <div class="group">
                    <table class="form-table table-definelist mb-0">
                        <?php foreach ($posttypeFields as $field) : ?>
                            <tr>
                                <th scope="row"><?php echo esc_html($field['title']); ?></th>
                                <td><?php $field['instance']->createField($value[$posttypeNo][$field['key']]); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>

                    <h4 class="handle">メタボックス</h4>
                    <div class="group">
                        <div class="metaboxList">
                            <?php foreach ($value[$posttypeNo]['metabox'] as $metaboxNo => $metabox) : ?>
                                <div class="item metaboxItem" data-metabox-no="<?php echo esc_html($metaboxNo); ?>">
                                    <h4 class="handle">
                                        <button type="button" class="removeItemButton"><span class="dashicons dashicons-dismiss"></span></button>
                                        <span class="metaboxHandle dashicons dashicons-leftright"></span>
                                        <span class="title"><?php echo esc_html($value[$posttypeNo]['metabox'][$metaboxNo]['title']); ?></span>&nbsp;
                                    </h4>
                                    <div class="group">
                                        <table class="form-table table-definelist mb-0">
                                            <tr>
                                                <th scope="row">タイトル</th>
                                                <td>
                                                    <input type="text" class="widefat" name="ffc-config-posttype[<?php echo esc_attr($posttypeNo); ?>][metabox][<?php echo esc_attr($metaboxNo); ?>][title]" value="<?php echo esc_attr($value[$posttypeNo]['metabox'][$metaboxNo]['title']); ?>">
                                                </td>
                                            </tr>
                                        </table>
                                        <div class="metaboxFieldList">
                                            <?php foreach ($value[$posttypeNo]['metabox'][$metaboxNo]['fields'] as $fieldNo => $fieldValue) :
                                                $metaboxFields = array();
                                                foreach($this->metaboxFieldKeyList as $field) {
                                                    $field['name'] = 'ffc-config-posttype[' . $posttypeNo . '][metabox][' . $metaboxNo . '][fields][' . $fieldNo . '][' . $field['key'] . ']';
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
                                                                <td colspan="2"><button type="button" class="button button-primary viewExampleCode" data-view-code="metabox">使用例を表示する</button></td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <button type="button" class="button addButton button-primary addMetaboxFieldButton">フィールドを追加する</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="button addButton button-primary addMetaboxButton">メタボックスを追加する</button>
                    </div>

                    <h4 class="handle">タクソノミー</h4>
                    <div class="group">
                        <div class="taxonomyList">
                            <?php foreach ($value[$posttypeNo]['taxonomy'] as $taxonomyNo => $taxonomy) : ?>
                                <div class="item taxonomyItem" data-taxonomy-no="<?php echo esc_html($taxonomyNo); ?>">
                                    <h4 class="handle">
                                        <button type="button" class="removeItemButton"><span class="dashicons dashicons-dismiss"></span></button>
                                        <span class="taxonomyHandle dashicons dashicons-leftright"></span>
                                        <span class="name"><?php echo esc_html($value[$posttypeNo]['taxonomy'][$taxonomyNo]['name']); ?></span>&nbsp;<span class="slug">(<?php echo esc_html($value[$posttypeNo]['taxonomy'][$taxonomyNo]['slug']); ?>)</span>
                                    </h4>
                                    <div class="group">
                                        <table class="form-table table-definelist mb-0">
                                            <tr>
                                                <th scope="row">表示名</th>
                                                <td>
                                                    <input type="text" class="widefat" name="ffc-config-posttype[<?php echo esc_attr($posttypeNo); ?>][taxonomy][<?php echo esc_attr($taxonomyNo); ?>][name]" value="<?php echo esc_attr($value[$posttypeNo]['taxonomy'][$taxonomyNo]['name']); ?>">
                                                </td>
                                            </tr>
                                            <tr>
                                                <th scope="row">スラッグ</th>
                                                <td>
                                                    <input type="text" class="widefat" name="ffc-config-posttype[<?php echo esc_attr($posttypeNo); ?>][taxonomy][<?php echo esc_attr($taxonomyNo); ?>][slug]" value="<?php echo esc_attr($value[$posttypeNo]['taxonomy'][$taxonomyNo]['slug']); ?>">
                                                </td>
                                            </tr>
                                            <tr>
                                                <th scope="row">階層</th>
                                                <td>
                                                <input type="checkbox" id="ffc-config-posttype[<?php echo esc_attr($posttypeNo); ?>][taxonomy][<?php echo esc_attr($taxonomyNo); ?>][child]_1" name="ffc-config-posttype[<?php echo esc_attr($posttypeNo); ?>][taxonomy][<?php echo esc_attr($taxonomyNo); ?>][child]" value="1"<?php checked($value[$posttypeNo]['taxonomy'][$taxonomyNo]['child'], 1); ?>><label for="ffc-config-posttype[<?php echo esc_attr($posttypeNo); ?>][taxonomy][<?php echo esc_attr($taxonomyNo); ?>][child]_1"></label>
                                                </td>
                                            </tr>
                                        </table>
                                        <div class="taxonomyFieldList">
                                            <?php foreach ($value[$posttypeNo]['taxonomy'][$taxonomyNo]['fields'] as $fieldNo => $fieldValue) :
                                                $taxonomyFields = array();
                                                foreach($this->metaboxFieldKeyList as $field) {
                                                    $field['name'] = 'ffc-config-posttype[' . $posttypeNo . '][taxonomy][' . $taxonomyNo . '][fields][' . $fieldNo . '][' . $field['key'] . ']';
                                                    $taxonomyFields[] = $field;
                                                }
                                                $taxonomyFields = $ffCollection->field->setDefaultConfig($taxonomyFields);
                                                ?>
                                                <div class="item taxonomyFieldItem" data-taxonomy-field-no="<?php echo esc_html($fieldNo); ?>">
                                                    <h4 class="handle">
                                                        <button type="button" class="removeItemButton"><span class="dashicons dashicons-dismiss"></span></button>
                                                        <span class="taxonomyFieldHandle dashicons dashicons-leftright"></span>
                                                        <span class="title"><?php echo esc_html($fieldValue['title']); ?></span>&nbsp;<span class="key">(<?php echo esc_html($fieldValue['key']); ?>)</span>
                                                    </h4>
                                                    <div class="group">
                                                        <table class="form-table table-definelist mb-0">
                                                            <?php foreach ($taxonomyFields as $field) : ?>
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
                                                                <td colspan="2"><button type="button" class="button button-primary viewExampleCode" data-view-code="taxonomy">使用例を表示する</button></td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <button type="button" class="button addButton button-primary addTaxonomyFieldButton">フィールドを追加する</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="button addButton button-primary addTaxonomyButton">タクソノミーを追加する</button>
                    </div>
                </div>
            </div>
            <?php
        endforeach;
    endif;
    ?>
</div>
<button type="button" class="button addButton button-primary addPosttypeButton">カスタム投稿タイプを追加する</button>

<script>
    var typeKeyList = <?php echo json_encode($this->typeKeyList); ?>
</script>
<script type="text/html" id="templatePosttypeItem">
<div class="item posttypeItem" data-posttype-no="{{POSTTYPE_NO}}">
    <h3 class="handle">
        <button type="button" class="removeItemButton"><span class="dashicons dashicons-dismiss"></span></button>
        <span class="posttypeHandle dashicons dashicons-leftright ui-sortable-handle"></span>
        <span class="name"></span>&nbsp;<span class="slug"></span>
    </h3>
    <div class="group">
        <table class="form-table table-definelist mb-0">
            <tbody>
                <tr>
                    <th scope="row">表示名</th>
                    <td>
                        <input type="text" id="ffc-config-posttype[{{POSTTYPE_NO}}][name]" class="widefat" name="ffc-config-posttype[{{POSTTYPE_NO}}][name]" value="" placeholder="">
                    </td>
                </tr>
                <tr>
                    <th scope="row">スラッグ</th>
                    <td>
                        <input type="text" id="ffc-config-posttype[{{POSTTYPE_NO}}][slug]" class="widefat" name="ffc-config-posttype[{{POSTTYPE_NO}}][slug]" value="" placeholder="">
                    </td>
                </tr>
                <tr>
                    <th scope="row">アイコン</th>
                    <td>
                        <input type="text" id="ffc-config-posttype[{{POSTTYPE_NO}}][icon]" class="widefat" name="ffc-config-posttype[{{POSTTYPE_NO}}][icon]" value="" placeholder="">
                        <p class="description">WordPressの<a href="https://developer.wordpress.org/resource/dashicons/" target="_blank">Dashicons</a>の名称を入力してください<br>使用したいアイコンをクリックすると上部に大きく表示されますので、その隣に記載されている名称(dashicons-***)を入力してください</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">メニュー位置</th>
                    <td>
                        <div class="spinner-container widefat" data-num-min="1" data-num-max="99" data-num-step="1">
                            <div class="button button-primary dashicons dashicons-minus spinner-down"></div>
                            <input type="text" id="ffc-config-posttype[{{POSTTYPE_NO}}][position]" class="spinner-field" name="ffc-config-posttype[{{POSTTYPE_NO}}][position]" value="">
                            <div class="button button-primary dashicons dashicons-plus spinner-up"></div>
                        </div>
                        <p class="description"><a href="https://developer.wordpress.org/reference/functions/add_menu_page/#menu-structure" target="_blank">既存のメニュー位置</a>を参考にしてください</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">記事タイトルのプレースホルダー</th>
                    <td>
                        <input type="text" id="ffc-config-posttype[{{POSTTYPE_NO}}][title-placeholder]" class="widefat" name="ffc-config-posttype[{{POSTTYPE_NO}}][title-placeholder]" value="" placeholder="">
                    </td>
                </tr>
                <tr>
                    <th scope="row">利用する初期入力項目</th>
                    <td>
                        <div class="widefat">
                            <input type="checkbox" id="ffc-config-posttype[{{POSTTYPE_NO}}][supports]_title" name="ffc-config-posttype[{{POSTTYPE_NO}}][supports][]" value="title">
                            <label for="ffc-config-posttype[{{POSTTYPE_NO}}][supports]_title">タイトル</label>
                            <br>
                            <input type="checkbox" id="ffc-config-posttype[{{POSTTYPE_NO}}][supports]_editor" name="ffc-config-posttype[{{POSTTYPE_NO}}][supports][]" value="editor">
                            <label for="ffc-config-posttype[{{POSTTYPE_NO}}][supports]_editor">本文</label>
                            <br>
                            <input type="checkbox" id="ffc-config-posttype[{{POSTTYPE_NO}}][supports]_thumbnail" name="ffc-config-posttype[{{POSTTYPE_NO}}][supports][]" value="thumbnail">
                            <label for="ffc-config-posttype[{{POSTTYPE_NO}}][supports]_thumbnail">アイキャッチ</label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th scope="row">利用するアーカイブページ</th>
                    <td>
                        <div class="widefat">
                            <input type="checkbox" id="ffc-config-posttype[{{POSTTYPE_NO}}][archive]_top" name="ffc-config-posttype[{{POSTTYPE_NO}}][archive][]" value="top">
                            <label for="ffc-config-posttype[{{POSTTYPE_NO}}][archive]_top">投稿タイプトップ</label>
                            <br>
                            <input type="checkbox" id="ffc-config-posttype[{{POSTTYPE_NO}}][archive]_year" name="ffc-config-posttype[{{POSTTYPE_NO}}][archive][]" value="year">
                            <label for="ffc-config-posttype[{{POSTTYPE_NO}}][archive]_year">年別アーカイブ</label>
                            <br>
                            <input type="checkbox" id="ffc-config-posttype[{{POSTTYPE_NO}}][archive]_month" name="ffc-config-posttype[{{POSTTYPE_NO}}][archive][]" value="month">
                            <label for="ffc-config-posttype[{{POSTTYPE_NO}}][archive]_month">月別アーカイブ</label>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <h4 class="handle">メタボックス</h4>
        <div class="group">
            <div class="metaboxList"></div>
            <button type="button" class="button addButton button-primary addMetaboxButton">メタボックスを追加する</button>
        </div>

        <h4 class="handle">タクソノミー</h4>
        <div class="group">
            <div class="taxonomyList"></div>
            <button type="button" class="button addButton button-primary addTaxonomyButton">タクソノミーを追加する</button>
        </div>
    </div>
</div>
</script>
<script type="text/html" id="templateMetaboxItem">
<div class="item metaboxItem" data-metabox-no="{{METABOX_NO}}">
    <h4 class="handle">
        <button type="button" class="removeItemButton"><span class="dashicons dashicons-dismiss"></span></button>
        <span class="metaboxHandle dashicons dashicons-leftright ui-sortable-handle"></span>
        <span class="title"></span>&nbsp;
    </h4>
    <div class="group">
        <table class="form-table table-definelist mb-0">
            <tbody>
                <tr>
                    <th scope="row">タイトル</th>
                    <td>
                        <input type="text" class="widefat" name="ffc-config-posttype[{{POSTTYPE_NO}}][metabox][{{METABOX_NO}}][title]" value="">
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="metaboxFieldList"></div>
        <button type="button" class="button addButton button-primary addMetaboxFieldButton">フィールドを追加する</button>
    </div>
</div>
</script>
<script type="text/html" id="templateTaxonomyItem">
<div class="item taxonomyItem" data-taxonomy-no="{{TAXONOMY_NO}}">
    <h4 class="handle">
        <button type="button" class="removeItemButton"><span class="dashicons dashicons-dismiss"></span></button>
        <span class="taxonomyHandle dashicons dashicons-leftright ui-sortable-handle"></span>
        <span class="name"></span>&nbsp;<span class="slug"></span>
    </h4>
    <div class="group">
        <table class="form-table table-definelist mb-0">
            <tbody>
                <tr>
                    <th scope="row">表示名</th>
                    <td>
                        <input type="text" class="widefat" name="ffc-config-posttype[{{POSTTYPE_NO}}][taxonomy][{{TAXONOMY_NO}}][name]" value="">
                    </td>
                </tr>
                <tr>
                    <th scope="row">スラッグ</th>
                    <td>
                        <input type="text" class="widefat" name="ffc-config-posttype[{{POSTTYPE_NO}}][taxonomy][{{TAXONOMY_NO}}][slug]" value="">
                    </td>
                </tr>
                <tr>
                    <th scope="row">階層</th>
                    <td>
                        <input type="checkbox" id="ffc-config-posttype[{{POSTTYPE_NO}}][taxonomy][{{TAXONOMY_NO}}][child]_1" name="ffc-config-posttype[{{POSTTYPE_NO}}][taxonomy][{{TAXONOMY_NO}}][child]" value="1">
                        <label for="ffc-config-posttype[{{POSTTYPE_NO}}][taxonomy][{{TAXONOMY_NO}}][child]_1"></label>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="taxonomyFieldList"></div>
        <button type="button" class="button addButton button-primary addTaxonomyFieldButton">フィールドを追加する</button>
    </div>
</div>
</script>
<script type="text/html" id="templateMetaboxFieldItem">
<?php
$metaboxFields = array();
foreach($this->metaboxFieldKeyList as $field) {
    $field['name'] = 'ffc-config-posttype[{{POSTTYPE_NO}}][metabox][{{METABOX_NO}}][fields][{{METABOX_FIELD_NO}}][' . $field['key'] . ']';
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
                <td colspan="2"><button type="button" class="button button-primary viewExampleCode" data-view-code="metabox">使用例を表示する</button></td>
            </tr>
        </table>
    </div>
</div>
</script>
<script type="text/html" id="templateTaxonomyFieldItem">
<?php
$taxonomyFields = array();
foreach($this->metaboxFieldKeyList as $field) {
    $field['name'] = 'ffc-config-posttype[{{POSTTYPE_NO}}][taxonomy][{{TAXONOMY_NO}}][fields][{{TAXONOMY_FIELD_NO}}][' . $field['key'] . ']';
    $taxonomyFields[] = $field;
}
$taxonomyFields = $ffCollection->field->setDefaultConfig($taxonomyFields);
?>
<div class="item taxonomyFieldItem" data-taxonomy-field-no="{{TAXONOMY_FIELD_NO}}">
    <h4 class="handle">
        <button type="button" class="removeItemButton"><span class="dashicons dashicons-dismiss"></span></button>
        <span class="taxonomyFieldHandle dashicons dashicons-leftright ui-sortable-handle"></span>
        <span class="title"></span>&nbsp;<span class="key"></span>
    </h4>
    <div class="group">
        <table class="form-table table-definelist mb-0">
            <?php foreach ($taxonomyFields as $field) : ?>
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
                <td colspan="2"><button type="button" class="button button-primary viewExampleCode" data-view-code="taxonomy">使用例を表示する</button></td>
            </tr>
        </table>
    </div>
</div>
</script>
<script>
    var exampleCode = <?php echo json_encode($this->exampleCode); ?>;
</script>
