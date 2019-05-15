<?php

require FFCOLLECTION_PLUGIN_DIR_PATH . 'config/class/FfcConfigPage.php';

class FfcConfigPosttype extends FfcConfigPage
{

    const PAGE_NAME = 'posttype';
    const PAGE_LABEL = 'カスタム投稿タイプ';
    protected $fieldKeyList = array(
        array(
            'key'   => 'name',
            'title' => '表示名',
            'type'  => 'text'
        ),
        array(
            'key'   => 'slug',
            'title' => 'スラッグ',
            'type'  => 'text'
        ),
        array(
            'key'   => 'icon',
            'title' => 'アイコン',
            'type'  => 'text',
            'description' => 'WordPressの<a href="https://developer.wordpress.org/resource/dashicons/" target="_blank">Dashicons</a>の名称を入力してください<br>使用したいアイコンをクリックすると上部に大きく表示されますので、その隣に記載されている名称(dashicons-***)を入力してください'
        ),
        array(
            'key'   => 'position',
            'title' => 'メニュー位置',
            'type'  => 'number',
            'min'   => 1,
            'max'   => 99,
            'step'  => 1,
            'description' => '<a href="https://developer.wordpress.org/reference/functions/add_menu_page/#menu-structure" target="_blank">既存のメニュー位置</a>を参考にしてください'
        ),
        array(
            'key'   => 'title-placeholder',
            'title' => '記事タイトルのプレースホルダー',
            'type'  => 'text'
        ),
        array(
            'key'       => 'supports',
            'title'     => '利用する初期入力項目',
            'type'      => 'checkbox',
            'multiply'  => 1,
            'delimiter' => '<br>',
            'options'   => array(
                'title'     => 'タイトル',
                'editor'    => '本文',
                'thumbnail' => 'アイキャッチ'
            )
        ),
        array(
            'key'       => 'archive',
            'title'     => '利用するアーカイブページ',
            'type'      => 'checkbox',
            'multiply'  => 1,
            'delimiter' => '<br>',
            'options'   => array(
                'top'   => '投稿タイプトップ',
                'year'  => '年別アーカイブ',
                'month' => '月別アーカイブ'
            )
        )
    );
    protected $metaboxFieldKeyList = array(
        array(
            'key'   => 'title',
            'title' => '表示名',
            'type'  => 'text'
        ),
        array(
            'key'   => 'key',
            'title' => '名前(name属性)',
            'type'  => 'text'
        ),
        array(
            'key'         => 'type',
            'title'       => 'タイプ',
            'type'        => 'select',
            'description' => 'mapを使用する時は<a href="admin.php?page=ffc-config-const">定数設定</a>でAPIキーを「GOOGLE_MAPS_API_KEY」として設定してください',
            'options'     => array(
                'text'     => 'text',
                'textarea' => 'textarea',
                'select'   => 'select',
                'radio'    => 'radio',
                'checkbox' => 'checkbox',
                'number'   => 'number',
                'image'    => 'image',
                'color'    => 'color',
                'date'     => 'date',
                'file'     => 'file',
                'map'      => 'map',
                'wysiwyg'  => 'wysiwyg'
            )
        ),
        array(
            'key'   => 'description',
            'title' => '説明',
            'type'  => 'text'
        ),
        array(
            'key'     => 'title-hide',
            'title'   => '表示名非表示',
            'type'    => 'checkbox',
            'options' => array(
                1 => ''
            )
        ),
        array(
            'key'     => 'save-empty',
            'title'   => '値無し時の保存',
            'type'    => 'checkbox',
            'options' => array(
                1 => ''
            )
        ),

        array(
            'key'     => 'multiply',
            'title'   => '複数フィールド',
            'type'    => 'checkbox',
            'options' => array(
                1 => ''
            )
        ),
        array(
            'key'     => 'sortable',
            'title'   => '並び替え可',
            'type'    => 'checkbox',
            'options' => array(
                1 => ''
            )
        ),
        array(
            'key'   => 'placeholder',
            'title' => 'プレースホルダー',
            'type'  => 'text'
        ),
        array(
            'key'   => 'map-placeholder',
            'title' => 'プレースホルダー',
            'type'  => 'group',
            'item'  => array(
                array(
                    'key'         => 'lat',
                    'title'       => '緯度',
                    'type'        => 'text'
                ),
                array(
                    'key'         => 'lng',
                    'title'       => '経度',
                    'type'        => 'text'
                ),
                array(
                    'key'         => 'zoom',
                    'title'       => '拡大率',
                    'type'        => 'text'
                )
            )
        ),
        array(
            'key'         => 'options',
            'title'       => '選択肢',
            'type'        => 'textarea',
            'description' => '選択肢を改行区切りで入力してください<br>各行が選択肢のラベルになります<br>値(value)は0からの連番となります<br>値(value)を任意に設定するにはすべての行を:(半角)で区切ってください(value:ラベル)<br>そうでないものが1つでもあれば値(value)は0からの連番となり入力文字(:を含む)がラベルとなります'
        ),
        array(
            'key'     => 'html',
            'title'   => 'HTML可',
            'type'    => 'checkbox',
            'options' => array(
                1 => ''
            )
        ),

        array(
            'key'   => 'min',
            'title' => '最小値',
            'type'  => 'number',
            'min'   => false,
            'max'   => false,
            'step'  => 1
        ),
        array(
            'key'   => 'max',
            'title' => '最大値',
            'type'  => 'number',
            'min'   => false,
            'max'   => false,
            'step'  => 1
        ),
        array(
            'key'   => 'step',
            'title' => 'ステップ',
            'type'  => 'number',
            'min'   => 1,
            'max'   => false,
            'step'  => 1
        ),
        array(
            'key'   => 'rows',
            'title' => '初期行数',
            'type'  => 'number',
            'min'   => 1,
            'max'   => false,
            'step'  => 1
        ),
        array(
            'key'   => 'unselected',
            'title' => '非選択のテキスト',
            'type'  => 'text'
        ),
        array(
            'key'         => 'filetype',
            'title'       => 'ファイルタイプ',
            'type'        => 'text',
            'description' => '許可するファイルのMIMEタイプをカンマ区切りで入力してください<br>例えばPDFであればapplication/pdfです<br>その他のMIMEタイプについては<a href="https://wpdocs.osdn.jp/%E9%96%A2%E6%95%B0%E3%83%AA%E3%83%95%E3%82%A1%E3%83%AC%E3%83%B3%E3%82%B9/wp_get_mime_types#.E3.83.87.E3.83.95.E3.82.A9.E3.83.AB.E3.83.88.E3.81.AE_MIME_.E3.82.BF.E3.82.A4.E3.83.97.E3.81.A8.E6.8B.A1.E5.BC.B5.E5.AD.90" target="_blank">こちら</a>を参照してください<br>空欄の場合はすべてのファイルを許可します(ただしWordPressが許可しているもののみ)'
        )
    );
    protected $typeKeyList = array(
        'checkbox' => array(
            'key',
            'type',
            'title',
            'description',
            'title-hide',
            // 'save-empty',
            // 'multiply',
            'options'
        ),
        'color' => array(
            'key',
            'type',
            'title',
            'description',
            'title-hide'
            // 'save-empty'
        ),
        'date' => array(
            'key',
            'type',
            'title',
            'description',
            'title-hide'
            // 'save-empty'
        ),
        'image' => array(
            'key',
            'type',
            'title',
            'description',
            'title-hide',
            // 'save-empty',
            // 'multiply',
            'sortable'
        ),
        'file' => array(
            'key',
            'type',
            'title',
            'description',
            'title-hide',
            // 'save-empty',
            'filetype',
            // 'multiply',
            'sortable'
        ),
        'map' => array(
            'key',
            'type',
            'title',
            'description',
            'title-hide',
            // 'save-empty',
            'map-placeholder'
        ),
        'number' => array(
            'key',
            'type',
            'title',
            'description',
            'title-hide',
            // 'save-empty',
            'min',
            'max',
            'step'
        ),
        'radio' => array(
            'key',
            'type',
            'title',
            'description',
            'title-hide',
            // 'save-empty',
            'options'
        ),
        'select' => array(
            'key',
            'type',
            'title',
            'description',
            'title-hide',
            // 'save-empty',
            'unselected',
            'options'
        ),
        'text' => array(
            'key',
            'type',
            'title',
            'description',
            'title-hide',
            // 'save-empty',
            'placeholder',
            'html'
            // 'multiply'
        ),
        'textarea' => array(
            'key',
            'type',
            'title',
            'description',
            'title-hide',
            // 'save-empty',
            'placeholder',
            'rows',
            'html'
            // 'multiply'
        ),
        'wysiwyg' => array(
            'key',
            'type',
            'title',
            'description',
            'title-hide'
            // 'save-empty'
        )
    );
    protected $intValueList = array(
        'title-hide',
        'save-empty',
        'multiply',
        'sortable',
        'html',
        'min',
        'max',
        'step',
        'rows'
    );
    protected $exampleCode = array();

