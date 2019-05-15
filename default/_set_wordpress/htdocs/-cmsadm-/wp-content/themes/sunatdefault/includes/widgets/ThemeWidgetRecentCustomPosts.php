<?php

class ThemeWidgetRecentCustomPosts extends WP_Widget
{

    public function __construct()
    {
        parent::__construct(
            'recent_custom_posts',
            '最近の投稿(カスタム投稿タイプ対応)',
            array(
                'classname'   => 'widget_recent_custom_posts',
                'description' => '投稿タイプを指定して直近の投稿を一覧表示します。「現在の投稿タイプ」も指定できます。'
            )
        );
        $this->alt_option_name = 'widget_recent_custom_posts';

        add_action('save_post', array($this, 'flush_widget_cache'));
        add_action('deleted_post', array($this, 'flush_widget_cache'));
        add_action('switch_theme', array($this, 'flush_widget_cache'));
    }

    public function widget($args, $instance)
    {
        $cache = array();

        if (!$this->is_preview()) {
            $cache = wp_cache_get('widget_recent_custom_posts', 'widget');
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
        if (empty($instance['number']) || !$number = absint($instance['number'])) {
            $number = 5;
        }
        $show_date = isset($instance['show_date']) ? $instance['show_date'] : false;

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
                $theQuery = new WP_Query(array(
                    'post_type'           => $posttype,
                    'posts_per_page'      => $number,
                    'no_found_rows'       => true,
                    'post_status'         => 'publish',
                    'ignore_sticky_posts' => true
                ));

                if ($theQuery->have_posts()) :
                    echo $args['before_widget'];
                    if ($title) {
                        echo $args['before_title'] . $title . $args['after_title'];
                    }
                    ?>

                    <ul class="slide_box archiveBox">
                    <?php while ($theQuery->have_posts()) : $theQuery->the_post(); ?>
                        <li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a><?php if ($show_date) : ?><span class="date">[&nbsp;<?php the_time('Y-m-d'); ?>&nbsp;]</span><?php endif; ?></li>
                    <?php endwhile; wp_reset_postdata(); ?>
                    </ul>

                    <?php
                    echo $args['after_widget'];
                endif;
            }
        }

        if (!$this->is_preview()) {
            $cache[$args['widget_id']] = ob_get_flush();
            wp_cache_set('widget_recent_custom_posts', $cache, 'widget');
        } else {
            ob_end_flush();
        }
    }

    public function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['posttype'] = esc_attr($new_instance['posttype']);
        $instance['defaulttype'] = esc_attr($new_instance['defaulttype']);
        $instance['number'] = (int)$new_instance['number'];
        $instance['show_date'] = isset($new_instance['show_date']) ? (bool)$new_instance['show_date'] : false;
        $this->flush_widget_cache();

        $alloptions = wp_cache_get('alloptions', 'options');
        if (isset($alloptions['widget_recent_custom_posts'])) {
            delete_option('widget_recent_custom_posts');
        }

        return $instance;
    }

    public function form($instance)
    {
        $title = isset($instance['title']) ? esc_attr($instance['title']) : '';
        $posttype = isset($instance['posttype']) ? esc_attr($instance['posttype']) : '';
        $defaulttype = isset($instance['defaulttype']) ? esc_attr($instance['defaulttype']) : '';
        $number = isset($instance['number']) ? absint($instance['number']) : 5;
        $show_date = isset($instance['show_date']) ? (bool) $instance['show_date'] : false;

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
            <label for="<?php echo $this->get_field_id('number'); ?>">表示する投稿数：</label>
            <input type="text" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" value="<?php echo $number; ?>" size="3">
        </p>

        <p>
            <input type="checkbox" id="<?php echo $this->get_field_id('show_date'); ?>" class="checkbox" name="<?php echo $this->get_field_name('show_date'); ?>"<?php checked($show_date); ?>>
            <label for="<?php echo $this->get_field_id('show_date'); ?>">投稿日を表示しますか？</label>
        </p>
        <?php
    }

    public function flush_widget_cache()
    {
        wp_cache_delete('widget_recent_custom_posts', 'widget');
    }

}
