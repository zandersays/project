// Array function
Array.prototype.clean = function(deleteValue) {
    for (var i = 0; i < this.length; i++) {
        if (this[i] === deleteValue) {         
            this.splice(i, 1);
            i--;
        }
    }
    return this;
};

if (!Array.prototype.indexOf) {
    Array.prototype.indexOf = function(elt /*, from*/)
    {
        var len = this.length >>> 0;

        var from = Number(arguments[1]) || 0;
        from = (from < 0)
        ? Math.ceil(from)
        : Math.floor(from);
        if (from < 0)
            from += len;

        for (; from < len; from++)
        {
            if (from in this &&
                this[from] === elt)
                return from;
        }
        return -1;
    };
}


// String functions
String.prototype.startsWith = function(string) {
    return (this.indexOf(string) === 0);
};

String.prototype.empty = function() {
    //console.log($.trim(this.valueOf()));
    if($.trim(this.valueOf()) === '') {
        return true;
    }
    else {
        return false;
    }
};

String.prototype.camelCaseToDashes = function () {
    return this.replace(/([A-Z])/g, function($1){return "-"+$1.toLowerCase();});
};

String.prototype.dashesToCamelCase = function() {
	return this.replace(/(\-[a-z])/g, function($1){return $1.toUpperCase().replace('-','');});
};

String.prototype.replaceArray = function(find, replace) {
    var replaceString = this;
    var regex; 
    for(var i = 0; i < find.length; i++) {
        regex = new RegExp(find[i], "gi");
        replaceString = replaceString.replace(regex, replace[i]);
    }
    return replaceString;
};

String.prototype.newLinesToParagraphTags = function(lineBreaks) {
    if(!lineBreaks) {
        lineBreaks = true;
    }
    var string = this;
    var newString = '';
    // It is conceivable that people might still want single line-breaks without breaking into a new paragraph.
    if(lineBreaks === true) {
        newString = '<p>'+string.replaceArray(['([\n]{2})', '([^>])\n([^<])'], ["</p>\n<p>", "$1<br />$2"])+'</p>';
    }            
    else {
        newString = '<p>'+string.replaceArray(["([\n]{2})", "([\r\n]{3,})", "([^>])\n([^<])"], ["</p>\n<p>", "</p>\n<p>", "$1<br />$2"])+'</p>';
    }

    return newString;
};
String.prototype.newLinesToBreakTags = function() {
    return this.replace('\n', '<br />');
};
// base64 String extensions
String.prototype.base64Encode = function() {
    var output = "";
    var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
    var i = 0;
    var _keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
        
    var input = this.utf8Encode();

    while (i < input.length) {

        chr1 = input.charCodeAt(i++);
        chr2 = input.charCodeAt(i++);
        chr3 = input.charCodeAt(i++);

        enc1 = chr1 >> 2;
        enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
        enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
        enc4 = chr3 & 63;

        if (isNaN(chr2)) {
            enc3 = enc4 = 64;
        } else if (isNaN(chr3)) {
            enc4 = 64;
        }
        output = output + _keyStr.charAt(enc1) + _keyStr.charAt(enc2) + _keyStr.charAt(enc3) + _keyStr.charAt(enc4);
    }
    return output;  
};
    
String.prototype.base64Decode = function() {
    var output = "";
    var chr1, chr2, chr3;
    var enc1, enc2, enc3, enc4;
    var i = 0;
    var keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";

    var input = this.replace(/[^A-Za-z0-9\+\/\=]/g, "");
    
    while (i < input.length) {
           
        enc1 = keyStr.indexOf(input.charAt(i++));
        enc2 = keyStr.indexOf(input.charAt(i++));
        enc3 = keyStr.indexOf(input.charAt(i++));
        enc4 = keyStr.indexOf(input.charAt(i++));
        //console.log(input, enc1, enc2, enc3, enc4);

        chr1 = (enc1 << 2) | (enc2 >> 4);
        chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
        chr3 = ((enc3 & 3) << 6) | enc4;
        //console.log(chr1, chr2, chr3);
        output = output + String.fromCharCode(chr1);

        if (enc3 !== 64) {
            output = output + String.fromCharCode(chr2);
        }
        if (enc4 !== 64) {
            output = output + String.fromCharCode(chr3);
        }
        
    }
    
    output = output.utf8Decode();

    return output;

};
    
