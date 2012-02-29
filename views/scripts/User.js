var UserClass = Class.extend({
    
    loggedIn: false,
    authenticationMethod: null,
    
    loggedInWithDatabase: function() {
        if(this.loggedIn && this.authenticationMethod == 'database') {
            return true;
        }
        else {
            return false;
        }
    },
    
    showLoginDialog: function(options) {
        var ajax = null;
        if(options && options.ajax) {
            ajax = options.ajax;
            delete options.ajax;
        }
        options = $.extend(true, {
            'class': 'formDialog',
            'modalOverlayClass': 'formModalOverlay',
            'header': '',
            ajax: {
                'url': '/api/user/getLoginDialog/outputType:raw/',
                'onSuccess': function() {
                    if(ajax && ajax.onSuccess) {
                        ajax.onSuccess();
                    }
                    $('#loginIdentifier').focus();
                }
            }
        }, options || {});
        
        this.loginDialog = new Dialog(options);
    },
    
    showRegisterDialog: function(options) {
        var ajax = null;
        if(options && options.ajax) {
            ajax = options.ajax;
            delete options.ajax;
        }
        options = $.extend(true, {
            'class': 'formDialog',
            'modalOverlayClass': 'formModalOverlay',
            'header': '',
            ajax: {
                'url': '/api/user/getRegisterDialog/outputType:raw/',
                'onSuccess': function() {
                    if(ajax && ajax.onSuccess) {
                        ajax.onSuccess();
                    }
                    $('#registerUsername').focus();
                }
            }
        }, options || {});
        
        this.registerDialog = new Dialog(options);
    }
    
});
var User = new UserClass();