    protected function adminInitialize()
    {
        parent::adminInitialize();

        add_action('update_option_ffc-config-posttype', array($this, 'updatePageConfig'), 10, 0);

        $this->flushRewriteRules();

        foreach (array('metabox', 'taxonomy') as $dir) {
            $path = FFCOLLECTION_PLUGIN_DIR_PATH . 'config/view/example/' . $dir;
            if ($dh = opendir($path)) {
                while (false !== ($fieldFileName = readdir($dh))) {
                    if (('.' === $fieldFileName)
                        || ('..' === $fieldFileName)
                        || !is_file($path . '/' . $fieldFileName)
                    ) {
                        continue;
                    }

                    $fieldName = wp_basename($fieldFileName, '.php');
                    $this->exampleCode[$dir][$fieldName] = esc_html(trim(file_get_contents($path . '/' . $fieldFileName)));
                }
                closedir($dh);
            }
        }
    }

    public function description()
    {
        parent::description();

        echo '<button type="button" class="viewDescription">説明を表示する</button>';
        echo '<div class="funcDescription"><p>各フィールドの「使用例を表示する」ボタンを押すとテンプレートに記述するコード例が表示されます。<br>「コードをクリップボードにコピーする」ボタンでコピーされますので、テンプレートに貼り付けて使用できます。<br>「例」ですので使用する場所や表示方法など、必要に応じて変更してご使用ください。</p>';
        echo '<p>カスタム投稿タイプのスラッグを「post」「page」にすると、それぞれ既存の「投稿」「固定ページ」の設定変更を行うことができます。<br>ただし「利用する初期入力項目」と「利用するアーカイブページ」は適用されません。<br>また、この時「表示名」を未入力にすると、既存の「投稿」「固定ページ」を非表示にすることができます。<br>「post」の場合にタクソノミーのスラッグを「category」「post_tag」にすると、それぞれ「カテゴリー」「タグ」の設定変更を行うことができます。<br>ただし「階層」は適用されません。</p>';
        echo '<p>その他「attachment」「revision」「nav_menu_item」「action」「order」「theme」はスラッグに使用できません。</p></div>';
    }

