<?php

abstract class ThemeWidgetsBase extends ThemeClassBase
{

    const WIDGETS_PATH = 'includes/widgets/';
    protected $config = array();

    protected function initialize()
    {
        $config = $this->getConfig();
        if (!$this->exists('widgets', $config)
            || !is_array($config['widgets'])
        ) {
            return;
        }
        $this->config = $config['widgets'];

        if (array_key_exists('register', $this->config)) {
            $this->includeWidget();
        }
        if (!empty($this->config)) {
            add_action('widgets_init', array($this, 'widgetsInit'));
        }
    }

    // ウィジェットのクラスを読み込む
    protected function includeWidget()
    {
        foreach ($this->config['register'] as $widget) {
            include_once getPath('wp-style') . self::WIDGETS_PATH . $widget . '.php';
        }
    }

    // ウィジェットを登録する
    protected function registerWidget()
    {
        if (array_key_exists('unregister', $this->config)) {
            foreach ($this->config['unregister'] as $widget) {
                unregister_widget($widget);
            }
        }
        if (array_key_exists('register', $this->config)) {
            foreach ($this->config['register'] as $widget) {
                register_widget($widget);
            }
        }
    }

    /* コールバックメソッド */

    public function widgetsInit()
    {
        // ウィジェットエリア登録
        if (array_key_exists('area', $this->config)) {
            foreach ($this->config['area'] as $key => $area) {
                $key++;
                register_sidebar(array_merge(array(
                    'name'          => 'サイドバー' . $key,
                    'id'            => 'sidebar' . $key,
                    'description'   => $key . 'つ目のサイドバーエリアです。',
                    'before_widget' => '<div class="widget %2$s">',
                    'after_widget'  => '</div>',
                    'before_title'  => '<h2>',
                    'after_title'   => '</h2>',
                ), $area));
            }
        }
        // ウィジェット登録
        $this->registerWidget();
    }

}
