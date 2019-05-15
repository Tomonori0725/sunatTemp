;window.sunat = window.sunat || {};

(function () {
    'use strict';

    var Spinner_ = function (containerSelector) {
        this.containerSelector_ = containerSelector;
        this.container_ = null;
        this.timer_ = {
            'delay': null,
            'repeat': null
        };
        this.current_ = 0;
        this.initialize_();
    };
    Spinner_.prototype.const_ = {
        'parts': ['field', 'up', 'down'],
        'params': ['min', 'max', 'step'],
        'timer': {
            'delay': 500,
            'repeat': 50
        }
    };
    Spinner_.prototype.initialize_ = function () {
        this.bind_();
    };
    Spinner_.prototype.bind_ = function () {
        var self_ = this;

        sunat.utils.addEventListener(document.body, 'touchstart', function (e) {
            if (!e.target.classList.contains('spinner-up') && !e.target.classList.contains('spinner-down')) {
                return;
            }
            self_.onStartEvent_(e);
        });
        sunat.utils.addEventListener(document.body, 'mousedown', function (e) {
            if (!e.target.classList.contains('spinner-up') && !e.target.classList.contains('spinner-down')) {
                return;
            }
            self_.onStartEvent_(e);
        });
    };
    Spinner_.prototype.onStartEvent_ = function (e) {
        var self_ = this,
            direction = 1;

        e.stopPropagation();
        e.preventDefault();

        self_.container_ = e.target.closest(self_.containerSelector_);
        if (!self_.container_.hasOwnProperty('spinnerObject')) {
            self_.bindSetting_();
            self_.bindEvent();
        }

        if (e.target.classList.contains('spinner-down')) {
            direction = -1;
        }
        self_.start_(direction);
    };
    Spinner_.prototype.bindSetting_ = function () {
        var i, temp,
            self_ = this;

        if (!self_.container_.hasOwnProperty('spinnerObject')) {
            self_.container_.spinnerObject = {
                'elements': {},
                'params': {}
            };
            for (i in self_.const_.parts) {
                temp = self_.container_.getElementsByClassName('spinner-' + self_.const_.parts[i]);
                if (0 !== temp.length) {
                    self_.container_.spinnerObject.elements[self_.const_.parts[i]] = temp[0];
                }
            }
            for (i in self_.const_.params) {
                temp = parseInt(self_.container_.getAttribute('data-num-' + self_.const_.params[i]));
                if (!isNaN(temp)) {
                    self_.container_.spinnerObject.params[self_.const_.params[i]] = temp;
                }
            }
        }
    };
    Spinner_.prototype.bindEvent = function () {
        var self_ = this;
        sunat.utils.addEventListener(self_.container_.spinnerObject.elements.up, 'touchend', self_.stop_());
        sunat.utils.addEventListener(self_.container_.spinnerObject.elements.up, 'mouseup', self_.stop_());
        sunat.utils.addEventListener(self_.container_.spinnerObject.elements.down, 'touchend', self_.stop_());
        sunat.utils.addEventListener(self_.container_.spinnerObject.elements.down, 'mouseup', self_.stop_());
    };
    Spinner_.prototype.start_ = function (direction) {
        var self_ = this;
        self_.first_(direction);
        self_.timer_.delay = setTimeout(function () {
            self_.timer_.repeat = setInterval(self_.spin_(direction), self_.const_.timer.repeat);
        }, self_.const_.timer.delay);
    };
    Spinner_.prototype.stop_ = function () {
        var self_ = this;
        return function (e) {
            e.stopPropagation();
            e.preventDefault();
            if (self_.timer_.delay) {
                clearTimeout(self_.timer_.delay);
                self_.timer_.delay = null;
            }
            if (self_.timer_.repeat) {
                clearInterval(self_.timer_.repeat);
                self_.timer_.repeat = null;
            }
            self_.container_ = null;
        };
    };
    Spinner_.prototype.first_ = function (direction) {
        var self_ = this;
        self_.current_ = parseInt(self_.container_.spinnerObject.elements.field.value);
        if (isNaN(self_.current_)) {
            self_.current_ = 0;
            if (false !== self_.container_.spinnerObject.params.min
                && self_.container_.spinnerObject.params.min > self_.current_
            ) {
                self_.current_ = self_.container_.spinnerObject.params.min;
            }
        }
        self_.current_ = self_.current_ + (self_.container_.spinnerObject.params.step * direction);
        self_.threshold_(direction);
        self_.container_.spinnerObject.elements.field.value = self_.current_;
    };
    Spinner_.prototype.spin_ = function (direction) {
        var self_ = this;
        return function (e) {
            self_.current_ = parseFloat(self_.current_) + (self_.container_.spinnerObject.params.step * direction);
            self_.threshold_(direction);
            self_.container_.spinnerObject.elements.field.value = self_.current_;
        };
    };
    Spinner_.prototype.threshold_ = function (direction) {
        var self_ = this;
        if (0 < direction) {
            if (false !== self_.container_.spinnerObject.params.max
                && self_.container_.spinnerObject.params.max < self_.current_
            ) {
                self_.current_ = self_.container_.spinnerObject.params.max;
            }
        } else {
            if (false !== self_.container_.spinnerObject.params.min
                && self_.container_.spinnerObject.params.min > self_.current_
            ) {
                self_.current_ = self_.container_.spinnerObject.params.min;
            }
        }
    };

    sunat.Spinner = Spinner_;
})();
document.addEventListener('DOMContentLoaded', function (e) {
    new sunat.Spinner('.spinner-container');
}, false);