    protected function parse($arrValue)
    {
        $ret = array();
        foreach ($arrValue as $slug => $setting) {
            $posttype = array(
                'slug'              => $slug,
                'name'              => '',
                'position'          => '',
                'icon'              => '',
                'title-placeholder' => '',
                'supports'          => array(),
                'archive'           => array(),
                'metabox'           => array(),
                'taxonomy'          => array()
            );

            if ($this->exists('name', $setting)
                && is_string($setting['name'])
            ) {
                $posttype['name'] = $setting['name'];
            }
            if ($this->exists('position', $setting, false, false, false)
                && is_numeric($setting['position'])
            ) {
                $posttype['position'] = $setting['position'];
            }
            if ($this->exists('icon', $setting)
                && is_string($setting['icon'])
            ) {
                $posttype['icon'] = $setting['icon'];
            }
            if ($this->exists('title-placeholder', $setting)
                && is_string($setting['title-placeholder'])
            ) {
                $posttype['title-placeholder'] = $setting['title-placeholder'];
            }
            if ($this->exists('supports', $setting)
                && is_array($setting['supports'])
            ) {
                $posttype['supports'] = $setting['supports'];
            }
            if ($this->exists('archive', $setting)
                && is_array($setting['archive'])
            ) {
                $posttype['archive'] = $setting['archive'];
            }

            // カスタムフィールド設定
            $metabox = $this->parseMetabox($setting);
            if (false !== $metabox) {
                $posttype['metabox'] = $metabox;
            }

            // カスタムタクソノミー設定
            $taxonomy = $this->parseTaxonomy($setting);
            if (false !== $taxonomy) {
                $posttype['taxonomy'] = $taxonomy;
            }

            $ret[] = $posttype;
        }

        return $ret;
    }