String.prototype.utf8Encode = function () {
    var string = this.replace(/\r\n/g,"\n");
    var utftext = "";

    for (var n = 0; n < string.length; n++) {
        var c = this.charCodeAt(n);

        if (c < 128) {
            utftext += String.fromCharCode(c);
        }
        else if((c > 127) && (c < 2048)) {
            utftext += String.fromCharCode((c >> 6) | 192);
            utftext += String.fromCharCode((c & 63) | 128);
        }
        else {
            utftext += String.fromCharCode((c >> 12) | 224);
            utftext += String.fromCharCode(((c >> 6) & 63) | 128);
            utftext += String.fromCharCode((c & 63) | 128);
        }
    }
    return utftext;
};

String.prototype.utf8Decode = function () {
    var utftext = this;
    var string = "";
    var i = 0;
    var c2 = 0, c1 = c2, c = c1, c3;
    

    while ( i < utftext.length ) {

            c = utftext.charCodeAt(i);

            if (c < 128) {
                    string += String.fromCharCode(c);
                    i++;
            }
            else if((c > 191) && (c < 224)) {
                    c2 = utftext.charCodeAt(i+1);
                    string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
                    i += 2;
            }
            else {
                    c2 = utftext.charCodeAt(i+1);
                    c3 = utftext.charCodeAt(i+2);
                    string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                    i += 3;
            }

    }

    return string;
};


var UtilityClass = Class.extend({
    
    eventIsSupported: function(eventName, element) {
        element = element || document.createElement('div');
        eventName = 'on' + eventName;
        
        // When using `setAttribute`, IE skips "unload", WebKit skips "unload" and "resize", whereas `in` "catches" those
        var isSupported = eventName in element;
        if(!isSupported) {
            // If it has no `setAttribute` (i.e. doesn't implement Node interface), try generic element
            if(!element.setAttribute) {
                element = document.createElement('div');
            }
            if(element.setAttribute && element.removeAttribute) {
                element.setAttribute(eventName, '');
                isSupported = typeof element[eventName] === 'function';
                
                // If property was created, "remove it" (by setting value to `undefined`)
                if(typeof element[eventName] !== undefined) {
                    element[eventName] = undefined;
                }
                element.removeAttribute(eventName);
            }
        }
        
        element = null;
        return isSupported;
    },

    disableSelection: function(target){
        if (typeof target.onselectstart!=="undefined") { //IE route
            target.onselectstart=function(){
                return false;
            };
        }
        else if (typeof target.style.MozUserSelect!=="undefined"){ //Firefox route
            target.style.MozUserSelect="none";
        }
        else { //All other route (ie: Opera)
            target.onmousedown=function(){
                return false;
            };
        }
    },

    set: function() {
        var a = arguments;
        var l = a.length;
        var i = 0;
        if(l === 0) {
            throw new Error('Empty isSet.');
        }
        while(i !== l) {
            if(typeof(a[i]) === 'undefined' || a[i] === null) {
                return false;
            }
            else {
                i++;
            }
        }
        return true;
    },

    empty: function(mixedVariable) {
        var key;
        if(mixedVariable === "" 
            || mixedVariable == 0 
            || mixedVariable === "0" 
            || mixedVariable === null 
            || mixedVariable === false 
            || mixedVariable === undefined ) {
            return true;
        }
        if(typeof mixedVariable === 'object') {
            for(key in mixedVariable) {
                if(typeof mixedVariable[key] !== 'function') {
                    return false;
                }
            }
            return true;
        }
        return false;
    },

    getExtraWidth: function(element) {
        var totalWidth = 0;
        element = $(element);
        
        totalWidth += parseInt(element.css("padding-left"), 10) + parseInt(element.css("padding-right"), 10); //Total Padding Width
        totalWidth += parseInt(element.css("margin-left"), 10) + parseInt(element.css("margin-right"), 10); //Total Margin Width
        totalWidth += parseInt(element.css("borderLeftWidth"), 10) + parseInt(element.css("borderRightWidth"), 10); //Total Border Width
        return totalWidth;
    }

});
var Utility = new UtilityClass();