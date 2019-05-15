<?php

abstract class ThemeResourceBase extends ThemeClassBase
{

    const JS_PATH = 'assets/js';
    const CSS_PATH = 'assets/css';
    const LIB_PATH = 'assets/lib';
    protected $config = array();

    protected function initialize()
    {
        $config = $this->getConfig();
        if (!$this->exists('resource', $config)
            || !is_array($config['resource'])
        ) {
            return;
        }
        $this->config = $config['resource'];

        add_action('after_setup_theme', array($this, 'afterSetupTheme'));
        // 管理画面
        add_action('admin_enqueue_scripts', array($this, 'addAdminScript'));
        // フロント
        add_action('wp_enqueue_scripts', array($this, 'addFrontScript'));

        add_filter('image_size_names_choose', array($this, 'imageSizeNamesChoose'), 11, 1);
    }

    // テーマの基本設定をする
    public function afterSetupTheme()
    {
        add_theme_support('post-thumbnails');
        if ($this->exists('imageSize', $this->config)) {
            foreach ($this->config['imageSize'] as $key => $value) {
                add_image_size($key, $value[1], $value[2]);
            }
        }
    }

    // 追加したサイズを画像サイズ選択に追加する
    public function imageSizeNamesChoose($sizes)
    {
        $new_sizes = array();
        $added_sizes = get_intermediate_image_sizes();
        foreach($added_sizes as $value) {
            if ($this->exists('imageSize', $this->config)
                && $this->exists($value, $this->config['imageSize'])
            ) {
                $new_sizes[$value] = $this->config['imageSize'][$value][0];
            } else {
                $new_sizes[$value] = $value;
            }
        }
        $new_sizes = array_merge($sizes, $new_sizes);

        return $new_sizes;
    }

    // 管理画面にJSファイルを追加する
    public function addAdminScript()
    {
        $this->addAdminStyle();

        wp_enqueue_media();

        // 各種ライブラリ
        wp_enqueue_script('autosize-js', getUrl('wp-style') . '/' . self::LIB_PATH . '/Autosize/autosize.min.js', array('jquery'), '3.0.15', true);

        // Google Maps
        $params = '';
        $key = getConst('GOOGLE_MAPS_API_KEY');
        if ($key) {
            $params = '?key=' . $key;
        }
        wp_enqueue_script('gmaps-api-js', 'https://maps.googleapis.com/maps/api/js' . $params, array(), null, true);
        wp_enqueue_script('googlemap-js', getUrl('wp-style') . '/' . self::JS_PATH . '/sunat.googleMap.js', array('jquery', 'gmaps-api-js'), null, true);
        wp_enqueue_script('gmap-js', getUrl('wp-style') . '/' . self::JS_PATH . '/admin/googlemap.js', array('googlemap-js'), null, true);

        wp_enqueue_script('colorpicker-js', getUrl('wp-style') . '/' . self::JS_PATH . '/admin/colorpicker.js', array('iris'), null,true);
        wp_enqueue_script('datepicker-js', getUrl('wp-style') . '/' . self::JS_PATH . '/admin/datepicker.js', array('jquery-ui-datepicker'), null,true);
        wp_enqueue_script('spinner-js', getUrl('wp-style') . '/' . self::JS_PATH . '/admin/spinner.js', array('jquery-ui-spinner'), null,true);
        wp_enqueue_script('fileupload-js', getUrl('wp-style') . '/' . self::JS_PATH . '/admin/fileupload.js', array('jquery', 'jquery-ui-sortable'), null,true);

        // COMMON
        wp_enqueue_script('common-js', getUrl('wp-style') . '/' . self::JS_PATH . '/admin/common.js', array('jquery'), null, true);
    }

