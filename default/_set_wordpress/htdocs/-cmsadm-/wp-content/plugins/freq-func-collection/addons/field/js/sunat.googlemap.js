;window.sunat = window.sunat || {};

// Google Mapコントロールクラス
(function () {
    'use strict';

    var GoogleMap_ = function (canvas, options) {
        this.INIT_DISPLAY_ = {
            'lat': 36.09357026114795,
            'lng': 140.09201212476728,
            'zoom': 21
        };
        this.map_ = null;
        this.info_ = null;
        this.group_ = {};
        this.defaultOptions_ = {};

        this.init_(canvas, options);
    };
    /* publicメソッド */
    // マップオブジェクトを取得する
    GoogleMap_.prototype.getMap = function (lat, lng, options) {
        return this.map_;
    };
    // マーカーをセットする
    GoogleMap_.prototype.marker = function (lat, lng, options) {
        if ('undefined' === typeof options) {
            options = {};
        }
        options.position = new google.maps.LatLng(lat, lng);
        var marker = (new this.overlay_(this)).create('Marker', sunat.utils.extend({}, this.defaultOptions_.marker, options));
        // マーカーグループ
        if (!(options['group'])) {
            options['group'] = 'always';
        }
        if (!(options['group'] in this.group_)) {
            this.group_[options['group']] = [];
        }
        this.group_[options['group']].push(marker);
        // 情報ウィンドウの紐付け
        if ('info' in options && options['info']) {
            var self = this;
            // クリックされたら情報ウィンドウに自身を紐付けコンテンツをセットする
            google.maps.event.addListener(marker.object, 'click', function () {
                self.info_.show(options['info'], marker.object);
//                self.map_.panTo(marker.object.getPosition());
            });
        }
        return marker;
    };
    // 円をセットする
    GoogleMap_.prototype.circle = function (lat, lng, radius, options) {
        if ('undefined' === typeof options) {
            options = {};
        }
        options.center = new google.maps.LatLng(lat, lng);
        options.radius = radius;
        return (new this.overlay_(this)).create('Circle', sunat.utils.extend({}, this.defaultOptions_.circle, options));
    };
    // 矩形をセットする
    GoogleMap_.prototype.rectangle = function (lat1, lng1, lat2, lng2, options) {
        if ('undefined' === typeof options) {
            options = {};
        }
        options.bounds = new google.maps.LatLngBounds(new google.maps.LatLng(lat1, lng1), new google.maps.LatLng(lat2, lng2));
        return (new this.overlay_(this)).create('Rectangle', sunat.utils.extend({}, this.defaultOptions_.rectangle, options));
    };
    // ポリゴンをセットする
    GoogleMap_.prototype.polygon = function (paths, options) {
        if ('undefined' === typeof options) {
            options = {};
        }
        options.paths = [];
        for (var i = 0; i < paths.length; i++) {
            options.paths.push(new google.maps.LatLng(paths[i].lat, paths[i].lng));
        }
        return (new this.overlay_(this)).create('Polygon', sunat.utils.extend({}, this.defaultOptions_.polygon, options));
    };
    // 表示グループを切り替える
    GoogleMap_.prototype.group = function (name) {
        var isAll = false;
        // グループ名指定がなければすべて表示
        if ('undefined' === typeof name || name === null || name === '') {
            isAll = true;
        }
        var bounds = new google.maps.LatLngBounds();
        // 指定されたグループを表示
        for (var item in this.group_) {
            if (isAll) {
                // すべて表示なら指定グループ名を更新
                name = item;
            }
            // 属しているグループによって表示を切り替える
            if (item == name || item == 'always') {
                // 指定グループもしくは常に表示なら表示
                for (var i = 0; i < this.group_[item].length; i++) {
                    this.group_[item][i].show();
                    // 枠にセット
                    bounds.extend(this.group_[item][i].object.position);
                }
            } else {
                // 指定グループでなければ非表示
                for (i = 0; i < this.group_[item].length; i++) {
                    this.group_[item][i].hide();
                }
            }
        }
        // 枠表示
        this.map_.fitBounds(bounds);
    };

    /* privateメソッド */
    // Googleマップの初期化・生成
    GoogleMap_.prototype.init_ = function (canvas, options) {
        if ('undefined' === typeof options) {
            options = {};
        }
        this.setDefaultValue_();
        this.map_ = new google.maps.Map(canvas, sunat.utils.extend({}, this.defaultOptions_.maps, options)); // マップ生成
        var type = '';
        if (options.hasOwnProperty('type')) {
            type = options.type;
        }
        this.info_ = (new this.infoWindow_(this)).create(type); // 情報ウィンドウ生成
    };
    // デフォルト設定値のセット
    GoogleMap_.prototype.setDefaultValue_ = function () {
        this.defaultOptions_ = {
            maps: {
                center: new google.maps.LatLng(this.INIT_DISPLAY_.lat, this.INIT_DISPLAY_.lng),
                zoom: this.INIT_DISPLAY_.zoom,
                zoomControlOptions : {
                    style: google.maps.ZoomControlStyle.SMALL
                },
                panControl: false,
                streetViewControl: true,
                mapTypeControl: true,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            },
            marker: {
                title: '',
                clickable: true
            },
            circle: {
                strokeColor: '#ff0000',
                strokeOpacity: 1,
                strokeWeight: 1,
                fillColor: '#ff0000',
                fillOpacity: 0,
                clickable: false
            },
            rectangle: {
                strokeColor: '#ff0000',
                strokeOpacity: 1,
                strokeWeight: 1,
                fillColor: '#ff0000',
                fillOpacity: 0,
                clickable: false
            },
            polygon: {
                strokeColor: '#ff0000',
                strokeOpacity: 1,
                strokeWeight: 1,
                fillColor: '#ff0000',
                fillOpacity: 0,
                clickable: false
            }
        }
    };

    sunat.googleMap = GoogleMap_;
})();