    protected function parseMetabox($setting)
    {
        if (!$this->exists('metabox', $setting, false, false, false)
            || !is_array($setting['metabox'])
        ) {
            return false;
        }

        $ret = array();
        foreach ($setting['metabox'] as $metabox) {
            $arrMetabox = array(
                'title'  => '',
                'fields' => array()
            );
            if ($this->exists('title', $metabox, true, false, false)
                && is_string($metabox['title'])
            ) {
                $arrMetabox['title'] = $metabox['title'];
            }

            $fields = array();
            foreach ($metabox['fields'] as $field) {
                if (!$this->exists('title', $field)) {
                    $field['title'] = '';
                }
                // optionsは配列を改行区切りにする
                if ($this->exists('options', $field, false, false, false)) {
                    $optionString = '';
                    if (is_array($field['options'])) {
                        $isAssociative = $this->isAssociative($field['options'], true);
                        $options = array();
                        foreach ($field['options'] as $optionKey => $option) {
                            $optionString .= "\n";
                            if ($isAssociative) {
                                $options[] = $optionKey . ':' . $option;
                            } else {
                                $options[] = $option;
                            }
                        }
                        $optionString = implode("\n", $options);
                    }
                    $field['options'] = $optionString;
                }

                if ('map' === $field['type']
                    && $this->exists('placeholder', $field, false, false, false)
                    && is_array($field['placeholder'])
                ) {
                    $field['map-placeholder'] = $field['placeholder'];
                    unset($field['placeholder']);
                }

                $fields[] = $field;
            }
            $arrMetabox['fields'] = $fields;

            $ret[] = $arrMetabox;
        }

        return $ret;
    }

    protected function parseTaxonomy($setting)
    {
        if (!$this->exists('taxonomy', $setting, false, false, false)
            || !is_array($setting['taxonomy'])
        ) {
            return false;
        }

        $ret = array();
        foreach ($setting['taxonomy'] as $slug => $taxonomy) {
            $arrTaxonomy = array(
                'slug'   => $slug,
                'name'   => '',
                'child'  => '',
                'fields' => array()
            );

            if ($this->exists('name', $taxonomy, true, false, false)
                && is_string($taxonomy['name'])
            ) {
                $arrTaxonomy['name'] = $taxonomy['name'];
            }
            if ($this->exists('child', $taxonomy, true, false, false)) {
                $arrTaxonomy['child'] = $taxonomy['child'];
            }

            if ($this->exists('fields', $taxonomy, false, false, false)) {
                $fields = array();
                foreach ($taxonomy['fields'] as $field) {
                    if (!$this->exists('title', $field)) {
                        $field['title'] = '';
                    }
                    // optionsは配列を改行区切りにする
                    if ($this->exists('options', $field, false, false, false)) {
                        $optionString = '';
                        if (is_array($field['options'])) {
                            $isAssociative = $this->isAssociative($field['options'], true);
                            $options = array();
                            foreach ($field['options'] as $optionKey => $option) {
                                $optionString .= "\n";
                                if ($isAssociative) {
                                    $options[] = $optionKey . ':' . $option;
                                } else {
                                    $options[] = $option;
                                }
                            }
                            $optionString = implode("\n", $options);
                        }
                        $field['options'] = $optionString;
                    }

                    if ('map' === $field['type']
                        && $this->exists('placeholder', $field, false, false, false)
                        && is_array($field['placeholder'])
                    ) {
                        $field['map-placeholder'] = $field['placeholder'];
                        unset($field['placeholder']);
                    }

                    $fields[] = $field;
                }
                $arrTaxonomy['fields'] = $fields;
            }

            $ret[] = $arrTaxonomy;
        }

        return $ret;
    }

