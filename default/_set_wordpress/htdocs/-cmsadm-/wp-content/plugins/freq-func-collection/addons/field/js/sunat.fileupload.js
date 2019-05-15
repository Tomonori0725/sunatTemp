;window.sunat = window.sunat || {};

(function ($) {
    'use strict';

    var FileUpload_ = function () {
        this.init();
    };
    FileUpload_.prototype.init = function () {
        var self_ = this;

        $(document).on('click', '.uploadImageButton', function (e) {
            self_.openUploader(e)
        });
        $(document).on('click', '.removeImageButton',function (e) {
            self_.removeImage(e);
        });

        var $list = $('.uploadImageList').filter('[data-upload-sortable="true"]');
        $list.each(function () {
            $(this).sortable({
                handle: '.sortHandle',
                cursor : 'move',
                tolerance : 'pointer',
                opacity: 0.6
            });
        });
    };
    FileUpload_.prototype.openUploader = function (e)
    {
        e.preventDefault();
        e.stopPropagation();

        // 押されたボタン
        var button = e.currentTarget;

        // ボタンに紐付けられたアップローダーがなければ作成する
        if (!button.hasOwnProperty('$uploadGroup')) {
            button.$uploadGroup = $(button).parents('.uploadImageGroup');
            button.$viewList = button.$uploadGroup.find('.uploadImageList');
            this.createUploader(button.$uploadGroup, button.$viewList);
            if (button.$viewList.data('uploadSortable') && !button.$viewList.hasClass('ui-sortable')) {
                button.$viewList.sortable({
                    handle: '.sortHandle',
                    cursor : 'move',
                    tolerance : 'pointer',
                    opacity: 0.6
                });
            }
        }

        // ボタンに紐付けられたアップローダーを開く
        button.$uploadGroup.uploader.open();
    };
    FileUpload_.prototype.createUploader = function ($uploadGroup, $viewList)
    {
        var self_ = this,
            fileType = [];
        if ($viewList.data('fileType')) {
            switch ($viewList.data('fileType')) {
                case 'image':
                    fileType = ['image'];
                    break;
                default:
                    fileType = $viewList.data('fileType');
                    break;
            }
        }

        $uploadGroup.uploader = new wp.media.view.MediaFrame.Select({
            state : 'mystate',
            title: _wpMediaViewsL10n.mediaLibraryTitle,
            library: {
                type: fileType
            },
            button: {
                text: 'ファイルを登録'
            },
            multiple: $uploadGroup.data('uploadMulti'),
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

        $uploadGroup.uploader
            // .on('activate', function () {})
            // .on('ready', function () {})
            .on('attach', function () {
                // 「この投稿へのアップロード」を選択する
                $('select.attachment-filters').find('[value="uploaded"]').prop('selected', true).parent().trigger('change');
                // 左メニューとタブを隠す
                // $('.media-frame').addClass('hide-menu').addClass('hide-router');
                $('.media-frame').addClass('hide-menu');
            })
            // .on('open', function () {})
            // .on('close', function () {})
            .on('select', function () {
                var selection = $uploadGroup.uploader.state().get('selection'),
                    id = 0,
                    keepIds = [],
                    keepCount = 0;
                if (selection.length) {
                    $viewList.children('li.noImage').remove();
                }
                // 登録済みの画像をチェック
                $viewList.children('li.image').each(function () {
                    id = parseInt($(this).attr('id').slice(6), 10);
                    keepIds.push(id);
                });
                keepCount = keepIds.length;
                // 選択した画像
                selection.each(function (file) {
                    id = file.toJSON().id;
                    // 保持リストにない画像のみ追加
                    if ($.inArray(id, keepIds) == -1) {
                        if (!$uploadGroup.data('uploadMulti')) {
                            $('#image_' + keepIds[0]).remove();
                        }

                        var url,
                            imageHtml = '<a class="removeImageButton dashicons dashicons-dismiss"></a>' + "\n";
                        if ('image' === file.attributes.type) {
                            if (file.attributes.sizes.hasOwnProperty($viewList.data('uploadSize'))) {
                                url = file.attributes.sizes[$viewList.data('uploadSize')].url;
                            } else {
                                url = file.attributes.sizes.full.url;
                            }
                            imageHtml += '<div><img src="' + url + '" class="sortHandle"></div>';
                        } else {
                            imageHtml += '<span class="sortHandle">' + file.attributes.filename + '</span>';
                        }
                        imageHtml += '<input type="hidden" name="' + $viewList.data('uploadName') + '[]" value="'+ id +'">';
                        if ($viewList.data('uploadText').length) {
                            imageHtml += '<input type="text" class="widefat" name="' + $viewList.data('uploadText') + '[]" value="">';
                        }
                        $viewList.append('<li class="image" id="image_' + id + '"></li>').find('li:last').append('<div class="imageWrap">' + imageHtml + '</div>');
                        keepCount++;
                    }
                });
                // if (keepCount > 5) {
                //     alert('選択枚数が5枚を超えました。');
                // }
            })
            // .on('escape', function () {})
            ;
    };
    FileUpload_.prototype.removeImage = function (e) {
        e.preventDefault();
        e.stopPropagation();

        var $button = $(e.currentTarget),
            $viewList = $button.parents('.uploadImageList');
        $button.parents('li.image').remove();
        if ($viewList.children('li.image').length === 0) {
            $viewList.append('<li class="noImage">登録がありません。</li>');
        }
    };

    sunat.fileUpload = FileUpload_;
})(jQuery);

new sunat.fileUpload();
