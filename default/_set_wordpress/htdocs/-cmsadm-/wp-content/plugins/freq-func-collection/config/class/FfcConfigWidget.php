<?php

require FFCOLLECTION_PLUGIN_DIR_PATH . 'config/class/FfcConfigPage.php';

class FfcConfigWidget extends FfcConfigPage
{

    const PAGE_NAME = 'widget';
    const PAGE_LABEL = 'ウィジェット';
    protected $fieldKeyList = array(
        array(
            'key' => 'display',
            'title' => '表示',
            'type' => 'group',
            'item' => array(
                array(
                    'key'   => 'name',
                    'title' => 'エリア名',
                    'type'  => 'text'
                ),
                array(
                    'key'   => 'description',
                    'title' => 'エリア説明',
                    'type'  => 'text'
                )
            )
        ),
        array(
            'key' => 'attribute',
            'title' => 'IDなど',
            'type' => 'group',
            'item' => array(
                array(
                    'key'   => 'id',
                    'title' => 'ID',
                    'type'  => 'text'
                ),
                array(
                    'key'   => 'class',
                    'title' => '管理画面でのclass',
                    'type'  => 'text'
                )
            )
        ),
        array(
            'key' => 'widget',
            'title' => 'ウィジェット前後HTML',
            'type' => 'group',
            'item' => array(
                array(
                    'key'   => 'before',
                    'title' => '直前',
                    'type'  => 'text'
                ),
                array(
                    'key'   => 'after',
                    'title' => '直後',
                    'type'  => 'text'
                )
            )
        ),
        array(
            'key' => 'title',
            'title' => 'タイトル前後HTML',
            'type' => 'group',
            'item' => array(
                array(
                    'key'   => 'before',
                    'title' => '直前',
                    'type'  => 'text'
                ),
                array(
                    'key'   => 'after',
                    'title' => '直後',
                    'type'  => 'text'
                )
            )
        )
    );
    protected $pluginWidgetList = array();
    protected $otherWidgetList = array();

    protected function adminInitialize()
    {
        parent::adminInitialize();

        // 当プラグインではwidgets_initの99でregister,unregisterをしているので
        // その直前に登録済みウィジェットおよび当プラグインのウィジェットを取得しておく
        add_action('widgets_init', array($this, 'getWidgetList'), 98);
    }

    public function getWidgetList()
    {
        // 登録済みウィジェットクラス名を取得する
        global $wp_widget_factory;
        foreach ($wp_widget_factory->widgets as $widget) {
            $otherWidget = array(
                'class' => get_class($widget),
                'name'  => $widget->name
            );
            if ($this->exists('description', $widget->widget_options, false)) {
                $otherWidget['description'] = $widget->widget_options['description'];
            }
            $this->otherWidgetList[] = $otherWidget;
        }

        // 自プラグインのウィジェットクラス名を取得する
        $path = FFCOLLECTION_PLUGIN_DIR_PATH . FFCOLLECTION_ADDONS_DIR_NAME . '/widget/class';
        if ($dh = opendir($path)) {
            while (false !== ($classFile = readdir($dh))) {
                if (('.' === $classFile)
                    || ('..' === $classFile)
                    || !is_file($path . '/' . $classFile)
                ) {
                    continue;
                }

                require $path . '/' . $classFile;
                $pathinfo = pathinfo($path . '/' . $classFile);
                $widget = new $pathinfo['filename'];
                $pluginWidget = array(
                    'class' => $pathinfo['filename'],
                    'name'  => $widget->name
                );
                if ($this->exists('description', $widget->widget_options, false)) {
                    $pluginWidget['description'] = $widget->widget_options['description'];
                }
                $this->pluginWidgetList[] = $pluginWidget;
            }
            closedir($dh);
        }
    }

    protected function parse($arrValue)
    {
        if ($this->exists('area', $arrValue)
            && is_array($arrValue['area'])
        ) {
            foreach ($arrValue['area'] as &$area) {
                $area = wp_parse_args($area, array(
                    'name'          => '',
                    'id'            => '',
                    'description'   => '',
                    'class'         => '',
                    'before_widget' => '',
                    'after_widget'  => '',
                    'before_title'  => '',
                    'after_title'   => ''
                ));
                $area = array(
                    'display' => array(
                        'name'        => $area['name'],
                        'description' => $area['description']
                    ),
                    'attribute' => array(
                        'id'    => $area['id'],
                        'class' => $area['class']
                    ),
                    'widget' => array(
                        'before' => $area['before_widget'],
                        'after'  => $area['after_widget']
                    ),
                    'title' => array(
                        'before' => $area['before_title'],
                        'after'  => $area['after_title']
                    )
                );
            }
            unset($area);
        } else {
            $arrValue['area'] = array();
        }

        if (!$this->exists('register', $arrValue)
            || !is_array($arrValue['register'])
        ) {
            $arrValue['register'] = array();
        }

        if (!$this->exists('unregister', $arrValue)
            || !is_array($arrValue['unregister'])
        ) {
            $arrValue['unregister'] = array();
        }

        return $arrValue;
    }

    public function preUpdatePageConfig($arrValue)
    {
        if (is_null($arrValue)) {
            $arrValue = array();
        }

        if ($this->exists('area', $arrValue)
            && is_array($arrValue['area'])
        ) {
            foreach ($arrValue['area'] as &$area) {
                $area = wp_parse_args($area, array(
                    'display' => array(
                        'name'        => '',
                        'description' => ''
                    ),
                    'attribute' => array(
                        'id'    => '',
                        'class' => ''
                    ),
                    'widget' => array(
                        'before' => '',
                        'after'  => ''
                    ),
                    'title' => array(
                        'before' => '',
                        'after'  => ''
                    )
                ));
                $area = array(
                    'name'          => $area['display']['name'],
                    'description'   => $area['display']['description'],
                    'id'            => $area['attribute']['id'],
                    'class'         => $area['attribute']['class'],
                    'before_widget' => $area['widget']['before'],
                    'after_widget'  => $area['widget']['after'],
                    'before_title'  => $area['title']['before'],
                    'after_title'   => $area['title']['after']
                );
                if (!strlen($area['name'])) {
                    unset($area['name']);
                }
                if (!strlen($area['id'])) {
                    unset($area['id']);
                }
            }
            unset($area);
        }

        return $arrValue;
    }

}
