;var fileUpload = function () {
    this.init();
};
fileUpload.prototype.init = function () {
    var me = this;

    jQuery(document).on('click', '.uploadImageButton', function (e) {
        me.openUploader(e)
    });
    jQuery(document).on('click', '.removeImageButton',function (e) {
        me.removeImage(e);
    });

    var $list = jQuery('.uploadImageList').filter('[data-upload-sortable="true"]');
    $list.each(function () {
        jQuery(this).sortable({
            handle: '.sortHandle',
            cursor : 'move',
            tolerance : 'pointer',
            opacity: 0.6
        });
    });
}
fileUpload.prototype.openUploader = function (e)
{
    e.preventDefault();
    e.stopPropagation();

    // 押されたボタン
    var $button = jQuery(e.currentTarget);

    if ($button.get(0).hasOwnProperty('$uploadGroup')) {
        // ボタンに紐付けられたアップローダーがあれば開く
        $button.get(0).$uploadGroup.uploader.open();
    } else {
        // ボタンに紐付けられたアップローダーがなければ作成する
        $button.get(0).$uploadGroup = $button.parents('.uploadImageGroup');
        $button.get(0).$viewList = $button.get(0).$uploadGroup.find('.uploadImageList');
        this.createUploader($button.get(0).$uploadGroup, $button.get(0).$viewList);
        if ($button.get(0).$viewList.data('uploadSortable') && !$button.get(0).$viewList.hasClass('ui-sortable')) {
            $button.get(0).$viewList.sortable({
                handle: '.sortHandle',
                cursor : 'move',
                tolerance : 'pointer',
                opacity: 0.6
            });
        }
        $button.get(0).$uploadGroup.uploader.open();
    }
}
fileUpload.prototype.createUploader = function ($uploadGroup, $viewList)
{
    var me = this;

    $uploadGroup.uploader = wp.media({
        state : 'mystate',
        title: _wpMediaViewsL10n.mediaLibraryTitle,
        library: {
            type: 'image'
        },
        button: {
            text: 'ファイルを登録'
        },
        multiple: $uploadGroup.data('uploadMulti'),
        frame: 'select',
        editing: false
    });
    $uploadGroup.uploader.states.add([
        new wp.media.controller.Library({
            id: 'mystate',
            title: 'ファイルのアップロード・選択(ドラッグ&ドロップでアップロードできます)',
            priority: 20,
            toolbar: 'select',
            filterable: 'uploaded',
            library: wp.media.query($uploadGroup.uploader.options.library),
            multiple: $uploadGroup.uploader.options.multiple ? 'reset' : false,
            editable: true,
            displayUserSettings: false,
            displaySettings: true,
            allowLocalEdits: true
        })
    ]);

    $uploadGroup.uploader.on('ready', function () {
        jQuery('select.attachment-filters').find('[value="uploaded"]').attr('selected', true).parent().trigger('change');
    }).on('open', function () {
        jQuery('.media-frame').addClass('hide-menu').addClass('hide-router');
    }).on('select', function () {
        var selection = $uploadGroup.uploader.state().get('selection'),
            id = 0,
            keepIds = [];
        if (selection.length) {
            $viewList.children('li.noImage').remove();
        }
        // 登録済みの画像をチェック
        $viewList.children('li.image').each(function () {
            id = Number(jQuery(this).attr('id').slice(6));
            keepIds.push(id);
        });
        // 選択した画像
        selection.each(function (file) {
            id = file.toJSON().id;
            // 保持リストにない画像のみ追加
            if (jQuery.inArray(id, keepIds) == -1) {
                if (!$uploadGroup.data('uploadMulti')) {
                    jQuery('#image_' + keepIds[0]).remove();
                }
                var url;
                if (file.attributes.sizes.hasOwnProperty($viewList.data('uploadSize'))) {
                    url = file.attributes.sizes[$viewList.data('uploadSize')].url;
                } else {
                    url = file.attributes.sizes.full.url;
                }
                var imageHtml = '<a class="removeImageButton dashicons dashicons-dismiss"></a>'
                            + '<div><img src="' + url + '" class="sortHandle"></div>'
                            + '<input type="hidden" name="' + $viewList.data('uploadName');
                if ($uploadGroup.data('uploadMulti')) {
                    imageHtml += '[]';
                }
                imageHtml += '" value="' + id + '">';
                if ($viewList.data('uploadText').length) {
                    imageHtml += '<input type="text" class="widefat" name="' + $viewList.data('uploadText')
                    if ($uploadGroup.data('uploadMulti')) {
                        imageHtml += '[]';
                    }
                    imageHtml += '" value="">';
                }
                $viewList.append('<li class="image" id="image_' + id + '"></li>').find('li:last').append('<div class="imageWrap">' + imageHtml + '</div>');
            }
        });
    });
}
fileUpload.prototype.removeImage = function (e) {
    e.preventDefault();
    e.stopPropagation();

    $button = jQuery(e.currentTarget);
    $viewList = $button.parents('.uploadImageList');
    $button.parents('li.image').remove();
    if ($viewList.children('li.image').length === 0) {
        $viewList.append('<li class="noImage">登録がありません。</li>');
    }
}
new fileUpload();
