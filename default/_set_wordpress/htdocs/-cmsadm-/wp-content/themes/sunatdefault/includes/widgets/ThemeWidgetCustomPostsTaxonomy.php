<?php

class ThemeWidgetCustomPostsTaxonomy extends WP_Widget
{

    public function __construct()
    {
        parent::__construct(
            'custom_posts_taxonomy',
            'カテゴリー(カスタムタクソノミー対応)',
            array(
                'classname'   => 'widget_custom_posts_taxonomy',
                'description' => 'タクソノミーを指定してタームを一覧表示します。'
            )
        );
        $this->alt_option_name = 'widget_custom_posts_taxonomy';

        add_action('save_post', array($this, 'flush_widget_cache'));
        add_action('deleted_post', array($this, 'flush_widget_cache'));
        add_action('switch_theme', array($this, 'flush_widget_cache'));
    }

    public function widget($args, $instance)
    {
        $cache = array();

        if (!$this->is_preview()) {
            $cache = wp_cache_get('widget_custom_posts_taxonomy', 'widget');
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
        $show_count = isset($instance['show_count']) ? $instance['show_count'] : false;
        $hierarchical = isset($instance['hierarchical']) ? $instance['hierarchical'] : false;

        $posttype = getPostType();
        if ('attachment' === $posttype || 'page' === $posttype) {
            $posttype = '';
        }

        if ($posttype) {
            $post_types = get_post_types(array(
                'public' => true
            ), 'objects');
            if (array_key_exists($posttype, (array)$post_types)) {
                $taxonomies = get_object_taxonomies($posttype, 'objects');
                if ($taxonomies) {
                    $taxonomy = array_shift($taxonomies);
                    $terms = get_categories(array('taxonomy' => $taxonomy->name, 'hide_empty' => true));
                    if (!empty($terms)) {
                        echo $args['before_widget'];
                        if ($title) {
                            echo $args['before_title'] . $title . $args['after_title'];
                        }
                        ?>

                        <ul class="slide_box archiveBox">
                            <?php foreach ($terms as $term) : ?>
                                <li><a href="<?php echo esc_url(get_term_link($term->slug, $term->taxonomy)) ?>"><?php echo esc_html($term->name); ?>
                                <?php if ($show_count) : ?>
                                    &nbsp;<span class="count">(<?php echo esc_html($term->count); ?>)</span>
                                <?php endif; ?>
                                </a></li>
                            <?php endforeach; ?>
                        </ul>

                        <?php
                        echo $args['after_widget'];
                    }
                }
            }
        }
    }

    public function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['show_count'] = isset($new_instance['show_count']) ? (bool)$new_instance['show_count'] : false;
        $this->flush_widget_cache();

        $alloptions = wp_cache_get('alloptions', 'options');
        if (isset($alloptions['widget_custom_posts_taxonomy'])) {
            delete_option('widget_custom_posts_taxonomy');
        }

        return $instance;
    }

    public function form($instance)
    {
        $title = isset($instance['title']) ? esc_attr($instance['title']) : '';
        $show_count = isset($instance['show_count']) ? (bool) $instance['show_count'] : false;
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">タイトル：</label>
            <input type="text" id="<?php echo $this->get_field_id('title'); ?>" class="widefat" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $title; ?>">
        </p>

        <p>
            <input type="checkbox" id="<?php echo $this->get_field_id('show_count'); ?>" class="checkbox" name="<?php echo $this->get_field_name('show_count'); ?>"<?php checked($show_count); ?>>
            <label for="<?php echo $this->get_field_id('show_count'); ?>">投稿数を表示しますか？</label>
        </p>
        <?php
    }

    public function flush_widget_cache()
    {
        wp_cache_delete('widget_custom_posts_taxonomy', 'widget');
    }

}
