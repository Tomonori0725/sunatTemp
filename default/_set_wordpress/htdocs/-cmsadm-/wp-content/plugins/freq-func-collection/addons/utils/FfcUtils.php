<?php

if (!class_exists('FfcUtils')) :

/**
 * ユーティリティクラス.
 * 便利機能の提供を行う。
 */
class FfcUtils extends FfcBaseClass
{

    /**
     * 初期化する.
     *
     * @access protected
     * @return void
     */
    protected function initialize()
    {
        $this->addShortcode();
    }

    // ショートコード追加
    protected function addShortcode()
    {
        add_shortcode('url', array($this, 'shortcodeUrl'));
        add_shortcode('antispam', array($this, 'shortcodeAntispam'));
    }

    public function shortcodeUrl($atts)
    {
        $atts = shortcode_atts(array(
            'type' => 'wp-top'
        ), $atts);

        return get_the_ffc_url($atts['type']);
    }

    public function shortcodeAntispam($atts)
    {
        $atts = shortcode_atts(array(
            'email' => get_bloginfo('admin_email')
        ), $atts);

        return esc_html(antispambot($atts['email']));
    }

    /**
     * 各種URLを取得する.
     * 最後にスラッシュを付加しない。
     *
     * @param string $type URL種別
     *                     wp-top：WordPressのトップページにアクセスするためのURL
     *                     wp-url：WordPressがインストールされているURL
     *                     wp-content：wp-contentのURL
     *                     wp-parent：親テーマがあれば親テーマ、なければ現在のテーマのURL
     *                     wp-theme：現在のテーマのURL
     *
     * @access public
     * @return string URL
     */
    public function getUrl($type = 'wp-top')
    {
        switch ($type) {
            case 'wp-top':
                // サイトにアクセスするためのURL
                $ret = home_url();
                break;
            case 'wp-url':
                // WordPressがインストールされているURL
                $ret = site_url();
                break;
            case 'wp-content':
                $ret = content_url();
                break;
            case 'wp-parent':
                $ret = get_template_directory_uri();
                break;
            case 'wp-theme':
                $ret = get_stylesheet_directory_uri();
                break;
            default:
                $ret = '';
                break;
        }

        return untrailingslashit($ret);
    }

    /**
     * 各種パスを取得する.
     * 最後にスラッシュを付加する。
     *
     * @param string $type パス種別
     *                     wp-path：WordPressがインストールされているパス
     *                     wp-content：wp-contentのパス
     *                     wp-parent：親テーマがあれば親テーマ、なければ現在のテーマのパス
     *                     wp-theme：現在のテーマのパス
     *
     * @access public
     * @return string パス
     */
    public function getPath($type)
    {
        switch ($type) {
            case 'wp-path':
                $ret = ABSPATH;
                break;
            case 'wp-content':
                $ret = WP_CONTENT_DIR;
                break;
            case 'wp-parent':
                $ret = get_template_directory();
                break;
            case 'wp-theme':
                $ret = get_stylesheet_directory();
                break;
            default:
                $ret = '';
                break;
        }

        return trailingslashit($ret);
    }

}

endif;
