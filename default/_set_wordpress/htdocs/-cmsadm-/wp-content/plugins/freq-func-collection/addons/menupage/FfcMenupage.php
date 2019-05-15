<?php

if (!class_exists('FfcMenupage')) :

/**
 * メニューページクラス.
 * メニューページの追加を行う。
 */
class FfcMenupage extends FfcBaseClass
{

    const CONFIG_NAME = 'menupage';
    const DIRECTORY_NAME = 'menupage';
    protected $config = array();
    protected $isInitializedConfig = array();
    protected $cached = false;
    protected $defaults = array(
        'title'    => '',
        'parent'   => array(
            'select' => '',
            'other'  => ''
        ),
        'template' => '',
        'cap'      => array(
            'select' => 'manage_options',
            'other'  => ''
        ),
        'menu'     => '',
        'icon'     => '',
        'position' => 76,
        'fields'   => array()
    );

    /**
     * 初期化する.
     *
     * @access protected
     * @return void
     */
    protected function initialize()
    {
        // configのメニューページ設定を読み込む
        $this->config = $this->main->getConfig(self::DIRECTORY_NAME);
        // 設定がなければ何もしない
        if (empty($this->config)) {
            return;
        }

        $isExistsFields = false;
        foreach ($this->config as $slug => $value) {
            $value = array_intersect_key(wp_parse_args($value, $this->defaults), $this->defaults);
            if (!$this->exists('title', $value, false, false, false)) {
                $value['title'] = $slug;
            }
            $this->config[$slug] = $value;
            if ($this->exists('fields', $value, false, false, false)) {
                $isExistsFields = true;
            }
        }

        add_action('admin_menu', array($this, 'addMenuPage'));
        if ($isExistsFields) {
            add_filter('pre_update_option', array($this, 'preUpdateOption'), 10, 3);
            add_filter('alloptions', array($this, 'alloptions'), 10);
        }
    }

    protected function initialConfig($slug)
    {
        if ($this->exists($slug, $this->isInitializedConfig, false, false, false)) {
            return;
        }
        if ($this->exists('fields', $this->config[$slug], false, false, false)) {
            $this->config[$slug]['fields'] = $this->main->field->setDefaultConfig($this->config[$slug]['fields']);
        }
        $this->register($slug);
        $this->isInitializedConfig[$slug] = true;
    }

    /**
     * メニューページを追加する.
     *
     * @access public
     * @return void
     */
    public function addMenuPage()
    {
        $self = $this;
        foreach ($this->config as $slug => $value) {
            extract($value);

            if (strlen($parent['select'])) {
                $parent = $parent['select'];
            } else {
                $parent = $parent['other'];
            }
            if (strlen($cap['select'])) {
                $cap = $cap['select'];
            } else {
                $cap = $cap['other'];
            }

            // 設定にparentがなければメニュー自体を追加する
            if (!strlen($parent)) {
                if (strlen($menu)) {
                    add_menu_page($menu, $menu, $cap, $slug, '', $icon, $position);
                    $parent = $slug;
                } else {
                    // 設定にmenuがなければ「設定」の中に入れる
                    $parent = 'options-general.php';
                }
            }
            // 設定ページを追加する
            $settingsPage = add_submenu_page($parent, $title, $title, $cap, $slug, function () use ($self, $slug, $template) {
                $self->page($slug, $template);
            });
            add_action('load-' . $settingsPage, function () use ($self, $slug) {
                // ページが読み込まれるされる時に初期化する
                $self->initialConfig($slug);
            });
            // optionsを保存できる権限を変更する
            add_filter('option_page_capability_' . $slug . '-section-group', function ($capability) use ($self, $slug, $cap) {
                // 保存される時に初期化する
                $self->initialConfig($slug);
                // 必要権限を変更する
                $capability = $cap;
                return $capability;
            });
        }
    }

    public function register($slug)
    {
        $self = $this;
        extract($this->config[$slug]);

        add_settings_section(
            $slug . '-section',
            $title,
            function () {
                // TODO:説明文などを読み込めるようにする
            },
            $slug
        );
        foreach ($fields as $setting) {
            if ($this->exists('title-hide', $setting) && $setting['title-hide']) {
                $setting['title'] = '';
            }
            add_settings_field(
                $setting['name'],
                $setting['title'],
                function () use ($self, $slug, $setting) {
                    $value = $setting['instance']->getOption();
                    $setting['instance']->createField($value);
                    // $value = $self->config[$slug]['fields'][$setting['key']]['instance']->getOption();
                    // $self->config[$slug]['fields'][$setting['key']]['instance']->createField($value);
                },
                $slug,
                $slug . '-section'
            );
            register_setting(
                $slug . '-section-group',
                $setting['name']
            );
        }
    }

    public function page($slug, $template)
    {
        // TODO:$template設定で自由な表示に対応する
        include FFCOLLECTION_PLUGIN_DIR_PATH . FFCOLLECTION_ADDONS_DIR_NAME . '/' . self::DIRECTORY_NAME . '/view/template.php';
    }

    public function preUpdateOption($value, $option, $old_value)
    {
        foreach ($this->isInitializedConfig as $slug => $isInitialized) {
            if (false === $isInitialized) {
                continue;
            }
            // 初期化済みのページ == 保存しようとしているページ

            foreach ($this->config[$slug]['fields'] as $setting) {
                if ($setting['name'] !== $option) {
                    continue;
                }

                // 名前が同じものを保存する
                $value = $setting['instance']->preUpdateOption($value, $old_value);
                break;
            }
            break;
        }
        return $value;
    }

    public function alloptions($alloptions)
    {
        if ($this->cached) {
            return $alloptions;
        }

        foreach ($this->config as $slug => $page) {
            if (array_key_exists('fields', $page)) {
                foreach ($page['fields'] as $setting) {
                    if ('map' === $setting['type']) {
                        $value = wp_cache_get($setting['key'], 'options');
                        if (false === $value) {
                            $lat = '';
                            if ($this->exists($setting['key'] . '-lat', $alloptions, false, false)) {
                                $lat = $alloptions[$setting['key'] . '-lat'];
                            }
                            $lng = '';
                            if ($this->exists($setting['key'] . '-lng', $alloptions, false, false)) {
                                $lng = $alloptions[$setting['key'] . '-lng'];
                            }
                            $zoom = '';
                            if ($this->exists($setting['key'] . '-zoom', $alloptions, false, false)) {
                                $zoom = $alloptions[$setting['key'] . '-zoom'];
                            }
                            if ($lat && $lng && $zoom) {
                                $value = array(
                                    'lat'  => $lat,
                                    'lng'  => $lng,
                                    'zoom' => $zoom
                                );
                                wp_cache_add($setting['key'], $value, 'options');
                            }
                        }
                    }
                }
            }
        }
        $this->cached = true;

        return $alloptions;
    }

    /**
     * メニューページカスタムフィールドの選択式フィールドの選択肢を取得する.
     *
     * @param integer $page_slug メニューページのスラッグ
     * @param string  $name      フィールドのname
     *
     * @access public
     * @return array 選択肢配列
     */
    public function getMenupageOptions($page_slug, $name)
    {
        $options = array();
        if ($this->exists($page_slug, $this->config)
            && $this->exists('fields', $this->config[$page_slug])
            && is_array($this->config[$page_slug]['fields'])
        ) {
            foreach ($this->config[$page_slug]['fields'] as $field) {
                if ($name !== $field['key']
                    || !$this->exists('options', $field)
                ) {
                    continue;
                }

                $options = $field['options'];
                break;
            }
        }

        return $options;
    }

}

endif;
