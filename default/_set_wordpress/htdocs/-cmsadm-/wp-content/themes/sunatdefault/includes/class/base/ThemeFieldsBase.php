<?php

abstract class ThemeFieldsBase extends ThemeClassBase
{

    protected $defaultConfig = array(
        'default'  => array(
            'title'        => '',
            'title-hide'   => false,
            'type'         => 'text',
            'quantity'     => 1,
            'multiply'     => false,
            'min-quantity' => 0,
            'max-quantity' => false,
            'placeholder'  => '',
            'html'         => false,
            'save-empty'   => false
        ),
        'text'     => array(),
        'textarea' => array(
            'rows' => 5
        ),
        'select'   => array(
            'option' => array()
        ),
        'radio'    => array(
            'option' => array()
        ),
        'checkbox' => array(
            'option' => array(),
            'default' => array()
        ),
        'number'   => array(
            'min'  => 0,
            'max'  => false,
            'step' => 1
        ),
        'image'    => array(
            'multiply' => true
        ),
        'file'     => array(),
        'map'      => array(
            'default' => array(
                'lat'  => '',
                'lng'  => '',
                'zoom' => 17
            ),
            'center' => array(
                '34.4600042',
                '134.8524728',
                17
            )
        ),
        'wysiwyg'  => array(
            'html' => true
        )
    );

    public function setDefaultConfig($config)
    {
        foreach ($config as $posttype => $value) {
            if (!$this->exists('metabox', $value)) {
                continue;
            }

            foreach ($value['metabox'] as $sectionNo => $metabox) {
                if (!$this->exists('item', $metabox)) {
                    continue;
                }

                foreach ($metabox['item'] as $name => $setting) {
                    if ($this->exists('type', $setting)
                        && $this->exists($setting['type'], $this->defaultConfig)
                    ) {
                        $typeConfig = $this->defaultConfig[$setting['type']];
                    } else {
                        $typeConfig = $this->defaultConfig[$this->defaultConfig['default']['type']];
                    }
                    $setting = array_merge($this->defaultConfig['default'], $typeConfig, $setting);
                    if (!$this->exists('default', $setting)) {
                        if ($setting['multiply']) {
                            $setting['default'] = array();
                        } else {
                            $setting['default'] = '';
                        }
                    }
                    $config[$posttype]['metabox'][$sectionNo]['item'][$name] = $setting;
                }
            }
        }

        return $config;
    }

    public function createField($name, $value, $setting)
    {
        echo '<div class="field">';
        if ($this->exists('title-hide', $setting) && $setting['title-hide']) {
            echo '<label class="screen-reader-text">';
        } else {
            echo '<label for="' . esc_attr($name) . '">';
        }
        echo esc_html($setting['title']) . '</label>';

        $function = 'createField_' . $setting['type'];
        if (method_exists($this, $function)) {
            $this->{$function}($name, $value, $setting);
        } else {
            echo '<p>定義されていないフィールドタイプです。</p>';
            // なければ表示しない
        }
        echo '</div>';
    }

    protected function createField_text($name, $value, $setting)
    {
        ?>
        <input type="text" id="<?php echo esc_attr($name); ?>" class="widefat" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>" placeholder="<?php echo esc_attr($setting['placeholder']); ?>">
        <?php
    }

    protected function createField_textarea($name, $value, $setting)
    {
        ?>
        <textarea id="<?php echo esc_attr($name); ?>" class="widefat autosize" name="<?php echo esc_attr($name); ?>" rows="<?php echo esc_attr($setting['rows']); ?>" placeholder="<?php echo esc_attr($setting['placeholder']); ?>"><?php echo "\n" . esc_html($value); ?></textarea>
        <?php
    }

    protected function createField_number($name, $value, $setting)
    {
        $min = '';
        $max = '';
        $step = '';
        if ($this->exists('min', $setting, false, false)) {
            $min = ' data-num-min="' . $setting['min'] . '"';
        }
        if ($this->exists('max', $setting, false, false)) {
            $max = ' data-num-max="' . $setting['max'] . '"';
        }
        if ($this->exists('step', $setting, false, false)) {
            $step = ' data-num-step="' . $setting['step'] . '"';
        }
        ?>
        <input type="text" id="<?php echo esc_attr($name); ?>" class="widefat numSpinner" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>"<?php echo $min . $max . $step; ?>>
        <?php
    }

    protected function createField_checkbox($name, $value, $setting)
    {
        if ($setting['multiply']) {
            $nameAttr = $name . '[]';
        }
        $id = $name;
        $index = 1;
        foreach ($setting[options] as $option) {
            if ($setting['multiply']) {
                $checked = in_array($option, $value, true);
                $id = $name . $index;
                $index++;
            } else {
                $checked = $option === $value;
            }
            ?>
            <label><input type="checkbox" id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($nameAttr); ?>" value="<?php echo esc_attr($option); ?>"<?php checked($checked, true); ?>><?php echo esc_html($option); ?></label>
            <?php
        }
    }

    protected function createField_image($name, $value, $setting)
    {
        uploadImageTemplate(array(
            'imageName' => $name,
            'imageId'   => $value,
            'multi'     => $setting['multiply'],
            'sortable'  => true,
            'size'      => 'medium'
        ));
    }

