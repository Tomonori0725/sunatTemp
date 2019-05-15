;jQuery(function ($) {
    function getParentNo($element, type) {
        var $targetItem = $element.parents('.' + type + 'Item').first();
        return $targetItem.data(type + 'No');
    }
    function getNextNo($element, type) {
        var noList = [],
            no = 0,
            $targetList = $element.prev('.' + type + 'List');
        $targetList.find('.' + type + 'Item').each(function () {
            noList.push($(this).data(type + 'No'));
        });
        if (noList.length > 0) {
            no = Math.max.apply(null, noList) + 1;
        }
        return no;
    }
    function sortableList($element, type) {
        $element.sortable({
            handle: '.' + type + 'Handle',
            placeholder: 'widget-placeholder'
        });
        $element.disableSelection();
    }

    ['menupage', 'metaboxField'].forEach(function (type) {
        sortableList($('.' + type + 'List'), type);
    });
    $('select[name*="[type]"]').each(function () {
        var $description = $(this).siblings('.description');
        if ('map' === $(this).val()) {
            $description.show();
        } else {
            $description.hide();
        }
    });

    $('body').on('click', '.handle', function (e) {
        $(this).next('.group').animate({
            'height': 'toggle'
        }, 250);
    });

    $('body').on('keyup', 'input[name*="[name]"]', function (e) {
        $(this).parents('.group').first().prev('.handle').find('.name').text($(this).val());
    });
    $('body').on('keyup', 'input[name*="[slug]"]', function (e) {
        var slug = $(this).val();
        if (slug.length) {
            slug = '(' + slug + ')';
        }
        $(this).parents('.group').first().prev('.handle').find('.slug').text(slug);
    });
    $('body').on('keyup', 'input[name*="[title]"]', function (e) {
        $(this).parents('.group').first().prev('.handle').find('.title').text($(this).val());
    });
    $('body').on('keyup', 'input[name*="[key]"]', function (e) {
        var key = $(this).val();
        if (key.length) {
            key = '(' + key + ')';
        }
        $(this).parents('.group').first().prev('.handle').find('.key').text(key);
    });
    $('body').on('change', 'select[name*="[type]"]', function (e) {
        var showList = typeKeyList[$(this).val()],
            $description = $(this).siblings('.description');
        if ('map' === $(this).val()) {
            $description.show();
        } else {
            $description.hide();
        }
        $(this).parents('.group').first().find('tr[data-field-key]').each(function () {
            if (-1 !== showList.indexOf($(this).data('fieldKey'))) {
                $(this).removeClass('hide');
            } else {
                $(this).addClass('hide');
            }
        });
    });

    // アイテム追加
    $('body').on('click', '.addMenupageButton', function (e) {
        var $element = $($('#templateMenupageItem')
            .html()
            .replace(/\{\{MENUPAGE_NO\}\}/g, getNextNo($(this), 'menupage')));
        sortableList($element.find('.metaboxFieldList'), 'metaboxField');
        $element.appendTo($(this).prev('.menupageList'));
    });
    $('body').on('click', '.addMetaboxFieldButton', function (e) {
        var $element = $($('#templateMetaboxFieldItem')
            .html()
            .replace(/\{\{MENUPAGE_NO\}\}/g, getParentNo($(this), 'menupage'))
            .replace(/\{\{METABOX_FIELD_NO\}\}/g, getNextNo($(this), 'metaboxField')));
        $element.appendTo($(this).prev('.metaboxFieldList'));
    });

    $('body').on('click', '.removeItemButton', function (e) {
        $(this).parents('.item').first().remove();
    });

    // 使用例
    $('<div>').addClass('windowWrapper').html('<div class="windowOverlay"></div><div class="windowInner"><pre><code></code></pre><button type="button" class="button button-primary copyCodeButton">コードをクリップボードにコピーする</button></div>').appendTo('body');
    $('body').on('click', '.viewExampleCode', function (e) {
        var $list = $(this).parents('.table-definelist').first(),
            slug = $(this).parents('.menupageItem').first().find('.table-definelist').first().find('[name*="slug"]').val();
            key = $list.find('[data-field-key="key"]').find('input').val(),
            dir = $(this).data('viewCode'),
            type = $list.find('[data-field-key="type"]').find('select').val(),
            $isHtml = $list.find('[data-field-key="html"]');
        if (!$isHtml.hasClass('hide')
            && $isHtml.find('input').prop('checked')
        ) {
            type = type + '-html';
        }
        var html = exampleCode[dir][type].replace(/\{\{SLUG\}\}/g, slug).replace(/\{\{KEY\}\}/g, key);
        $('.windowInner code').html(html);
        $('.windowWrapper').addClass('active');
        return false;
    });
    $('.copyCodeButton').on('click', function (e) {
        var code = $(this).parents('.windowInner').first().find('code').text(),
            $element = $('<textarea>').text(code);
        $('body').append($element);
        $element.select();
        document.execCommand('copy');
        $element.remove();
        alert('使用例のコードをクリップボードにコピーしました。');
        return false;
    });
    $('.windowOverlay').on('click', function (e) {
        $('.windowWrapper').removeClass('active');
        $('.windowInner code').html('');
    });

    $('form').on('submit', function () {
        var invalidCount = 0;
        for (var i = 0; i < this.elements.length; i++) {
            var match = this.elements[i].name.match(/\[fields\]\[\d\]\[key\]/);
            if ('null' === Object.prototype.toString.call(match).slice(8, -1).toLowerCase()) {
                continue;
            }
            if ('' === this.elements[i].value) {
                this.elements[i].classList.add('invalid');
                invalidCount++;
            } else {
                this.elements[i].classList.remove('invalid');
            }
        }
        if (invalidCount > 0) {
            alert('名前(name属性)が空のフィールドが' + invalidCount + 'つあります。\n名前(name属性)は必ず入力してください。');
            return false;
        }
    });
});
