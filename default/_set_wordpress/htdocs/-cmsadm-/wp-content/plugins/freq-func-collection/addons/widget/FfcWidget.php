<?php

if (!class_exists('FfcWidget')) :

/**
 * ウィジェットクラス.
 * ウィジェットの登録・削除を行う。
 */
class FfcWidget extends FfcBaseClass
{

    const CONFIG_NAME = 'widget';
    const DIRECTORY_NAME = 'widget';
    protected $config = array();

    /**
     * 初期化する.
     *
     * @access protected
     * @return void
     */
    protected function initialize()
    {
        // configの定数設定を読み込む
        $this->config = $this->main->getConfig(self::CONFIG_NAME);

        // 設定がなければ何もしない
        if (empty($this->config)) {
            return;
        }

        if ($this->exists('register', $this->config)
            && is_array($this->config['register'])
        ) {
            $this->requireWidget();
        }
        add_action('widgets_init', array($this, 'widgetsInit'), 99);
    }

    /**
     * ウィジェットのクラスを読み込む.
     *
     * @access protected
     * @return void
     */
    protected function requireWidget()
    {
        foreach ($this->config['register'] as $widget) {
            $file = FFCOLLECTION_PLUGIN_DIR_PATH . FFCOLLECTION_ADDONS_DIR_NAME . '/' . self::DIRECTORY_NAME . '/class/' . $widget . '.php';
            if (!file_exists($file)) {
                continue;
            }
            require $file;
        }
    }

    /**
     * ウィジェットエリア・ウィジェットを登録する.
     *
     * @access public
     * @return void
     */
    public function widgetsInit()
    {
        // ウィジェットエリア登録
        if ($this->exists('area', $this->config)
            && is_array($this->config['area'])
        ) {
            foreach ($this->config['area'] as $key => $area) {
                $key++;
                register_sidebar(array_merge(array(
                    'name'          => __('Widgets area', FFCOLLECTION_PLUGIN_DIR_NAME) . $key,
                    'id'            => 'widgets_area' . $key,
                    'description'   => __('It is a widget area.', FFCOLLECTION_PLUGIN_DIR_NAME),
                    'before_widget' => '<div class="widget %2$s">',
                    'after_widget'  => '</div>',
                    'before_title'  => '<h2 class="widgetTitle">',
                    'after_title'   => '</h2>',
                ), $area));
            }
        }
        // ウィジェット登録
        $this->registerWidget();
    }

    /**
     * ウィジェットを登録・削除する.
     *
     * @access protected
     * @return void
     */
    protected function registerWidget()
    {
        if ($this->exists('unregister', $this->config)
            && is_array($this->config['unregister'])
        ) {
            foreach ($this->config['unregister'] as $widget) {
                unregister_widget($widget);
            }
        }
        if ($this->exists('register', $this->config)
            && is_array($this->config['register'])
        ) {
            foreach ($this->config['register'] as $widget) {
                register_widget($widget);
            }
        }
    }

}

endif;
