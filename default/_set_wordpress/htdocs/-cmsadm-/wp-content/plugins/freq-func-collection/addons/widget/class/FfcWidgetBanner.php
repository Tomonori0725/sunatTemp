<?php

if (!class_exists('FfcWidgetBanner')) :

class FfcWidgetBanner extends WP_Widget
{

    protected $fields = array(
        array(
            'key'      => 'image_id',
            'title'    => '画像',
            'type'     => 'image',
            'multi'    => false,
            'sortable' => false
        ),
        array(
            'key'   => 'alt',
            'title' => '代替テキスト',
            'type'  => 'text'
        ),
        array(
            'key'   => 'link',
            'title' => 'リンク先URL',
            'type'  => 'text'
        ),
        array(
            'key'     => 'target',
            'title'   => '',
            'type'    => 'checkbox',
            'options' => array(
                1 => '新規タブで開く'
            )
        ),
        array(
            'key'   => 'caption',
            'title' => 'キャプション',
            'type'  => 'text'
        ),
        array(
            'key'     => 'position',
            'title'   => 'キャプション表示位置',
            'type'    => 'select',
            'options' => array(
                1 => 'バナーの上',
                2 => 'バナーの下',
                3 => 'バナーホバー時'
            )
        )
    );

    public function __construct()
    {
        parent::__construct(
            'ffc_widget_banner',
            'バナー(画像登録付き)',
            array(
                'classname'   => 'widget_banner',
                'description' => 'バナーの登録ができます。ここから画像の登録もできます。リンクあり/なし、テキストあり/なしも可能です。'
            )
        );
    }

    public function widget($args, $instance)
    {
        extract($instance);

        if ($image_id) {
            $srcList = wp_get_attachment_image_src($image_id[0], 'full');
            if (false === $srcList) {
                return;
            }

            echo $args['before_widget'];
            if (1 === $position) {
                echo $args['before_title'] . $caption . $args['after_title'];
            }

            if (strlen($link)) {
                echo '<a href="' . esc_url($link) . '"';
                if ($target) {
                    echo ' target="_blank"';
                }
                echo '>';
            }

            echo '<img src="' . $srcList[0] . '"';
            echo ' alt="' . $alt . '"';
            if (3 === $position) {
                echo ' title="' . $caption . '"';
            }
            echo '>';

            if (strlen($link)) {
                echo '</a>';
            }

            if (2 === $position) {
                echo $args['before_title'] . $caption . $args['after_title'];
            }
            echo $args['after_widget'];
        }
    }

    public function update($new_instance, $old_instance)
    {
        $new_instance = $this->setDefault($new_instance);
        $new_instance = $this->normalize($new_instance);

        $instance = array_merge($old_instance, $new_instance);

        return $instance;
    }

    protected function setDefault($data)
    {
        $defaults = array(
            'image_id' => array(),
            'alt'      => '',
            'link'     => '',
            'target'   => false,
            'caption'  => '',
            'position' => 1
        );
        $data = wp_parse_args($data, $defaults);

        return $data;
    }

    protected function normalize($data)
    {
        $data['image_id'] = $data['image_id'];
        $data['alt'] = $data['alt'];
        $data['link'] = $data['link'];
        $data['target'] = $data['target'];
        $data['caption'] = wp_kses_post($data['caption']);
        $data['position'] = abs($data['position']);

        return $data;
    }

    protected function escape($data)
    {
        $data['image_id'] = $data['image_id'];
        $data['alt'] = esc_attr($data['alt']);
        $data['link'] = esc_url($data['link']);
        $data['target'] = esc_attr($data['target']);
        $data['caption'] = esc_attr($data['caption']);
        $data['position'] = abs($data['position']);

        return $data;
    }

    public function form($instance)
    {
        $instance = $this->setDefault($instance);
        $instance = $this->escape($instance);
        extract($instance);

        foreach ($this->fields as $no => $setting) {
            $this->fields[$no]['name'] = $this->get_field_name($setting['key']);
            $this->fields[$no]['id'] = $this->get_field_id($setting['key']);
        }

        global $ffCollection;
        $this->fields = $ffCollection->field->setDefaultConfig($this->fields);
        ?>

        <?php $this->fields['image_id']['instance']->createField($image_id); ?>

        <p>
            <label for="<?php echo $this->fields['alt']['id']; ?>"><?php echo esc_html($this->fields['alt']['title']); ?>：</label>
            <?php $this->fields['alt']['instance']->createField($alt); ?>
        </p>

        <p>
            <label for="<?php echo $this->fields['link']['id']; ?>"><?php echo esc_html($this->fields['link']['title']); ?>：</label>
            <?php $this->fields['link']['instance']->createField($link); ?>
            <small>リンクしない場合は空にしてください。</small>
        </p>

        <p>
            <?php $this->fields['target']['instance']->createField($target); ?>
        </p>

        <p>
            <label for="<?php echo $this->fields['caption']['id']; ?>"><?php echo esc_html($this->fields['caption']['title']); ?>：</label>
            <?php $this->fields['caption']['instance']->createField($caption); ?>
            <small>表示しない場合は空にしてください。</small>
        </p>

        <p>
            <label for="<?php echo $this->fields['position']['id']; ?>"><?php echo esc_html($this->fields['position']['title']); ?>：</label>
            <?php $this->fields['position']['instance']->createField($position); ?>
        </p>
        <?php
    }

}

endif;
