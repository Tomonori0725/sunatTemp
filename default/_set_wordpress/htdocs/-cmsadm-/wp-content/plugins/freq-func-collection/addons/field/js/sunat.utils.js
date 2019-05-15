;window.sunat = window.sunat || {};

(function () {
    'use strict';

    var supportsPassive = false;
    try {
        var options = {
            get passive() {
                supportsPassive = true;
            }
        },
        handler = function () {};
        window.addEventListener('checkOptions', handler, options);
        window.removeEventListener('checkOptions', handler, options);
    } catch (error) {
    }

    var Utils_ = function () {};
    // jQuery.extend の模倣
    Utils_.prototype.extend = function () {
        for (var i = 1; i < arguments.length; i++) {
            for (var key in arguments[i]) {
                if (arguments[i].hasOwnProperty(key)) {
                    arguments[0][key] = arguments[i][key];
                }
            }
        }
        return arguments[0];
    };
    // AddEventListenerOptionsを判定してイベントリスナー登録を行う。
    Utils_.prototype.addEventListener = function (target, type, handler, options) {
        var self_ = this,
            options = self_.extend({
                'capture': false,
                'passive': false
            }, options);

        if (!supportsPassive) {
            options = options.capture;
        }
        target.addEventListener(type, handler, options);
    };
    // scriptタグのsrcのファイル名からそのディレクトリーを取得する
    Utils_.prototype.dirname = function (filename) {
        var scripts = document.getElementsByTagName('script'),
            length = scripts.length,
            dirname = false;
        for (var i = 0; i < length; i++) {
            var regex = new RegExp('(^|.*\/)' + filename + '([?].*)?$', 'i');
            var match = scripts[i].src.match(regex);
            if (match) {
                dirname = match[1];
                break;
            }
        }
        return dirname;
    };
    // 型を取得する
    Utils_.prototype.type = function (data) {
        return Object.prototype.toString.call(data).slice(8, -1).toLowerCase();
    };
    // 指定した型かどうかを判別する
    Utils_.prototype.is = function (type, data) {
        return this.type(data) === type;
    };
    // nullかどうかを判定する
    Utils_.prototype.isNull = function (data) {
        return this.is('null', data);
    };
    // 未定義かどうかを判定する
    Utils_.prototype.isUndefined = function (data) {
        return this.is('undefined', data);
    };
    // 文字列かどうかを判定する
    Utils_.prototype.isString = function (data) {
        return this.is('string', data);
    };
    // 数値かどうかを判定する
    Utils_.prototype.isNumber = function (data) {
        return this.is('number', data);
    };
    // 真偽値かどうかを判定する
    Utils_.prototype.isBoolean = function (data) {
        return this.is('boolean', data);
    };
    // 配列かどうかを判定する
    Utils_.prototype.isArray = function (data) {
        return this.is('array', data);
    };
    // オブジェクトかどうかを判定する
    Utils_.prototype.isObject = function (data) {
        return this.is('object', data);
    };
    // 関数かどうかを判定する
    Utils_.prototype.isFunction = function (data) {
        return this.is('function', data);
    };
    // 文字列をURLエンコードする
    Utils_.prototype.urlEncode = function (str) {
        // encodeURIComponentは -_!~*.()a' をエンコードしないので、別途変換する
        // エンコードが必要な !*()' のみ
        // %20 (半角スペース)は + に変換する
        return encodeURIComponent('' + str).replace(/!/g, '%21').replace(/'/g, '%27').replace(/\(/g, '%28').replace(/\)/g, '%29').replace(/\*/g, '%2A').replace(/%20/g, '+');
    };
    // 文字列をURLデコードする
    Utils_.prototype.urlDecode = function (str) {
        // + は %20 (半角スペース)に変換する
        return decodeURIComponent(('' + str).replace(/\+/g, '%20'));
    }
    // URLパラメーターに使える値かどうかを判定する
    Utils_.prototype.isCanQuery = function (value) {
        var type = this.type(value);
        return 'string' === type || 'number' === type || 'boolean' === type;
    };
    // Booleanなら1/0に変換する
    Utils_.prototype.replaceBoolean = function (value) {
        if(true === value) {
            value = 1;
        } else if (false === value) {
            value = 0;
        }
        return value;
    };
    // 配列からURLパラメーターを作成する
    Utils_.prototype.query = function (data) {
        if (this.isArray(data) || this.isObject(data)) {
            var params = [];
            for (var i in data) {
                if (this.isArray(data[i]) || this.isObject(data[i])) {
                    params.push(this.queryArray(i, data[i]));
                } else if (this.isCanQuery(data[i])) {
                    params.push(this.urlEncode(i) + '=' + this.urlEncode(this.replaceBoolean(data[i])));
                }
            }
            return params.join('&');
        } else {
            throw new Error('Parameter 1 expected to be Array or Object.');
        }
    };
    // 配列からURLパラメーターを作成する(値が配列時)
    Utils_.prototype.queryArray = function (key, data) {
        var params = [];
        for (var i in data) {
            if (this.isArray(data[i]) || this.isObject(data[i])) {
                params.push(this.queryArray(key + '[' + i + ']', data[i]));
            } else if (this.isCanQuery(data[i])) {
                params.push(this.urlEncode(key + '[' + i + ']') + '=' + this.urlEncode(this.replaceBoolean(data[i])));
            }
        }
        return params.join('&');
    };
    // URLパラメーターから配列を作成する
    Utils_.prototype.queryToArray = function () {
        var arg = new Object;
        if (location.search.length > 0) {
            var arrPair = location.search.substring(1).split('&');
            for (var i = 0; i < arrPair.length; i++) {
                var pair = arrPair[i].split('=');
                arg[this.urlDecode(pair[0])] = this.urlDecode(pair[1]);
            }
        }
        return arg;
    };

    sunat.utils = new Utils_();
})();
