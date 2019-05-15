<div class="wrap">
    <form action="options.php" method="post">
        <?php
        settings_fields('top-carousel-section-group');
//        do_settings_sections('top-carousel');
        ?>
        <h2>トップカルーセル設定</h2>
        <p>
            <span class="button">追加</span>ボタンで画像を追加できます。<br>
            複数枚追加する時は画像選択画面で「Ctrl」キーを押しながら選択してください。<br>
            「Shift」キーを押しながら選択すれば範囲選択もできます。<br>
            (1枚ずつ<span class="button">追加</span>しても複数枚追加できます。)<br>
            削除する時は<i class="dashicons dashicons-dismiss" aria-hidden="true"></i>を押してください。(表示はされなくなりますが画像は削除されません。)<br>
            画像をドラッグ＆ドロップすれば表示順の並び替えができます。
        </p>
        <table class="form-table">
        <tr>
            <th scope="row">画像</th>
            <td>
                <?php
                uploadImageTemplate(array(
                    'imageName' => 'top-carousel-image',
                    'textName'  => 'top-carousel-text',
                    'imageId'   => get_option('top-carousel-image'),
                    'text'      => get_option('top-carousel-text'),
                ));
                ?>
            </td>
        </tr>
        </table>
        <?php
        submit_button();
        ?>
    </form>
</div>
