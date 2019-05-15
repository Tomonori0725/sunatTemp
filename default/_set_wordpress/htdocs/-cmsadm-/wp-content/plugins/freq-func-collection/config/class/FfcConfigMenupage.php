<?php

require FFCOLLECTION_PLUGIN_DIR_PATH . 'config/class/FfcConfigPage.php';

class FfcConfigMenupage extends FfcConfigPage
{

    const PAGE_NAME = 'menupage';
    const PAGE_LABEL = 'メニューページ';
    protected $defaults = array(
        'title'        => '',
        'parent'       => array(
            'select' => '',
            'other'  => ''
        ),
        'template'     => '',
        'cap'          => array(
            'select' => '',
            'other'  => ''
        ),
        'menu'         => '',
        'icon'         => '',
        'position'     => '',
        'fields'       => array()
    );
    protected $fieldKeyList = array(
        array(
            'key'   => 'title',
            'title' => '表示名',
            'type'  => 'text'
        ),
        array(
            'key'   => 'slug',
            'title' => 'スラッグ',
            'type'  => 'text'
        ),
        array(
            'key'         => 'menu',
            'title'       => 'メニュー名',
            'type'        => 'text',
            'description' => 'このメニューを親メニューにする場合のメニュー名を入力してください'
        ),
        array(
            'key'         => 'icon',
            'title'       => 'アイコン',
            'type'        => 'text',
            'description' => 'このメニューを親メニューにする場合のアイコンを入力してください<br>WordPressの<a href="https://developer.wordpress.org/resource/dashicons/" target="_blank">Dashicons</a>の名称を入力してください<br>使用したいアイコンをクリックすると上部に大きく表示されますので、その隣に記載されている名称(dashicons-***)を入力してください'
        ),
        array(
            'key'         => 'position',
            'title'       => 'メニュー位置',
            'type'        => 'number',
            'min'         => 1,
            'max'         => 99,
            'step'        => 1,
            'description' => 'このメニューを親メニューにする場合のメニュー位置を入力してください<br><a href="https://developer.wordpress.org/reference/functions/add_menu_page/#menu-structure" target="_blank">既存のメニュー位置</a>を参考にしてください'
        ),
        array(
            'key'   => 'parent',
            'title' => '親メニュー',
            'type'  => 'group',
            'item'  => array(
                array(
                    'key'        => 'select',
                    'type'       => 'select',
                    'unselected' => 'その他',
                    'options'    => array(
                        'themes.php'     => '外観',
                        'manage_options' => '設定'
                    )
                ),
                array(
                    'key'         => 'other',
                    'type'        => 'text',
                    'description' => ''
                )
            ),
            'description' => 'このメニューを親メニューの場合は「その他」を選択し空欄にしてください<br>このメニューを子メニューにする場合は親メニューを選択してください<br>選択肢以外を親メニューにする場合には「その他」を選択し親メニューのスラッグを入力してください'
        ),
        array(
            'key'   => 'cap',
            'title' => '必要権限',
            'type'  => 'group',
            'item'  => array(
                array(
                    'key'        => 'select',
                    'type'       => 'select',
                    'unselected' => 'その他',
                    'options'    => array(
                        'manage_sites'       => 'manage_sites (マルチサイトを作成する権限：特権管理者以上)',
                        'edit_theme_options' => 'edit_theme_options (外観を編集する権限：管理者以上)',
                        'manage_options'     => 'manage_options (設定を編集する権限：管理者以上)',
                        'edit_others_posts'  => 'edit_others_posts (他のユーザーの投稿を編集する権限：編集者以上)',
                        'publish_posts'      => 'publish_posts (投稿を公開する権限：投稿者以上)',
                        'edit_posts'         => 'edit_posts (投稿を新規作成する権限：寄稿者以上)'
                    )
                ),
                array(
                    'key'         => 'other',
                    'type'        => 'text'
                )
            ),
            'description' => 'このメニューページを利用するために必要な権限を選択してください<br>選択肢以外の権限が必要な場合には「その他」を選択しその権限を入力してください<br>権限については<a href="https://wpdocs.osdn.jp/%E3%83%A6%E3%83%BC%E3%82%B6%E3%83%BC%E3%81%AE%E7%A8%AE%E9%A1%9E%E3%81%A8%E6%A8%A9%E9%99%90" target="_blank">こちら</a>を参照してください'
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

        $path = FFCOLLECTION_PLUGIN_DIR_PATH . 'config/view/example/menupage';
        if ($dh = opendir($path)) {
            while (false !== ($fieldFileName = readdir($dh))) {
                if (('.' === $fieldFileName)
                    || ('..' === $fieldFileName)
                    || !is_file($path . '/' . $fieldFileName)
                ) {
                    continue;
                }

                $fieldName = wp_basename($fieldFileName, '.php');
                $this->exampleCode['menupage'][$fieldName] = esc_html(trim(file_get_contents($path . '/' . $fieldFileName)));
            }
            closedir($dh);
        }
    }

    public function description()
    {
        parent::description();

        echo '<button type="button" class="viewDescription">説明を表示する</button>';
        echo '<div class="funcDescription"><p>メニューページとは、管理メニューに項目を追加し、その項目に割り当てるページを作成する機能です。<br>ページにフィールドを追加することで新たに設定ページを作成することができます。</p>';
        echo '<p>例えば画像アップロードのフィールドを持ったメニューページを追加すれば、テーマ側でその画像を使用してカルーセルを実装することができます。(本プラグインにカルーセルを実装する機能はありません)</p>';
        echo '<p>各フィールドの「使用例を表示する」ボタンを押すとテンプレートに記述するコード例が表示されます。<br>「コードをクリップボードにコピーする」ボタンでコピーされますので、テンプレートに貼り付けて使用できます。<br>「例」ですので使用する場所や表示方法など、必要に応じて変更してご使用ください。</p></div>';
    }

    protected function parse($arrValue)
    {
        global $menu;
        foreach ($menu as $menuItem) {
            if (strlen($menuItem[0])) {
                $pos = strpos($menuItem[0], '<');
                if ($pos) {
                    $menuItem[0] = trim(substr($menuItem[0], 0, $pos));
                }
                $options[$menuItem[2]] = $menuItem[0];
            }
        }
        foreach ($this->fieldKeyList as &$list) {
            if ('parent' === $list['key']) {
                $list['item'][0]['options'] = $options;
                break;
            }
        }
        unset($list);

        $ret = array();
        foreach ($arrValue as $slug => $setting) {
            $setting = array_intersect_key(wp_parse_args($setting, $this->defaults), $this->defaults);
            $setting['slug'] = $slug;

            $fields = array();
            foreach ($setting['fields'] as $field) {
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
            $setting['fields'] = $fields;

            $ret[] = $setting;
        }

        return $ret;
    }

    public function preUpdatePageConfig($arrValue)
    {
        $ret = array();

        foreach ($arrValue as $menupage) {
            if (!$this->exists('slug', $menupage, false, false, false)) {
                continue;
            }

            $ret[$menupage['slug']] = array();

            if ($this->exists('title', $menupage, false, false, false)
                && is_string($menupage['title'])
            ) {
                $ret[$menupage['slug']]['title'] = $menupage['title'];
            }
            if ($this->exists('position', $menupage, false, false, false)
                && is_string($menupage['position'])
            ) {
                if (!is_int($menupage['position'])) {
                    $posttype['position'] = intval($menupage['position']);
                }
                $ret[$menupage['slug']]['position'] = $menupage['position'];
            }
            if ($this->exists('menu', $menupage, false, false, false)
                && is_string($menupage['menu'])
            ) {
                $ret[$menupage['slug']]['menu'] = $menupage['menu'];
            }
            if ($this->exists('icon', $menupage, false, false, false)
                && is_string($menupage['icon'])
            ) {
                $ret[$menupage['slug']]['icon'] = $menupage['icon'];
            }
            if ($this->exists('parent', $menupage, false, false, false)) {
                if (($this->exists('select', $menupage['parent'], false, false, false) && is_string($menupage['parent']['select']))
                    || ($this->exists('other', $menupage['parent'], false, false, false) && is_string($menupage['parent']['other']))
                ) {
                    $ret[$menupage['slug']]['parent'] = $menupage['parent'];
                }
            }
            if ($this->exists('cap', $menupage, false, false, false)) {
                if (($this->exists('select', $menupage['cap'], false, false, false) && is_string($menupage['cap']['select']))
                    || ($this->exists('other', $menupage['cap'], false, false, false) && is_string($menupage['cap']['other']))
                ) {
                    $ret[$menupage['slug']]['cap'] = $menupage['cap'];
                }
            }

            if ($this->exists('fields', $menupage)) {
                $fields = array();
                // メタボックス内のフィールドを見ていく
                foreach ($menupage['fields'] as $field) {
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
                        if ((is_array($value) && empty($value))
                            || (is_string($value) && !strlen($value))
                        ) {
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
                $ret[$menupage['slug']]['fields'] = $fields;
            }
        }

        return $ret;
    }

}
