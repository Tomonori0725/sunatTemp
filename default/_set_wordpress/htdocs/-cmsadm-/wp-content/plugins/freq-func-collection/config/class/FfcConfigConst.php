<?php

require FFCOLLECTION_PLUGIN_DIR_PATH . 'config/class/FfcConfigPage.php';

class FfcConfigConst extends FfcConfigPage
{

    const PAGE_NAME = 'const';
    const PAGE_LABEL = '定数';

    public function description()
    {
        parent::description();
        echo '<button type="button" class="viewDescription">説明を表示する</button>';
        echo '<div class="funcDescription"><p>テンプレート上で固定の値として使用できます。</p>';
        echo '<p><pre><code>&lt;?php the_ffc_const(\'キー\'); ?&gt;</code></pre>とすると値が表示できます。<br>値を表示せず取得する場合は<pre><code>&lt;?php $value = get_the_ffc_const(\'キー\'); ?&gt;</code></pre>とすると取得できます。</p>';
        echo '<p>「PHPの定数として定義する」にチェックを入れるとキーのみで値が取得できます。<br>例えば、キーが「EXAMPLE」で値が「定数の例」とすると<pre><code>&lt;?php echo EXAMPLE; ?&gt;</code></pre>で「定数の例」が表示されます。<br>これは<pre><code>&lt;?php echo get_the_ffc_const(\'EXAMPLE\'); ?&gt;</code></pre>や<pre><code>&lt;?php the_ffc_const(\'EXAMPLE\'); ?&gt;</code></pre>と同じ結果になります。</p></div>';
    }

    protected function parse($arrValue)
    {
        $ret = array(
            'const' => array(),
            'define' => false
        );

        if ($this->exists('const', $arrValue)
            && is_array($arrValue['const'])
        ) {
            foreach ($arrValue['const'] as $var => $value) {
                if (is_bool($value)) {
                    if ($value) {
                        $value = 'true';
                    } else {
                        $value = 'false';
                    }
                }
                $ret['const'][] = array(
                    'var'   => $var,
                    'value' => $value
                );
            }
        }

        if ($this->exists('define', $arrValue)
            && $arrValue['define']
        ) {
            $ret['define'] = true;
        }

        return $ret;
    }

    public function preUpdatePageConfig($arrValue)
    {
        $ret = array(
            'const' => array(),
            'define' => false
        );

        $index = 0;
        if ($this->exists('value', $arrValue)
            && is_array($arrValue['value'])
            && is_array($arrValue['var'])
        ) {
            foreach ($arrValue['value'] as $value) {
                if ($this->exists($index, $arrValue['var'])
                    && strlen($arrValue['var'][$index])
                ) {
                    // 数値と正しい記述の場合は数値に型変換する
                    if (preg_match('/^[\d\.-]+$/', $value)) {
                        $float = floatval($value);
                        if (strval($float) === $value) {
                            $value = $float;
                        }
                    }
                    // boolean文字列の場合はbooleanに型変換する
                    $lower = strtolower($value);
                    if ('true' === $lower) {
                        $value = true;
                    } elseif ('false' === $lower) {
                        $value = false;
                    }
                    $ret['const'][$arrValue['var'][$index]] = $value;
                }
                $index++;
            }
        }

        if ($this->exists('define', $arrValue)
            && $arrValue['define']
        ) {
            $ret['define'] = true;
        }

        return $ret;
    }

}
