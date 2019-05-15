<?php

require FFCOLLECTION_PLUGIN_DIR_PATH . 'config/class/FfcConfigPage.php';

class FfcConfigBasic extends FfcConfigPage
{

    const PAGE_NAME = 'basic';
    const PAGE_LABEL = '基本';
    protected $adminFieldKeyList = array(
        'adminbar_logo' => array(
            'label'       => '管理バーのWordPressロゴを削除する',
            'description' => '左上のロゴです',
            'type'        => 'checkbox'
        ),
        'remove_welcome' => array(
            'label'   => 'ダッシュボードの「ようこそ」パネルを削除する',
            'type'    => 'checkbox'
        ),
        'dashboard_default' => array(
            'label'       => 'ダッシュボードのデフォルトパネルを削除する',
            'description' => '「概要」「アクティビティ」「クイックドラフト」「WordPressイベントとニュース」などが削除されます',
            'type'        => 'checkbox'
        )
    );
    protected $headFieldKeyList = array(
        'feed' => array(
            'label'       => 'フィードURL出力をサポートする',
            'description' => '通常はテーマでサポートしています',
            'type'        => 'checkbox'
        ),
        'remove_head_feed' => array(
            'label'   => 'フィードURLに関するタグを削除する',
            'type'    => 'checkbox'
        ),
        'remove_head_emoji' => array(
            'label'   => '絵文字に関するタグを削除する',
            'type'    => 'checkbox'
        ),
        'remove_head_rest' => array(
            'label'   => 'REST APIに関するタグを削除する',
            'type'    => 'checkbox'
        ),
        'remove_head_dns-prefetch' => array(
            'label'   => 'DNSプリフェッチに関するタグを削除する',
            'type'    => 'checkbox'
        ),
        'remove_head_external' => array(
            'label'   => '外部ツールによる更新用URLに関するタグを削除する',
            'type'    => 'checkbox'
        ),
        'remove_head_basic' => array(
            'label'       => '上記以外で不要と思われるタグを削除する',
            'description' => '削除されるタグを確認して使用してください',
            'type'        => 'checkbox'
        )
    );
    protected $menuFieldKeyList = array(
        'menu' => array(
            'label' => '',
            'type'  => ''
        )
    );
    protected $queryFieldKeyList = array(
        'query-vars' => array(
            'label' => '',
            'type'  => 'textarea'
        )
    );
    protected $otherFieldKeyList = array(
        'redirect_author' => array(
            'label'       => 'ユーザーページへのアクセスを404にする',
            'description' => 'ユーザーページは通常 /?author=%id% もしくは /author/%id%/ です',
            'type'        => 'checkbox'
        )
    );

    protected function parse($arrValue)
    {
        $ret['menu'] = array();
        if ($this->exists('menu', $arrValue)
            && is_array($arrValue['menu'])
        ) {
            foreach ($arrValue['menu'] as $key => $label) {
                $ret['menu'][] = array(
                    'key'   => $key,
                    'label' => $label
                );
            }
        }
        unset($arrValue['menu']);
        $ret['query-vars'] = '';
        if ($this->exists('query-vars', $arrValue)
            && is_array($arrValue['query-vars'])
        ) {
            $ret['query-vars'] = "\n" . implode("\n", $arrValue['query-vars']);
        }
        unset($arrValue['query-vars']);
        $ret = array_merge($ret, $this->parseArray($arrValue));

        return $ret;
    }

    protected function parseArray($arrValue, $parentKey = '')
    {
        $ret = array();
        foreach ($arrValue as $key => $value) {
            if (is_array($value)) {
                $ret = array_merge($ret, $this->parseArray($value, $parentKey . $key . '_'));
            } else {
                $ret[$parentKey . $key] = $value;
            }
        }
        return $ret;
    }

    public function preUpdatePageConfig($arrValue)
    {
        $ret = array();
        if ($this->exists('menu', $arrValue)
            && $this->exists('key', $arrValue['menu'])
            && is_array($arrValue['menu']['key'])
        ) {
            $ret['menu'] = array();
            foreach ($arrValue['menu']['key'] as $no => $key) {
                if (!strlen($key)
                    || !$this->exists($no, $arrValue['menu']['label'], false)
                    || !is_string($arrValue['menu']['label'][$no])
                ) {
                    continue;
                }
                $ret['menu'][$key] = $arrValue['menu']['label'][$no];
            }
        }
        unset($arrValue['menu']);
        if ($this->exists('query-vars', $arrValue, false)
            && is_string($arrValue['query-vars'])
        ) {
            $queryVars = str_replace("\r\n", "\n", $arrValue['query-vars']);
            $queryVars = str_replace("\r", "\n", $queryVars);
            $queryVars = explode("\n", $queryVars);
            $ret['query-vars'] = array_values(array_filter($queryVars, 'strlen'));
        }
        unset($arrValue['query-vars']);
        foreach ($arrValue as $key => $value) {
            $ret = array_merge_recursive($ret, $this->buildArray($key, $value));
        }

        return $ret;
    }

    protected function buildArray($key, $value, $parentKey = '')
    {
        $ret = array();
        $arrKey = explode('_', $key, 2);
        if (2 === count($arrKey)) {
            if (!$this->exists($arrKey[0], $ret)) {
                $ret[$arrKey[0]] = array();
            }
            $ret[$arrKey[0]] = array_merge($ret[$arrKey[0]], $this->buildArray($arrKey[1], $value, $parentKey . $arrKey[0] . '_'));
        } else {
            $ret[$key] = intval($value);
        }
        return $ret;
    }

}
