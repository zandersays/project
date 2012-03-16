var AddressHandler = Class.extend({
    initialize: function (options) {
        // Update the options object
        this.options = $.extend(true, {
            'autoUpdate': true,
            'crawlable': false,
            'history': true,
            'strict': true,
            'wrap': false
        }, options || {});

        this.browser = $.browser;
        this.version = parseFloat(this.browser.version),
        this.document = window.document
        this.version = parseFloat($.browser.version),
        this.msie = !$.support.opacity,
        this.webkit = $.browser.webkit || $.browser.safari
        this.history = window.history;
        this.location = window.location;
        this.agent = navigator.userAgent,
        this.state = null;
        this.value = this.href();
        this.updating = false;
        this.regEx = /\/{2,9}/g;
        this.silent = false;
        
        if (this.msie) {
            this.version = parseFloat(this.agent.substr(this.agent.indexOf('MSIE') + 4));
            if (this.document.documentMode && this.document.documentMode != this.version) {
                this.version = this.document.documentMode != 8 ? 7 : 8;
            }
            var pc = this.document.onpropertychange;
            this.document.onpropertychange = function() {
                if (pc) {
                    pc.call(this.document);
                }
                if (this.document.title != this.title && this.document.title.indexOf('#' + this.href()) != -1) {
                    this.document.title = this.title;
                }
            };
        }
        
        if (this.history.navigationMode) {
            this.history.navigationMode = 'compatible';
        }
        if (this.document.readyState == 'complete') {
            var self = this;
            var interval = setInterval(function() {
                self.load();
                clearInterval(interval);
            }, 50);
        } else {
            this.options();
            $(this.load);
        }
        $(window).bind('popstate', this.popstate).bind('unload', function() {
            if (this.window.removeEventListener) {
                this.window.removeEventListener('hashchange', function() {
                    if (!self.silent) {
                        var hash = self.href(),
                        diff = self.value != hash;
                        if (diff) {
                            if (self.msie && self.version < 7) {
                                self.location.reload();
                            } else {
                                if (self.msie && !self.hashchange && self.options.history) {
                                    setTimer(self.html, 50);
                                }
                                self.value = hash;
                                self.update(FALSE);
                            }
                        }
                    }
                }, FALSE);
            } else if (this.window.detachEvent) {
                this.window.detachEvent('onhashchange', 'hashchange');
            }
        });        
    },
    trigger: function(name) {
        $($.address).trigger(
            $.extend($.Event(name), 
                (function() {
                    var parameters = {},
                    parameterNames = $.address.parameterNames();
                    for (var i = 0, l = parameterNames.length; i < l; i++) {
                        parameters[parameterNames[i]] = $.address.parameter(parameterNames[i]);
                    }
                    return {
                        value: $.address.value(),
                        path: $.address.path(),
                        pathNames: $.address.pathNames(),
                        parameterNames: parameterNames,
                        parameters: parameters,
                        queryString: $.address.queryString()
                    };
                }).call($.address)
                )
            );
    },
    bind: function(value, data, fn) {
        $().bind.apply($($.address), Array.prototype.slice.call(arguments));
        return $.address;
    },
    supportsState: function() {
        return (_h.pushState && this.options.state !== UNDEFINED);
    },
    hrefState: function() {
        return ('/' + this.location.pathname.replace(new RegExp(this.options.state), '') + 
            this.location.search + (_hrefHash() ? '#' + _hrefHash() : '')).replace(_re, '/');
    },
    hrefHash: function() {
        var index = this.location.href.indexOf('#');
        return index != -1 ? _crawl(this.location.href.substr(index + 1), FALSE) : '';
    },
    href: function() {
        return _supportsState() ? _hrefState() : _hrefHash();
    },
    window: function() {
        try {
            return top.document !== UNDEFINED ? top : window;
        } catch (e) { 
            return window;
        }
    },
    js: function() {
        return 'javascript';
    },
    strict: function(value) {
        value = value.toString();
        return (this.options.strict && value.substr(0, 1) != '/' ? '/' : '') + value;
    },
    crawl: function(value, direction) {
        if (this.options.crawlable && direction) {
            return (value !== '' ? '!' : '') + value;
        }
        return value.replace(/^\!/, '');
    },
    cssint: function(el, value) {
        return parseInt(el.css(value), 10);
    },
    update: function(internal) {
        _trigger(CHANGE);
        _trigger(internal ? INTERNAL_CHANGE : EXTERNAL_CHANGE);
        setTimer(function() {
            if (this.options.tracker !== 'null' && this.options.tracker !== null) {
                var fn = $.isFunction(this.options.tracker) ? this.options.tracker : this.window[this.options.tracker],
                value = (this.location.pathname + this.location.search + 
                    ($.address && !_supportsState() ? $.address.value() : ''))
                .replace(/\/\//, '/').replace(/^\/$/, '');
                if ($.isFunction(fn)) {
                    fn(value);
                } else if ($.isFunction(this.window.urchinTracker)) {
                    this.window.urchinTracker(value);
                } else if (this.window.pageTracker !== UNDEFINED && $.isFunction(this.window.pageTracker._trackPageview)) {
                    this.window.pageTracker._trackPageview(value);
                } else if (this.window._gaq !== UNDEFINED && $.isFunction(this.window._gaq.push)) {
                    this.window._gaq.push(['_trackPageview', decodeURI(value)]);
                }
            }
        }, 10);
    },
    track: function() {
        if (this.options.tracker !== 'null' && this.options.tracker !== null) {
            var fn = $.isFunction(this.options.tracker) ? this.options.tracker : this.window[this.options.tracker],
            value = (this.location.pathname + this.location.search + 
                ($.address && !_supportsState() ? $.address.value() : ''))
            .replace(/\/\//, '/').replace(/^\/$/, '');
            if ($.isFunction(fn)) {
                fn(value);
            } else if ($.isFunction(this.window.urchinTracker)) {
                this.window.urchinTracker(value);
            } else if (this.window.pageTracker !== UNDEFINED && $.isFunction(this.window.pageTracker._trackPageview)) {
                this.window.pageTracker._trackPageview(value);
            } else if (this.window._gaq !== UNDEFINED && $.isFunction(this.window._gaq.push)) {
                this.window._gaq.push(['_trackPageview', decodeURI(value)]);
            }
        }
    },
    html: function() {
        var src = _js() + ':' + FALSE + ';document.open();document.writeln(\'<html><head><title>' + 
        _d.title.replace(/\'/g, '\\\'') + '</title><script>var ' + ID + ' = "' + _href() + 
        (_d.domain != this.location.hostname ? '";document.domain="' + _d.domain : '') + 
        '";</' + 'script></head></html>\');document.close();';
        if (_version < 7) {
            _frame.src = src;
        } else {
            _frame.contentWindow.location.replace(src);
        }
    },
    options: function() {
        if (_url && _qi != -1) {
            var i, param, params = _url.substr(_qi + 1).split('&');
            for (i = 0; i < params.length; i++) {
                param = params[i].split('=');
                if (/^(autoUpdate|crawlable|history|strict|wrap)$/.test(param[0])) {
                    this.options[param[0]] = (isNaN(param[1]) ? /^(true|yes)$/i.test(param[1]) : (parseInt(param[1], 10) !== 0));
                }
                if (/^(state|tracker)$/.test(param[0])) {
                    this.options[param[0]] = param[1];
                }
            }
            _url = null;
        }
        _value = _href();
    },
    load: function() {
        if (!this.locationoaded) {
            this.locationoaded = TRUE;
            _options();
            var complete = function() {
                _enable.call(this);
                _unescape.call(this);
            },
            body = $('body').ajaxComplete(complete);
            complete();
            if (this.options.wrap) {
                var wrap = $('body > *')
                .wrapAll('<div style="padding:' + 
                    (_cssint(body, 'marginTop') + _cssint(body, 'paddingTop')) + 'px ' + 
                    (_cssint(body, 'marginRight') + _cssint(body, 'paddingRight')) + 'px ' + 
                    (_cssint(body, 'marginBottom') + _cssint(body, 'paddingBottom')) + 'px ' + 
                    (_cssint(body, 'marginLeft') + _cssint(body, 'paddingLeft')) + 'px;" />')
                .parent()
                .wrap('<div id="' + ID + '" style="height:100%;overflow:auto;position:relative;' + 
                    (_webkit && !window.statusbar.visible ? 'resize:both;' : '') + '" />');
                $('html, body')
                .css({
                    height: '100%',
                    margin: 0,
                    padding: 0,
                    overflow: 'hidden'
                });
                if (_webkit) {
                    $('<style type="text/css" />')
                    .appendTo('head')
                    .text('#' + ID + '::-webkit-resizer { background-color: #fff; }');
                }
            }
            if (_msie && !this.hashchange) {
                var frameset = _d.getElementsByTagName('frameset')[0];
                _frame = _d.createElement((frameset ? '' : 'i') + 'frame');
                _frame.src = _js() + ':' + FALSE;
                if (frameset) {
                    frameset.insertAdjacentElement('beforeEnd', _frame);
                    frameset[frameset.cols ? 'cols' : 'rows'] += ',0';
                    _frame.noResize = TRUE;
                    _frame.frameBorder = _frame.frameSpacing = 0;
                } else {
                    _frame.style.display = 'none';
                    _frame.style.width = _frame.style.height = 0;
                    _frame.tabIndex = -1;
                    _d.body.insertAdjacentElement('afterBegin', _frame);
                }
                _st(function() {
                    $(_frame).bind('load', function() {
                        var win = _frame.contentWindow;
                        _value = win[ID] !== UNDEFINED ? win[ID] : '';
                        if (_value != _href()) {
                            _update(FALSE);
                            this.location.hash = _crawl(_value, TRUE);
                        }
                    });
                    if (_frame.contentWindow[ID] === UNDEFINED) {
                        _html();
                    }
                }, 50);
            }

            _st(function() {
                _trigger('init');
                _update(FALSE);
            }, 1);

            if (!_supportsState()) {
                if (this.hashchange) {
                    if (this.window.addEventListener) {
                        this.window.addEventListener(HASH_CHANGE, this.locationisten, FALSE);
                    } else if (this.window.attachEvent) {
                        this.window.attachEvent('on' + HASH_CHANGE, this.locationisten);
                    }
                } else {
                    setInterval(this.locationisten, 50);
                }
            }
        }
    },
    enable: function() {
        var el, 
        elements = $('a'), 
        length = elements.size(),
        delay = 1,
        index = -1,
        sel = '[rel*="address:"]',
        fn = function() {
            if (++index != length) {
                el = $(elements.get(index));
                if (el.is(sel)) {
                    el.address(sel);
                }
                _st(fn, delay);
            }
        };
        _st(fn, delay);
    },
    popstate: function() {
        if (this.value != _href()) {
            this.value = _href();
            _update(FALSE);
        }
    },
    unescape: function() {
        if (this.options.crawlable) {
            var base = this.location.pathname.replace(/\/$/, ''),
            fragment = '_escaped_fragment_';
            if ($('body').html().indexOf(fragment) != -1) {
                $('a[href]:not([href^=http]), a[href*="' + document.domain + '"]').each(function() {
                    var href = $(this).attr('href').replace(/^http:/, '').replace(new RegExp(base + '/?$'), '');
                    if (href === '' || href.indexOf(fragment) != -1) {
                        $(this).attr('href', '#' + encodeURI(decodeURIComponent(href.replace(new RegExp('/(.*)\\?' + 
                            fragment + '=(.*)$'))), '!$2'));
                    }
                });
            }
        }
    }

    
    
});
