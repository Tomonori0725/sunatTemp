<?php

class ThemeWidgetCustomPostsArchive extends WP_Widget
{

    public function __construct()
    {
        parent::__construct(
            'custom_posts_archive',
            'アーカイブ(カスタム投稿タイプ対応)',
            array(
                'classname'   => 'widget_custom_posts_archive archiveBox',
                'description' => '投稿タイプを指定して投稿の月別アーカイブを一覧表示します。「現在の投稿タイプ」も指定できます。'
            )
        );
        $this->alt_option_name = 'widget_custom_posts_archve';

        add_action('save_post', array($this, 'flush_widget_cache'));
        add_action('deleted_post', array($this, 'flush_widget_cache'));
        add_action('switch_theme', array($this, 'flush_widget_cache'));
    }

    public function widget($args, $instance)
    {
        $cache = array();

        if (!$this->is_preview()) {
            $cache = wp_cache_get('widget_custom_posts_archve', 'widget');
        }
        if (!is_array($cache)) {
            $cache = array();
        }
        if (!isset($args['widget_id'])) {
            $args['widget_id'] = $this->id;
        }
        if (isset($cache[$args['widget_id']])) {
            echo $cache[$args['widget_id']];
            return;
        }

        ob_start();
        $title = !empty($instance['title']) ? $instance['title'] : '';
        $posttype = $instance['posttype'];
        $defaulttype = $instance['defaulttype'];
        $show_count = isset($instance['show_count']) ? $instance['show_count'] : false;

        if (!$posttype) {
            $posttype = getPostType();
            if ('attachment' === $posttype || 'page' === $posttype) {
                $posttype = '';
            }
        }
        if (!$posttype) {
            $posttype = $defaulttype;
        }

        if ($posttype) {
            $post_types = get_post_types(array(
                'public' => true
            ), 'objects');
            if (array_key_exists($posttype, (array)$post_types)) {
                // $type = 'monthly';
                $type = 'yearly';
                $list = wp_get_archives(array(
                    'type'            => $type,
                    'post_type'       => $posttype,
                    'show_post_count' => $show_count,
                    'echo'            => false
                ));

                if ($list) {
                    echo $args['before_widget'];
                    if ($title) {
                        echo $args['before_title'] . $title . $args['after_title'];
                    }
                    ?>

                    <ul class="slide_box archiveBox">
                        <?php
                        $list = array_filter(explode("\n", $list), 'strlen');
                        foreach ($list as $html) {
                            if ('yearly' === $type) {
                                if (preg_match('/(\d{4})<\/a>/u', $html, $m)) {
                                    $year = (int)$m[1];
                                    $replace = $year . '年';
                                }
                            } elseif ('monthly' === $type) {
                                if (preg_match('/(\d{4})年(\d+)月/u', $html, $m)) {
                                    $year = (int)$m[1];
                                    $month = (int)$m[2];
                                    $replace = date('Y.m', mktime(0, 0, 0, $month, 1, $year));
                                }
                            }
                            echo str_replace($m[0], $replace, $html);
                        }
                        ?>
                    </ul>

                    <?php
                    echo $args['after_widget'];
                }
            }
        }
    }

    public function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['posttype'] = esc_attr($new_instance['posttype']);
        $instance['defaulttype'] = esc_attr($new_instance['defaulttype']);
        $instance['show_count'] = isset($new_instance['show_count']) ? (bool)$new_instance['show_count'] : false;
        $this->flush_widget_cache();

        $alloptions = wp_cache_get('alloptions', 'options');
        if (isset($alloptions['widget_custom_posts_archve'])) {
            delete_option('widget_custom_posts_archve');
        }

        return $instance;
    }

    public function form($instance)
    {
        $title = isset($instance['title']) ? esc_attr($instance['title']) : '';
        $posttype = isset($instance['posttype']) ? esc_attr($instance['posttype']) : '';
        $defaulttype = isset($instance['defaulttype']) ? esc_attr($instance['defaulttype']) : '';
        $show_count = isset($instance['show_count']) ? (bool) $instance['show_count'] : false;

        $post_types = get_post_types(array('public' => true), 'objects');
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">タイトル：</label>
            <input type="text" id="<?php echo $this->get_field_id('title'); ?>" class="widefat" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $title; ?>">
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('posttype'); ?>">表示する投稿タイプ：</label>
            <select id="<?php echo $this->get_field_id('posttype'); ?>" class="widefat" name="<?php echo $this->get_field_name('posttype'); ?>">
                <option value="">現在の投稿タイプ</option>
                <?php foreach ($post_types as $post_type => $value) : ?>
                    <?php if ('attachment' === $post_type || 'page' === $post_type) : ?>
                        <?php continue; ?>
                    <?php endif; ?>
                    <option value="<?php echo esc_attr($post_type); ?>"<?php selected($post_type, $posttype); ?>><?php echo esc_html($value->label); ?></option>
                <?php endforeach; ?>
            </select>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('defaulttype'); ?>">「現在の投稿タイプ」の時に投稿でないページで表示する投稿タイプ：</label>
            <select id="<?php echo $this->get_field_id('defaulttype'); ?>" class="widefat" name="<?php echo $this->get_field_name('defaulttype'); ?>">
                <option value="">非表示</option>
                <?php foreach ($post_types as $post_type => $value) : ?>
                    <?php if ('attachment' === $post_type || 'page' === $post_type) : ?>
                        <?php continue; ?>
                    <?php endif; ?>
                    <option value="<?php echo esc_attr($post_type); ?>"<?php selected($post_type, $defaulttype); ?>><?php echo esc_html($value->label); ?></option>
                <?php endforeach; ?>
            </select>
        </p>

        <p>
            <input type="checkbox" id="<?php echo $this->get_field_id('show_count'); ?>" class="checkbox" name="<?php echo $this->get_field_name('show_count'); ?>"<?php checked($show_count); ?>>
            <label for="<?php echo $this->get_field_id('show_count'); ?>">投稿数を表示しますか？</label>
        </p>
        <?php
    }

    public function flush_widget_cache()
    {
        wp_cache_delete('widget_custom_posts_archve', 'widget');
    }

}
