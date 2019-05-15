<?php

abstract class ThemeMenuPageBase extends ThemeClassBase
{

    const MENUPAGE_PATH = 'includes/menupage/';
    protected $config = array();

    protected function initialize()
    {
        $config = $this->getConfig();
        if (!$this->exists('menupage', $config)
            || !is_array($config['menupage'])
        ) {
            return;
        }
        $this->config = $config['menupage'];

        add_action('admin_menu', array($this, 'addMenuPage'));
    }

    // メニューページを追加する
    public function addMenuPage()
    {
        add_action('admin_init', array($this, 'register'));

        $self = $this;
        foreach ($this->config as $slug => $value) {
            extract($value);

            // 設定にmenuがあればメニュー自体を追加する
            if ($this->exists('menu', $value, false)) {
                add_menu_page($menu, $menu, $cap, $slug, '', $icon, $position);
                $parent = $slug;
            }
            // 設定ページを追加する
            add_submenu_page($parent, $title, $title, $cap, $slug, function () use ($self, $template) {
                $self->page($template);
            });

            // optionsを保存できる権限を変更する
            add_filter('option_page_capability_' . $slug . '-section-group', function ($capability) use ($cap) {
                $capability = $cap;
                return $capability;
            });
        }
    }

    public function register()
    {
        foreach ($this->config as $slug => $value) {
            extract($value);

            add_settings_section(
                $slug . '-section',
                $title,
                function () {},
                $slug
            );
            foreach ($item as $key => $value) {
                add_settings_field(
                    $key,
                    $value[0],
                    function () {},
                    $slug,
                    $slug . '-section'
                );
                register_setting(
                    $slug . '-section-group',
                    $key
                );
            }
        }
    }

    public function page($template)
    {
        include getPath('wp-style') . self::MENUPAGE_PATH . $template;
    }

}