    protected function createField_map($name, $value, $setting)
    {
        ?>
        <div class="mapField" data-latlng="[<?php echo implode(',', $setting['center']); ?>]">
            <input type="text" class="lat" name="<?php echo esc_attr($name); ?>[lat]" value="<?php echo esc_attr($value['lat']); ?>" placeholder="緯度">
            <input type="text" class="lng" name="<?php echo esc_attr($name); ?>[lng]" value="<?php echo esc_attr($value['lng']); ?>" placeholder="経度">
            <input type="text" class="zoom" name="<?php echo esc_attr($name); ?>[zoom]" value="<?php echo esc_attr($value['zoom']); ?>" placeholder="拡大率">
            <p>
                マーカーをドラッグで動かしてください。<br>
                マウスホイールもしくは右下の「<span class="dashicons dashicons-plus"></span>」「<span class="dashicons dashicons-minus"></span>」で拡大縮小します。<br>
                実際の表示ではマーカーの位置が中心となります。
            </p>
            <p><label><input class="visible" type="checkbox" checked="checked">マップ非表示</label></p>
            <div class="mapCanvas"></div>
        </div>
        <?php
    }

    protected function createField_wysiwyg($name, $value, $setting)
    {
        wp_editor($value, $name, array(
            'textarea_name' => $name
        ));
    }

    public function setData($name, $arrValue, $setting)
    {
        $function = 'setData_' . $setting['type'];
        if (method_exists($this, $function)) {
            $arrValue[$name] = $this->{$function}($name, $arrValue, $setting);
        } else {
            // なければ通常処理

            if ($setting['multiply']) {
                // 複数値
                if (!$this->exists($name, $arrValue)) {
                    // 値がなければ空配列とする
                    $arrValue[$name] = array();
                }
            } else {
                // 単一値
                if (!$this->exists($name, $arrValue)) {
                    // 値がなければ空文字とする
                    $arrValue[$name] = '';
                } else {
                    $arrValue[$name] = $arrValue[$name][0];
                }
            }
        }

        return $arrValue[$name];
    }

    public function setData_map($name, $arrValue, $setting)
    {
        $arrValue[$name] = array();
        foreach (array('lat', 'lng', 'zoom') as $key) {
            if ($setting['multiply']) {
                // 複数値
                if (!$this->exists($name, $arrValue)) {
                    // 値がなければ空配列とする
                    $arrValue[$name][$key] = array();
                } else {
                    $arrValue[$name][$key] = $arrValue[$name . '-' . $key];
                }
            } else {
                // 単一値
                if (!$this->exists($name . '-' . $key, $arrValue)) {
                    // 値がなければ空文字とする
                    $arrValue[$name][$key] = '';
                } else {
                    $arrValue[$name][$key] = $arrValue[$name . '-' . $key][0];
                }
            }
            unset($arrValue[$name . '-' . $key]);
        }

        return $arrValue[$name];
    }

    public function sevaPost($post_id, $name, $value, $setting)
    {
        $function = 'sevaPost_' . $setting['type'];
        if (method_exists($this, $function)) {
            $this->{$function}($post_id, $name, $value, $setting);
        } else {
            // なければ通常処理

            $isSanitize = true;
            if ($this->exists('html', $setting, false, false, false)) {
                // HTML可ならサニタイズしない
                $isSanitize = false;
            }

            if ($setting['multiply']) {
                // 複数値なら一度すべて削除してから追加する
                delete_post_meta($post_id, $name);
                foreach ($value as $val) {
                    if ($isSanitize) {
                        $val = sanitize_text_field($val);
                    }
                    if (strlen($val)
                        || $this->exists('save-empty', $setting, false, false, false)
                    ) {
                        // 値があるか、値がなくても保存する設定なら保存する
                        add_post_meta($post_id, $name, $val);
                    }
                }
            } else {
                // 単一要素なら更新する
                if ($isSanitize) {
                    $value = sanitize_text_field($value);
                }
                if (strlen($value)
                    || $this->exists('save-empty', $setting, false, false, false)
                ) {
                    // 値があるか、値がなくても保存する設定なら保存する
                    update_post_meta($post_id, $name, $value);
                } else {
                    // 空なら削除する
                    delete_post_meta($post_id, $name);
                }
            }
        }
    }

    public function sevaPost_map($post_id, $name, $arrValue, $setting)
    {
        // $arrValue[map][lat], $arrValue[map][lng], $arrValue[map][zoom]の形で来る
        foreach ($arrValue as $key => $value) {
            if ($setting['multiply']) {
                // 複数値なら一度すべて削除してから追加する
                delete_post_meta($post_id, $name . '-' . $key);
                foreach ($value as $val) {
                    $val = sanitize_text_field($val);
                    if (strlen($val)
                        || $this->exists('save-empty', $setting, false, false, false)
                    ) {
                        // 値があるか、値がなくても保存する設定なら保存する
                        add_post_meta($post_id, $name . '-' . $key, $val);
                    }
                }
            } else {
                // 単一要素なら更新する
                $value = sanitize_text_field($value);
                if (strlen($value)
                    || $this->exists('save-empty', $setting, false, false, false)
                ) {
                    // 値があるか、値がなくても保存する設定なら保存する
                    update_post_meta($post_id, $name . '-' . $key, $value);
                } else {
                    // 空なら削除する
                    delete_post_meta($post_id, $name . '-' . $key);
                }
            }
        }
    }

}
