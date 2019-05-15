<?php

if (!class_exists('FfcResource')) :

/**
 * リソースクラス.
 * リソースファイルの設定や追加を行う。
 */
class FfcResource extends FfcBaseClass
{

    const CONFIG_NAME = 'resource';
    const DIRECTORY_NAME = 'resource';
    protected $resourceOptionsDefault = array(
        'handle'    => '',
        'deps'      => array(),
        'ver'       => false,
        'in_footer' => true,
        'media'     => 'all'
    );
    protected $config = array();
    protected $resource = array(
        'front' => array(
            'style'  => array(),
            'script' => array()
        ),
        'admin' => array(
            'style'  => array(),
            'script' => array()
        ),
        'login' => array(
            'style'  => array(),
            'script' => array()
        )
    );
    protected $urls = array(
        'front' => array(
            'style'  => array(),
            'script' => array()
        ),
        'admin' => array(
            'style'  => array(),
            'script' => array()
        ),
        'login' => array(
            'style'  => array(),
            'script' => array()
        )
    );
    protected $finishEnqueueAction = array();

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

        add_action('login_enqueue_scripts', array($this, 'addLoginScripts'));
        add_action('admin_enqueue_scripts', array($this, 'addAdminScripts'));
        add_action('wp_enqueue_scripts', array($this, 'addFrontScripts'));

        $this->addImageSize();
    }

    /**
     * リソースファイルを登録する.
     *
     * @param string  $type    リソースタイプ(style / script)
     * @param string  $url     リソースファイルURL
     * @param array   $options パラメーター(wp_enqueue_style, wp_enqueue_script のハンドル以外のパラメーター)
     * @param boolean $admin   管理画面:true / フロント:false
     *
     * @access public
     * @return void
     */
    public function set($type, $url, $options = array(), $side = 'front')
    {
        if (!in_array($type, array('style', 'script'), true)
            || 0 === strlen($url)
        ) {
            return;
        }
        if (!array_key_exists($side, $this->resource)) {
            $side = 'front';
        }

        // 登録済みURLなら登録しない
        if (in_array($url, $this->urls[$side][$type])) {
            return;
        }

        $resource = array(
            'url'     => $url,
            'options' => wp_parse_args($options, $this->resourceOptionsDefault)
        );
        // handleがなければ自動で付ける
        if (0 === strlen($resource['options']['handle'])) {
            $resource['options']['handle'] = 'add-ffc-' . $type . '-' . (count($this->resource[$side][$type]) + 1);
        }
        // 登録リストに入れる
        $this->resource[$side][$type][] = $resource;
        $this->urls[$side][$type][] = $url;

        if (in_array($side, $this->finishEnqueueAction, true)) {
            // admin_enqueue_scripts, wp_enqueue_scripts完了済みで同じ側なら直接登録する
            $this->enqueueResource($type, $resource);
        }
    }

    /**
     * 画像サイズを追加する.
     *
     * @access public
     * @return void
     */
    protected function addImageSize()
    {
        if ($this->exists('image-size', $this->config)
            && is_array($this->config['image-size'])
        ) {
            foreach ($this->config['image-size'] as $name => $value) {
                if (!$this->exists('width', $value)
                    || !$this->exists('height', $value)
                ) {
                    continue;
                }
                if ($this->exists('crop', $value)
                    && $value['crop']
                ) {
                    $value['crop'] = true;
                } else {
                    $value['crop'] = false;
                }
                add_image_size($name, $value['width'], $value['height'], $value['crop']);
            }
            add_filter('image_size_names_choose', array($this, 'imageSizeNamesChoose'), 11, 1);
        }
    }

    /**
     * 追加したサイズを画像サイズ選択に追加する.
     *
     * @param array $sizes 追加前画像サイズ配列
     *
     * @access public
     * @return array 追加後画像サイズ配列
     */
     public function imageSizeNamesChoose($sizes)
    {
        $addSizes = array();
        $allSizes = get_intermediate_image_sizes();
        $config = $this->config['image-size'];
        foreach($allSizes as $name) {
            if (!$this->exists($name, $config)
                || !$this->exists('width', $config[$name])
                || !$this->exists('height', $config[$name])
            ) {
                continue;
            }
            $label = $name;
            if ($this->exists('label', $config[$name], false)
                && is_string($config[$name]['label'])
            ) {
                $label = $config[$name]['label'];
            }
            $addSizes[$name] = $label;
        }

        return array_merge($sizes, $addSizes);
    }

    protected function enqueueResource($type, $resource)
    {
        $version = $this->main->version();
        if ($resource['options']['ver']) {
            $version = $resource['options']['ver'];
        }
        if ('style' === $type) {
            wp_enqueue_style($resource['options']['handle'], $resource['url'], $resource['options']['deps'], $version, $resource['options']['media']);
        } else {
            wp_enqueue_script($resource['options']['handle'], $resource['url'], $resource['options']['deps'], $version, $resource['options']['in_footer']);
        }
    }

    /**
     * ログイン画面にCSSファイルを追加する.
     *
     * @access public
     * @return void
     */
    public function addLoginStyles()
    {
        foreach ($this->resource['login']['style'] as $resource) {
            $this->enqueueResource('style', $resource);
        }
    }

    /**
     * ログイン画面にJavaScriptファイルを追加する.
     *
     * @access public
     * @return void
     */
    public function addLoginScripts()
    {
        $this->addLoginStyles();

        foreach ($this->resource['login']['script'] as $resource) {
            $this->enqueueResource('script', $resource);
        }
        $this->finishEnqueueAction[] = 'login';
    }

    /**
     * 管理画面にCSSファイルを追加する.
     *
     * @access public
     * @return void
     */
    public function addAdminStyles()
    {
        foreach ($this->resource['admin']['style'] as $resource) {
            $this->enqueueResource('style', $resource);
        }
    }

    /**
     * 管理画面にJavaScriptファイルを追加する.
     *
     * @access public
     * @return void
     */
    public function addAdminScripts()
    {
        $this->addAdminStyles();

        foreach ($this->resource['admin']['script'] as $resource) {
            $this->enqueueResource('script', $resource);
        }
        $this->finishEnqueueAction[] = 'admin';
    }

    /**
     * フロントにCSSファイルを追加する.
     *
     * @access public
     * @return void
     */
    public function addFrontStyles()
    {
        if ($this->exists('support', $this->config)
            && is_array($this->config['support'])
        ) {
            if (in_array('dashicons', $this->config['support'], true)) {
                wp_enqueue_style('dashicons');
            }
        }
        foreach ($this->resource['front']['style'] as $resource) {
            $this->enqueueResource('style', $resource);
        }
    }

    /**
     * フロントにJavaScriptファイルを追加する.
     *
     * @access public
     * @return void
     */
    public function addFrontScripts()
    {
        $this->addFrontStyles();

        foreach ($this->resource['front']['script'] as $resource) {
            $this->enqueueResource('script', $resource);
        }
        $this->finishEnqueueAction[] = 'front';
    }

}

endif;
