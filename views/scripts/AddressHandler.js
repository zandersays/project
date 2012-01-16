var AddressHandler = Class.extend({
    initialize: function(options) {
        // Update the options object
        this.options = $.extend(true, {
            'autoUpdate': true, 
            'crawlable': false,
            'history': true, 
            'strict': true,
            'wrap': false
        }, options || {});
        
        this.browser = $.browser;
        this.document = window.document
        this.version = parseFloat($.browser.version),
        this.msie = !$.support.opacity,
        this.webkit = $.browser.webkit || $.browser.safari
        this.history = window.history;
        this.location = window.location;
        this.state = null;
        this.value = this.href();
        this.updating = false;
        this.regEx = /\/{2,9}/g;
        this.silent = false;
    },
    crawl : function(value, direction) {
        if (this.options.crawlable && direction) {
            return (value !== '' ? '!' : '') + value;
        }
        return value.replace(/^\!/, '');
    },
    hrefState: function() {
        return ('/' + this.location.pathname.replace(new RegExp(this.state), '') + 
            this.location.search + (this.hrefHash() ? '#' + this.hrefHash() : '')).replace(this.regEx, '/');
    },
    hrefHash: function() {
        var index = this.location.href.indexOf('#');
        return index != -1 ? this.crawl(this.location.href.substr(index + 1), false) : '';
    },
    href: function() {
        return this.supportsState() ? this.hrefState() : this.hrefHash();
    },
    html: function(){
        var src = 'javascript:' + false + ';document.open();document.writeln(\'<html><head><title>' + 
        this.document.title.replace(/\'/g, '\\\'') + '</title><script>var ' + ID + ' = "' + this.href() + 
        (this.document.domain != this.location.hostname ? '";document.domain="' + this.document.domain : '') + 
        '";</' + 'script></head></html>\');document.close();';
        if (this.version < 7) {
            this.frame.src = src;
        } else {
            this.frame.contentWindow.location.replace(src);
        }  
    },    
    supportsState : function() {
        return (this.history.pushState && this.state !== null);
    },
    value: function(value) {
        if (value !== undefined) {
            value = this.strict(value);
            if (value == '/') {
                value = '';
            }
            if (this.value == value && !this.updating) {
                return;
            }
            //_justset = TRUE;
            this.value = value;
            if (this.supportsState()) {
                this.history[this.options.history ? 'pushState' : 'replaceState']({}, '', 
                    this.state.replace(/\/$/, '') + (this.value === '' ? '/' : this.value));
            } else {
                this.silent = TRUE;
                if (this.webkit) {
                    if (this.options.history) {
                        this.location.hash = '#' + this.crawl(this.value, true);
                    } else {
                        this.location.replace('#' + this.crawl(this.value, true));
                    }
                } else if (this.value != this.href()) {
                    if (this.options.history) {
                        this.location.hash = '#' + this.crawl(this.value, true);
                    } else {
                        this.location.replace('#' + this.crawl(this.value, true));
                    }
                }
                if ((this.msie && !this.hashchange) && this.options.history) {
                    setTimeout(this.html, 50);
                }
                if (this.webkit) {
                    var self = this;
                    setTimeout(function(){
                        self.silent = false;
                    }, 1);
                } else {
                    this.silent = false;
                }
            }
        }
        return this.strict(this.value);
    }

    
    
});