    // 管理画面にCSSファイルを追加する
    public function addAdminStyle()
    {
        wp_enqueue_style('jquery-ui-redmond-style', getUrl('wp-style') . '/' . self::LIB_PATH . '/jquery/core/jquery-ui-redmond.css', array(), '1.11.2');
        wp_enqueue_style('edit-post-style', getUrl('wp-style') . '/' . self::CSS_PATH . '/admin/edit-post.css');
        wp_enqueue_style('fileupload-style', getUrl('wp-style') . '/' . self::CSS_PATH . '/admin/fileupload.css');
        wp_enqueue_style('colorpicker-style', getUrl('wp-style') . '/' . self::CSS_PATH . '/admin/colorpicker.css');
        wp_enqueue_style('googlemap-style', getUrl('wp-style') . '/' . self::CSS_PATH . '/admin/googlemap.css');
    }

    // フロントにJSファイルを追加する
    public function addFrontScript()
    {
        $this->addFrontStyle();

        // jQueryを最新にする
        // jQueryのみhead内に記載する
        wp_deregister_script('jquery');
        wp_enqueue_script('jquery', getUrl('wp-style') . '/' . self::LIB_PATH . '/jquery/core/jquery-1.12.4.min.js', array(), '1.12.4');
        wp_enqueue_script('jquery-mig', getUrl('wp-style') . '/' . self::LIB_PATH . '/jquery/core/jquery-migrate-1.4.1.min.js', array('jquery'), '1.4.1');

        // 各種ライブラリ
        wp_enqueue_script('jquery-easing-js', getUrl('wp-style') . '/' . self::LIB_PATH . '/jquery/jquery.easing.1.3.min.js', array('jquery-mig'), '1.3', true);
        wp_enqueue_script('jquery-targetScroller-js', getUrl('wp-style') . '/' . self::LIB_PATH . '/jquery/jquery.targetScroller.js', array('jquery-easing-js'), '1.0', true);
        wp_enqueue_script('autosize-js', getUrl('wp-style') . '/' . self::LIB_PATH . '/Autosize/autosize.min.js', array('jquery-mig'), '3.0.15', true);
        wp_enqueue_script('jquery-matchHeight-js', getUrl('wp-style') . '/' . self::LIB_PATH . '/jquery/jquery.matchHeight.js', array('jquery-mig'), null, true);
        wp_enqueue_script('jquery-browser-js', getUrl('wp-style') . '/' . self::LIB_PATH . '/jquery/jquery.browser.js', array('jquery-mig'), null, true);
        wp_enqueue_script('sp-slidemenu-js', getUrl('wp-style') . '/' . self::LIB_PATH . '/sp-slidemenu/sp-slidemenu.js', array(), null, true);
        wp_enqueue_script('bootstarp-js', getUrl('wp-style') . '/' . self::LIB_PATH . '/bootstrap/core/bootstrap.min.js', array(), '3.3.6', true);
        wp_enqueue_script('access-google-js', getUrl('wp-style') . '/' . self::JS_PATH . '/access_google.js', array(), null, true);
        wp_enqueue_script('bxslider-js', getUrl('wp-style') . '/' . self::LIB_PATH . '/jquery/bxslider/jquery.bxslider.min.js', array('jquery-mig'), '4.1.2', true);

        // Google Maps
        $params = '';
        $key = getConst('GOOGLE_MAPS_API_KEY');
        if ($key) {
            $params = '?key=' . $key;
        }
        wp_enqueue_script('gmaps-api-js', 'https://maps.googleapis.com/maps/api/js' . $params, array(), null, true);
        wp_enqueue_script('googlemap-js', getUrl('wp-style') . '/' . self::JS_PATH . '/sunat.googleMap.js', array('jquery-mig', 'gmaps-api-js'), null, true);
        wp_enqueue_script('gmap-js', getUrl('wp-style') . '/' . self::JS_PATH . '/gmap.js', array('googlemap-js'), null, true);

        // COMMON
        wp_enqueue_script('common-js', getUrl('wp-style') . '/' . self::JS_PATH . '/common.js', array('jquery-mig'), null, true);
    }

    // フロントにCSSファイルを追加する
    public function addFrontStyle()
    {
        wp_enqueue_style('dashicons');

        if ($this->exists('css', $this->config)) {
            foreach ($this->config['css'] as $no => $css) {
                wp_enqueue_style('add-style-' . $no, getUrl('wp-style') . '/' . $css);
            }
        }
    }

}
