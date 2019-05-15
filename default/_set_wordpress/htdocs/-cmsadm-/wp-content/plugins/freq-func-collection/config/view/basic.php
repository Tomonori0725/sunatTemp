<table class="form-table table-checklist table-striped">
    <caption>
        <h4>管理画面表示</h4>
    </caption>
    <tbody>
        <?php
        $arrValue = $value;
        foreach ($this->adminFieldKeyList as $key => $setting) {
            $name = 'ffc-config-' . $pageName . '[' . $key . ']';
            ?>
            <tr>
                <td><?php include FFCOLLECTION_PLUGIN_DIR_PATH . 'config/view/field.php'; ?></td>
                <th>
                    <label for="<?php echo esc_attr($name); ?>"><?php echo esc_html($setting['label']); ?></label>
                    <?php if ($this->exists('description', $setting, false)) : ?>
                        <p class="description"><label for="<?php echo esc_attr($name); ?>"><?php echo esc_html($setting['description']); ?></label></p>
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
        <h4>フロントheadタグ内出力</h4>
    </caption>
    <tbody>
        <?php
        $arrValue = $value;
        foreach ($this->headFieldKeyList as $key => $setting) {
            $name = 'ffc-config-' . $pageName . '[' . $key . ']';
            ?>
            <tr>
                <td><?php include FFCOLLECTION_PLUGIN_DIR_PATH . 'config/view/field.php'; ?></td>
                <th>
                    <label for="<?php echo esc_attr($name); ?>"><?php echo esc_html($setting['label']); ?></label>
                    <?php if ($this->exists('description', $setting, false)) : ?>
                        <p class="description"><label for="<?php echo esc_attr($name); ?>"><?php echo esc_html($setting['description']); ?></label></p>
                    <?php endif; ?>
                </th>
            </tr>
            <?php
        }
        ?>
    </tbody>
</table>

<table class="form-table table-addablelist table-striped">
    <caption>
        <h4>カスタムメニュー</h4>
        <button type="button" class="viewDescription">説明を表示する</button>
        <div class="funcDescription">
            <p class="description">「外観 > メニュー」の位置管理に新たに位置を追加して、紐づけたメニューを設置できるようにします。<br>キーと名前(管理画面上での名前)をセットで入力してください。<br>例えばキーを「global_navigation」、名前を「グローバルナビゲーション」とすると位置管理に「グローバルナビゲーション」が追加されます。<br>作成したメニューを「グローバルナビゲーション」に紐づけ、テンプレート上の表示したい箇所で<pre><code>&lt;?php wp_nav_menu(array(
        'theme_location' => 'global_navigation'
    )); ?&gt;</code></pre>と記述することで作成したメニューを表示できます。<br>wp_nav_menuを記述する際のその他のパラメーターについては<a href="https://wpdocs.osdn.jp/%E3%83%86%E3%83%B3%E3%83%97%E3%83%AC%E3%83%BC%E3%83%88%E3%82%BF%E3%82%B0/wp_nav_menu" target="_blank">公式ドキュメント</a>を参照してください。</p>
        </div>
    </caption>
    <thead>
        <tr>
            <th></th>
            <th>キー</th>
            <th>名前</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $arrValue = $value;
        foreach ($this->menuFieldKeyList as $key => $setting) {
            $name = 'ffc-config-' . $pageName . '[' . $key . ']';
            if ($this->exists($key, $arrValue)
                && is_array($arrValue[$key])
            ) {
                foreach ($arrValue[$key] as $var) {
                    ?>
                    <tr>
                        <td class="delete"><button type="button" class="removeItemButton"><span class="dashicons dashicons-dismiss"></span></button></td>
                        <td data-colname="キー"><input type="text" class="widefat" name="<?php echo esc_attr($name); ?>[key][]" value="<?php echo esc_attr($var['key']); ?>"></td>
                        <td data-colname="名前"><input type="text" class="widefat" name="<?php echo esc_attr($name); ?>[label][]" value="<?php echo esc_attr($var['label']); ?>"></td>
                    </tr>
                    <?php
                }
            }
        }
        ?>
    </tbody>
</table>
<button type="button" id="addMenuFieldButton" class="button button-primary addButton">カスタムメニューを追加する</button>

<table class="form-table query table-striped">
    <caption>
        <h4>クエリ変数</h4>
        <button type="button" class="viewDescription">説明を表示する</button>
        <div class="funcDescription">
            <p class="description">URLのクエリストリング(パラメーター)にキーを追加し、get_query_varで取得できるようになります。<br>例えば「country」を追加しURLの最後に<code>?country=japan</code>などを付けてアクセスするとテーマ内で<pre><code>&lt;php echo get_query_var('country'); ?&gt;</code></pre>とすると<code>japan</code>と表示されます。<br>これを利用して、投稿の絞り込み機能や表示の切り替え機能などの作成が可能になります。(本プラグインにこの機能はありません)<br>追加するキーを改行区切りで入力してください。</p>
        </div>
    </caption>
    <tbody>
        <?php
        $arrValue = $value;
        foreach ($this->queryFieldKeyList as $key => $setting) {
            $name = 'ffc-config-' . $pageName . '[' . $key . ']';
            ?>
            <tr>
                <td><?php include FFCOLLECTION_PLUGIN_DIR_PATH . 'config/view/field.php'; ?></td>
            </tr>
            <?php
        }
        ?>
    </tbody>
</table>

<table class="form-table table-checklist table-striped">
    <caption>
        <h4>その他</h4>
    </caption>
    <tbody>
        <?php
        $arrValue = $value;
        foreach ($this->otherFieldKeyList as $key => $setting) {
            $name = 'ffc-config-' . $pageName . '[' . $key . ']';
            ?>
            <tr>
                <td><?php include FFCOLLECTION_PLUGIN_DIR_PATH . 'config/view/field.php'; ?></td>
                <th>
                    <label for="<?php echo esc_attr($name); ?>"><?php echo esc_html($setting['label']); ?></label>
                    <?php if ($this->exists('description', $setting, false)) : ?>
                        <p class="description"><label for="<?php echo esc_attr($name); ?>"><?php echo esc_html($setting['description']); ?></label></p>
                    <?php endif; ?>
                </th>
            </tr>
            <?php
        }
        ?>
    </tbody>
</table>

<script type="text/html" id="templateMenuFieldItem">
<tr>
    <td><button type="button" class="removeItemButton"><span class="dashicons dashicons-dismiss"></span></button></td>
    <td data-colname="キー"><input type="text" class="widefat" name="ffc-config-basic[menu][key][]" value=""></td>
    <td data-colname="名前"><input type="text" class="widefat" name="ffc-config-basic[menu][label][]" value=""></td>
</tr>
</script>