    public function preUpdatePageConfig($arrValue)
    {
        $arrPosttype = array();

        foreach ($arrValue as $posttype) {
            if (!$this->exists('slug', $posttype, false, false, false)) {
                continue;
            }

            $arrPosttype[$posttype['slug']] = array();

            // 標準の投稿削除機能のため、表示名は空文字でも保存する
            if ($this->exists('name', $posttype, true, false, false)
                && is_string($posttype['name'])
            ) {
                $arrPosttype[$posttype['slug']]['name'] = $posttype['name'];
            }
            if ($this->exists('position', $posttype, false, false, false)
                && is_string($posttype['position'])
            ) {
                if (!is_int($posttype['position'])) {
                    $posttype['position'] = intval($posttype['position']);
                }
                $arrPosttype[$posttype['slug']]['position'] = $posttype['position'];
            }
            if ($this->exists('icon', $posttype, false, false, false)
                && is_string($posttype['icon'])
            ) {
                $arrPosttype[$posttype['slug']]['icon'] = $posttype['icon'];
            }
            if ($this->exists('title-placeholder', $posttype, false, false, false)
                && is_string($posttype['title-placeholder'])
            ) {
                $arrPosttype[$posttype['slug']]['title-placeholder'] = $posttype['title-placeholder'];
            }
            if ($this->exists('supports', $posttype, false, false, false)
                && is_array($posttype['supports'])
            ) {
                $arrPosttype[$posttype['slug']]['supports'] = $posttype['supports'];
            }
            if ($this->exists('archive', $posttype, false, false, false)
                && is_array($posttype['archive'])
            ) {
                $arrPosttype[$posttype['slug']]['archive'] = $posttype['archive'];
            }

            // カスタムフィールド設定
            $metabox = $this->preUpdateMetabox($posttype);
            if (false !== $metabox) {
                $arrPosttype[$posttype['slug']]['metabox'] = $metabox;
            }

            // カスタムタクソノミー設定
            $taxonomy = $this->preUpdateTaxonomy($posttype);
            if (false !== $taxonomy) {
                $arrPosttype[$posttype['slug']]['taxonomy'] = $taxonomy;
            }
        }

        return $arrPosttype;
    }

    public function preUpdateMetabox($posttype)
    {
        if (!$this->exists('metabox', $posttype, false, false, false)
            || !is_array($posttype['metabox'])
        ) {
            return false;
        }

        $ret = array();
        foreach ($posttype['metabox'] as $metabox) {
            if (!$this->exists('fields', $metabox, false, false, false)
                || !is_array($metabox['fields'])
            ) {
                continue;
            }

            $fields = array();
            // メタボックス内のフィールドを見ていく
            foreach ($metabox['fields'] as $field) {
                // フィールドの各項目を見ていく
                $tempField = $field;
                foreach ($tempField as $key => $value) {
                    // 表示切り替えのためにすべての項目がPOSTされてくるので
                    // 保存時に不要な項目は削除する
                    if (!in_array($key, $this->typeKeyList[$field['type']], true)) {
                        unset($field[$key]);
                        continue;
                    }

                    // 空配列もしくは空文字ならキーごと削除する
                    if (empty($value)) {
                        unset($field[$key]);
                        continue;
                    }

                    // 数値項目はint型に変換しておく
                    if (in_array($key, $this->intValueList, true)) {
                        $field[$key] = intval($value);
                    }

                    // optionsは改行区切りで配列にする
                    if ('options' === $key) {
                        $field[$key] = array();
                        if (0 !== strlen($value)) {
                            $options = str_replace("\r\n", "\n", $value);
                            $options = str_replace("\r", "\n", $options);
                            $options = explode("\n", $options);
                            $options = array_values(array_filter($options, 'strlen'));
                            // すべての要素の値の中にそれぞれ1箇所のみ(頭尾以外)「:」があれば連想配列とする
                            $arr = preg_grep('/^[^:]+:[^:]+$/', $options, PREG_GREP_INVERT);
                            // そうでないものが1つでもあれば添え字配列とする
                            if (count($arr) > 0) {
                                // 添え字配列
                                $field[$key] = $options;
                            } else {
                                // 連想配列
                                foreach ($options as $optionValue) {
                                    // 「:」で区切ってキーと値にする
                                    $arr = explode(':', $optionValue);
                                    $field[$key][$arr[0]] = $arr[1];
                                }
                            }
                        }
                    }

                    // mapのプレースホルダーのみ配列なのでkeyが違う
                    // 合わせるために変換を行う
                    if ('map-placeholder' === $key) {
                        $field['placeholder'] = $value;
                        unset($field[$key]);
                    }
                }

                $fields[] = $field;
            }

            $ret[] = array(
                'title'  => $metabox['title'],
                'fields' => $fields
            );
        }

        return $ret;
    }