// オーバーレイラップクラス
(function () {
    'use strict';

    var Overlay_ = function (parent_) {
        this.parent_ = parent_;
        this.object = null; // オーバーレイオブジェクト
    };
    // オーバーレイ生成
    Overlay_.prototype.create = function (type, options) {
        this.object = new (google.maps[type])(sunat.utils.extend({map: this.parent_.map_}, options));
        return this;
    };
    // オーバーレイ表示
    Overlay_.prototype.show = function () {
        this.object.setVisible(true);
        return this;
    };
    // オーバーレイ非表示
    Overlay_.prototype.hide = function () {
        this.object.setVisible(false);
        return this;
    };
    // オーバーレイ表示切替え
    Overlay_.prototype.toggle = function () {
        if (this.object.getVisible()) {
            this.hide();
        } else {
            this.show();
        }
        return this;
    };

    sunat.googleMap.prototype.overlay_ = Overlay_;
})();

// 情報ウィンドウラップクラス
(function () {
    'use strict';

    var InfoWindow_ = function (parent_) {
        this.parent_ = parent_
        this.object = null; // 情報ウィンドウオブジェクト
    };
    // 情報ウィンドウ生成
    InfoWindow_.prototype.create = function (type) {
        if ('undefined' === typeof type) {
            type = 'design';
        }
        if ('design' === type) {
            this.object = new this.parent_.designWindow_();
        } else {
            this.object = new google.maps.InfoWindow();
        }
        return this;
    };
    // 情報ウィンドウ表示
    InfoWindow_.prototype.show = function (content, marker) {
        this.object.setContent(content);
        this.object.open(this.parent_.map_, marker);
        return this;
    };
    // 情報ウィンドウ非表示
    InfoWindow_.prototype.hide = function () {
        this.object.close();
        return this;
    };

    sunat.googleMap.prototype.infoWindow_ = InfoWindow_;
})();

// フルデザイン可能情報ウィンドウクラス
(function ($) {
    'use strict';

    var DesignWindow_ = function () {
        this.container_ = $('<div class="designWindow" style="position: absolute;"></div>');
        this.layer_ = null;
        this.marker_ = null;
        this.position_ = null;
    };
    DesignWindow_.prototype = new google.maps.OverlayView();
    DesignWindow_.prototype.onAdd = function () {
        var self = this;
        this.layer_ = $(this.getPanes().floatPane);
        this.layer_.append(this.container_);
        this.container_.find('.closeButton').on('click', function (e) {
            self.close();
            return false;
        });
    };
    DesignWindow_.prototype.draw = function () {
        var markerIcon = this.marker_.getIcon();
        var cHeight = this.container_.outerHeight() - this.marker_.anchorPoint.y;
        var cWidth = this.container_.outerWidth() / 2;
        this.position_ = this.getProjection().fromLatLngToDivPixel(this.marker_.getPosition());
        this.container_.css({
            'top': this.position_.y - cHeight,
            'left': this.position_.x - cWidth
        });
    };
    DesignWindow_.prototype.onRemove = function () {
        this.container_.remove();
    };
    DesignWindow_.prototype.setContent = function (content) {
        this.container_.html(content);
    };
    DesignWindow_.prototype.open = function (map, marker) {
        this.marker_ = marker;
        this.setMap(map);
    };
    DesignWindow_.prototype.close = function () {
        this.setMap(null);
    };

    sunat.googleMap.prototype.designWindow_ = DesignWindow_;
})(jQuery);
