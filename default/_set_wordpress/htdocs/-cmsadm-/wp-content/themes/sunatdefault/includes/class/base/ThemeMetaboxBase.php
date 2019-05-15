<?php

abstract class ThemeMetaboxBase extends ThemeClassBase
{

    const METABOX_PATH = 'includes/metabox/';
    protected $config = array();
    protected $fields = null;
    protected function initialize()
    {
        $config = $this->getConfig();
        if (!$this->exists('posttype', $config)
            || !is_array($config['posttype'])
        ) {
            return;
        }
        $this->config = $config['posttype'];

        $isExistsMetabox = false;
        foreach ($this->config as $key => $value) {
            if ($this->exists('metabox', $value)) {
                // カスタムフィールドあり
                add_action('add_meta_boxes_' . $key, array($this, 'addMetaboxes'));
                $isExistsMetabox = true;
            }
        }

        if ($isExistsMetabox) {
            $this->fields = ThemeFields::getInstance();
            $this->config = $this->fields->setDefaultConfig($this->config);
            add_action('save_post', array($this, 'savePost'), 10, 3);
            add_filter('the_posts', array($this, 'thePosts'), 10, 2);
        }
    }

    public function addMetaboxes($post)
    {
        $arrMeta = array();
        $this->getMetaData($post->ID, $arrMeta);
        foreach ($this->config[$post->post_type]['metabox'] as $key => $metabox) {
            $no = $key + 1;
            $id = $post->post_type . 'Section' . $no;
            $title = $metabox['title'];
            if (0 === strlen($title)) {
                $title = $id . 'ボックス';
                add_filter('postbox_classes_' . $post->post_type . '_' . $id, function ($classes) {
                    array_push($classes, 'no_box');
                    return $classes;
                });
            }
            add_meta_box(
                $id,
                $title,
                array($this, 'createMetaSection'),
                $post->post_type,
                'normal',
                'high',
                array_merge($arrMeta, array('sectionNo' => $no))
            );
        }
    }

    public function createMetaSection($post, $arrMeta)
    {
        $arrMeta = $arrMeta['args'];
        $config = $this->config[$post->post_type]['metabox'][$arrMeta['sectionNo'] - 1];

        if (1 === $arrMeta['sectionNo']) {
            echo '<input type="hidden" name="custom_nonce" value="' . wp_create_nonce(getPath('wp-style')) . '" />';
        }
        if ($this->exists('template', $config)) {
            include getPath('wp-style') . self::METABOX_PATH . $config['template'] . '.php';
            return;
        }

        foreach ($config['item'] as $name => $setting) {
            $this->fields->createField($name, $arrMeta[$name]['value'], $setting);
        }
    }

    public function getMetaData($post_id, array &$arrMeta, $isDefault = true)
    {
        $isNew = false;
        $posttype = get_post_type($post_id);
        $arrCustom = get_post_custom($post_id);

        // カスタムフィールドが空なら新規追加である
        // 新規でなければ値が空でも管理用データが入っている
        if (empty($arrCustom)) {
            $isNew = true;
        }

        if (!$this->exists($posttype, $this->config)
            || !$this->exists('metabox', $this->config[$posttype])
        ) {
            return;
        }

        $metaboxes = $this->config[$posttype]['metabox'];
        foreach ($metaboxes as $metabox) {
            foreach ($metabox['item'] as $name => $setting) {
                if ($isNew && $isDefault) {
                    // 新規追加時で初期値を使用する時は初期値を入れる
                    $arrCustom[$name] = $setting['default'];
                } else {
                    $arrCustom[$name] = $this->fields->setData($name, $arrCustom, $setting);
                }
                $arrMeta[$name] = array(
                    'name'    => $name,
                    'display' => $setting['title'],
                    'value'   => $arrCustom[$name]
                );
            }
        }
    }

    public function savePost($post_id, $post, $update)
    {
        if (!$this->checkSavePost($post_id, $post, $update)
            || !$this->exists($post->post_type, $this->config)
            || !$this->exists('metabox', $this->config[$post->post_type])
        ) {
            return;
        }

        // カスタムフィールドを保存する
        $metaboxes = $this->config[$post->post_type]['metabox'];
        foreach ($metaboxes as $metabox) {
            foreach ($metabox['item'] as $name => $setting) {
                if (!$this->exists($name, $_POST)) {
                    // POSTされてなければDBから削除する
                    delete_post_meta($post_id, $name);
                    continue;
                }

                $this->fields->sevaPost($post_id, $name, $_POST[$name], $setting);
            }
        }
    }

    protected function checkSavePost($post_id, $post, $update)
    {
        // nonce
        $custom_nonce = null;
        if ($this->exists('custom_nonce', $_POST)) {
            $custom_nonce = $_POST['custom_nonce'];
        }
        if (is_null($custom_nonce)) {
            return false;
        }
        if (!wp_verify_nonce($custom_nonce, getPath('wp-style'))) {
            return false;
        }

        // 権限
        $edit = 'edit_post';
        if ('page' === $post->post_type) {
            $edit = 'edit_page';
        }
        if (!current_user_can($edit, $post_id)) {
            return false;
        }

        // 自動保存
        if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            || 'auto-draft' === $post->post_status
        ) {
            return false;
        }

        return true;
    }

    // カスタムフィールドの値を投稿データに含める
    public function thePosts($posts, $wp_query)
    {
        foreach ($posts as $key => $post) {
            $posttype = get_post_type($post->ID);
            if (!$this->exists($posttype, $this->config)
                || !$this->exists('metabox', $this->config[$posttype])
            ) {
                continue;
            }

            $arrMeta = array();
            $this->getMetaData($post->ID, $arrMeta);
            $posts[$key]->metaFields = $arrMeta;
        }

        return $posts;
    }

}