    public function preUpdateTaxonomy($posttype)
    {
        if (!$this->exists('taxonomy', $posttype, false, false, false)
            || !is_array($posttype['taxonomy'])
        ) {
            return false;
        }

        $ret = array();
        foreach ($posttype['taxonomy'] as $taxonomy) {
            $arrTaxonomy = array();

            if ($this->exists('name', $taxonomy, false, false, false)
                && is_string($taxonomy['name'])
            ) {
                $arrTaxonomy['name'] = $taxonomy['name'];
            }
            if ($this->exists('child', $taxonomy, false, false, false)) {
                $arrTaxonomy['child'] = intval($taxonomy['child']);
            }

            if ($this->exists('fields', $taxonomy, false, false, false)
                && is_array($taxonomy['fields'])
            ) {
                $arrTaxonomy['fields'] = array();
                // メタボックス内のフィールドを見ていく
                foreach ($taxonomy['fields'] as $field) {
                    // フィールドの各項目を見ていく
                    $tempField = $field;
                    foreach ($tempField as $key => $value) {
                        // 表示切り替えのためにすべての項目がPOSTされてくるので
                        // 保存時に不要な項目は削除する
                        if (!in_array($key, $this->typeKeyList[$field['type']], true)) {
                            unset($field[$key]);
                            continue;
                        }

                        // 空配列もしくは空文字ならキーごと削除する
                        if (empty($value)) {
                            unset($field[$key]);
                            continue;
                        }

                        // 数値項目はint型に変換しておく
                        if (in_array($key, $this->intValueList, true)) {
                            $field[$key] = intval($value);
                        }

                        // optionsは改行区切りで配列にする
                        if ('options' === $key) {
                            $field[$key] = array();
                            if (0 !== strlen($value)) {
                                $options = str_replace("\r\n", "\n", $value);
                                $options = str_replace("\r", "\n", $options);
                                $options = explode("\n", $options);
                                $options = array_values(array_filter($options, 'strlen'));
                                // すべての要素の値の中にそれぞれ1箇所のみ(頭尾以外)「:」があれば連想配列とする
                                $arr = preg_grep('/^[^:]+:[^:]+$/', $options, PREG_GREP_INVERT);
                                // そうでないものが1つでもあれば添え字配列とする
                                if (count($arr) > 0) {
                                    // 添え字配列
                                    $field[$key] = $options;
                                } else {
                                    // 連想配列
                                    foreach ($options as $optionValue) {
                                        // 「:」で区切ってキーと値にする
                                        $arr = explode(':', $optionValue);
                                        $field[$key][$arr[0]] = $arr[1];
                                    }
                                }
                            }
                        }

                        // mapのプレースホルダーのみ配列なのでkeyが違う
                        // 合わせるために変換を行う
                        if ('map-placeholder' === $key) {
                            $field['placeholder'] = $value;
                            unset($field[$key]);
                        }
                    }

                    $arrTaxonomy['fields'][] = $field;
                }
            }

            $ret[$taxonomy['slug']] = $arrTaxonomy;
        }
        return $ret;
    }

    public function flushRewriteRules()
    {
        if (!is_admin()) {
            return;
        }

        $doFlush = intval(get_option('ffc-config-rewrite', 0));
        if ($doFlush) {
            // リライトルール再生成フラグが立っていれば現在のリライトルールを削除しフラグを落とす
            flush_rewrite_rules();
            update_option('ffc-config-rewrite', 0);
        }
    }

    public function updatePageConfig()
    {
        // カスタム投稿タイプやカスタムタクソノミーが変更されているかもしれないのでリライトルールを再生成する
        // リロード時に再生成させるためのフラグを立てて置く
        update_option('ffc-config-rewrite', 1);
    }

}
