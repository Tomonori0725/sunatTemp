<?php

class ThemeWidgetBanner extends WP_Widget
{

    public function __construct()
    {
        parent::__construct(
            'theme_widget_banner',
            'バナー(画像登録付き)',
            array(
                'classname'   => 'widget_banner linkBox',
                'description' => 'バナーの登録ができます。ここから画像の登録もできます。リンクあり/なし、テキストあり/なしも選択できます。'
            )
        );
        $this->alt_option_name = 'widget_banner';
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
        $instance = $old_instance;
        $instance['caption'] = strip_tags($new_instance['caption']);
        $instance['alt'] = esc_attr($new_instance['alt']);
        $instance['target'] = isset($new_instance['target']) ? (bool)$new_instance['target'] : false;
        $instance['image_id'] = isset($instance['image_id']) ? $new_instance['image_id'] : '';
        $instance['position'] = abs($new_instance['position']);
        $instance['link'] = $new_instance['link'];

        return $instance;
    }

    public function form($instance)
    {
        $image_id = isset($instance['image_id']) ? $instance['image_id'] : '';
        $link = isset($instance['link']) ? esc_url($instance['link']) : '';
        $alt = isset($instance['alt']) ? esc_attr($instance['alt']) : '';
        $caption = isset($instance['caption']) ? esc_attr($instance['caption']) : '';
        $target = isset($instance['target']) ? (bool)$instance['target'] : false;
        $position = isset($instance['position']) ? abs($instance['position']) : 1;

        uploadImageTemplate(array(
            'imageName' => $this->get_field_name('image_id'),
            'imageId'   => $image_id,
            'multi'     => false,
            'sortable'  => false,
            'size'      => 'full',
            'button'    => '画像を選択する'
        ));
        ?>

        <p>
            <label for="<?php echo $this->get_field_id('alt'); ?>">代替テキスト：</label>
            <input type="text" id="<?php echo $this->get_field_id('alt'); ?>" class="widefat" name="<?php echo $this->get_field_name('alt'); ?>" value="<?php echo $alt; ?>">
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('link'); ?>">リンク先URL：</label>
            <input type="text" id="<?php echo $this->get_field_id('link'); ?>" class="widefat" name="<?php echo $this->get_field_name('link'); ?>" value="<?php echo $link; ?>">
            リンクしない場合は空にしてください。
        </p>

        <p>
            <input type="checkbox" id="<?php echo $this->get_field_id('target'); ?>" class="checkbox" name="<?php echo $this->get_field_name('target'); ?>"<?php checked($target); ?>>
            <label for="<?php echo $this->get_field_id('target'); ?>">新規タブで開く</label>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('caption'); ?>">キャプション：</label>
            <input type="text" id="<?php echo $this->get_field_id('caption'); ?>" class="widefat" name="<?php echo $this->get_field_name('caption'); ?>" value="<?php echo $caption; ?>"><br>
            表示しない場合は空にしてください。
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('position'); ?>">キャプション表示位置：</label>
            <select id="<?php echo $this->get_field_id('position'); ?>" class="widefat" name="<?php echo $this->get_field_name('position'); ?>">
                <option value="1"<?php selected(1, $position); ?>>バナーの上</option>
                <option value="2"<?php selected(2, $position); ?>>バナーの下</option>
                <option value="3"<?php selected(3, $position); ?>>バナーホバー時</option>
            </select>
        </p>
        <?php
    }

}
