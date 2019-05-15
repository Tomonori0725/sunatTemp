<?php

abstract class ThemeDataListBase extends ThemeClassBase
{

    protected $config = array();
    protected $data = array();

    protected function initialize()
    {
        include_once trailingslashit(get_stylesheet_directory()) . 'includes/data/data.php';

        $config = $this->getConfig();
        if (!$this->exists('const', $config)
            || !is_array($config['const'])
        ) {
            return;
        }
        $this->config = $config['const'];
    }

    public function getList($type)
    {
        $data = array();
        if (array_key_exists($type, $this->data)) {
            $data = $this->data[$type];
        }
        return $data;
    }

    public function getData($type, $id)
    {
        $data = null;
        if (array_key_exists($type, $this->data)) {
            if (array_key_exists($id, $this->data[$type])) {
                $data = $this->data[$type][$id];
            }
        }
        return $data;
    }

    public function getConst($id)
    {
        $value = null;
        if (array_key_exists($id, $this->config)) {
            $value = $this->config[$id];
        }
        return $value;
    }

}
