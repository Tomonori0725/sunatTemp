<?php

if (!class_exists('FfcConfigPage')) :

abstract class FfcConfigPage extends FfcBaseClass
{

    const DIRECTORY_NAME = 'config';
    protected $config = array();

    public function initialize()
    {
        parent::initialize();

        $this->config = $this->loadConfig();

        if (is_admin()) {
            $this->adminInitialize();
        }
    }

    protected function adminInitialize()
    {
        add_filter('pre_update_option_ffc-config-' . $this::PAGE_NAME, array($this, 'preUpdatePageConfig'), 10, 1);
    }

    /**
     * メニューページを追加する.
     *
     * @access public
     * @return void
     */
    public function addMenuPage($parentMenuSlug)
    {
        add_submenu_page($parentMenuSlug, $this::PAGE_LABEL . '設定 &lsaquo; Freq Func COLLECTION', $this::PAGE_LABEL, 'manage_options', 'ffc-config-' . $this::PAGE_NAME, array($this, 'page'));
    }

    public function description()
    {
        echo '<p>' . $this::PAGE_LABEL . 'の設定をします。</p>';
    }

    public function page()
    {
        $this->main->resource->set('style', FFCOLLECTION_PLUGIN_DIR_URL . '/' . self::DIRECTORY_NAME . '/css/common.css', array(), 'admin');
        if (is_file(FFCOLLECTION_PLUGIN_DIR_PATH . self::DIRECTORY_NAME . '/css/' . $this::PAGE_NAME . '.css')) {
            $this->main->resource->set('style', FFCOLLECTION_PLUGIN_DIR_URL . '/' . self::DIRECTORY_NAME . '/css/' . $this::PAGE_NAME . '.css', array(), 'admin');
        }
        $this->main->resource->set('script', FFCOLLECTION_PLUGIN_DIR_URL . '/' . self::DIRECTORY_NAME . '/js/common.js', array('deps' => array('jquery-ui-sortable')), 'admin');
        if (is_file(FFCOLLECTION_PLUGIN_DIR_PATH . self::DIRECTORY_NAME . '/js/' . $this::PAGE_NAME . '.js')) {
            $this->main->resource->set('script', FFCOLLECTION_PLUGIN_DIR_URL . '/' . self::DIRECTORY_NAME . '/js/' . $this::PAGE_NAME . '.js', array('deps' => array('jquery-ui-sortable')), 'admin');
        }
        $pageName = $this::PAGE_NAME;
        $value = $this->parse($this->getConfig());
        include FFCOLLECTION_PLUGIN_DIR_PATH . 'config/view/page.php';
    }

    protected function parse($arrValue)
    {
        return $arrValue;
    }

    public function preUpdatePageConfig($arrValue)
    {
        return $arrValue;
    }

    protected function loadConfig()
    {
        return get_option('ffc-config-' . $this::PAGE_NAME, array());
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function migration($dbVersion, $currentVersion)
    {
        return;
    }

}

endif;
