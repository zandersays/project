var SecurityClass = Class.extend({
    // Hex output format. 0 - lowercase; 1 - uppercase
    hexCase: 0,

    // Base-64 pad character. "=" for strict RFC compliance
    base64Pad: "",

    md5: function(string) {
        return this.hexMd5(string);
    },

    hexMd5: function(s) {
        return this.stringToHex(this.stringMd5(this.stringToStringUtf8(s)));
    },

    base64Md5: function(s) {
        return this.stringToBase64(this.stringMd5(this.stringToStringUtf8(s)));
    },

    anyMd5: function(s, e) {
        return this.stringToAny(this.stringMd5(this.stringToStringUtf8(s)), e);
    },

    hexHmacMd5: function(k, d) {
        return this.stringToHex(this.stringHmacMd5(this.stringToStringUtf8(k), this.stringToStringUtf8(d)));
    },

    base64HmacMd5: function(k, d) {
        return this.stringToBase64(this.stringHmacMd5(this.stringToStringUtf8(k), this.stringToStringUtf8(d)));
    },

    hmacMd5: function(k, d, e) {
        return this.stringToAny(this.stringHmacMd5(this.stringToStringUtf8(k), this.stringToStringUtf8(d)), e);
    },

    /*
     * Perform a simple self-test to see if the VM is working
     */
    md5VmTest: function() {
      return this.hexMd5("abc").toLowerCase() == "900150983cd24fb0d6963f7d28e17f72";
    },

    /*
     * Calculate the MD5 of a raw string
     */
    stringMd5: function(s) {
      return this.littleEndianArrayToString(this.md5LittleEndianArray(this.rawStringToLittleEndianArray(s), s.length * 8));
    },

    /*
     * Calculate the HMAC-MD5, of a key and some data (raw strings)
     */
    stringHmacMd5: function(key, data) {
      var bkey = this.rawStringToLittleEndianArray(key);
      if(bkey.length > 16) bkey = this.md5LittleEndianArray(bkey, key.length * 8);

      var ipad = Array(16), opad = Array(16);
      for(var i = 0; i < 16; i++)
      {
        ipad[i] = bkey[i] ^ 0x36363636;
        opad[i] = bkey[i] ^ 0x5C5C5C5C;
      }

      var hash = this.md5LittleEndianArray(ipad.concat(this.rawStringToLittleEndianArray(data)), 512 + data.length * 8);
      return this.littleEndianArrayToString(this.md5LittleEndianArray(opad.concat(hash), 512 + 128));
    },

    /*
     * Convert a raw string to an array of little-endian words
     * Characters >255 have their high-byte silently ignored.
     */
    rawStringToLittleEndianArray: function(input) {
      var output = Array(input.length >> 2);
      for(var i = 0; i < output.length; i++)
        output[i] = 0;
      for(var i = 0; i < input.length * 8; i += 8)
        output[i>>5] |= (input.charCodeAt(i / 8) & 0xFF) << (i%32);
      return output;
    },

    /*
     * Convert an array of little-endian words to a string
     */
    littleEndianArrayToString: function(input) {
      var output = "";
      for(var i = 0; i < input.length * 32; i += 8)
        output += String.fromCharCode((input[i>>5] >>> (i % 32)) & 0xFF);
      return output;
    },

    /*
     * Calculate the MD5 of an array of little-endian words, and a bit length.
     */
    md5LittleEndianArray: function(x, len)
    {
      /* append padding */
      x[len >> 5] |= 0x80 << ((len) % 32);
      x[(((len + 64) >>> 9) << 4) + 14] = len;

      var a =  1732584193;
      var b = -271733879;
      var c = -1732584194;
      var d =  271733878;

      for(var i = 0; i < x.length; i += 16)
      {
        var olda = a;
        var oldb = b;
        var oldc = c;
        var oldd = d;

        a = this.md5Ff(a, b, c, d, x[i+ 0], 7 , -680876936);
        d = this.md5Ff(d, a, b, c, x[i+ 1], 12, -389564586);
        c = this.md5Ff(c, d, a, b, x[i+ 2], 17,  606105819);
        b = this.md5Ff(b, c, d, a, x[i+ 3], 22, -1044525330);
        a = this.md5Ff(a, b, c, d, x[i+ 4], 7 , -176418897);
        d = this.md5Ff(d, a, b, c, x[i+ 5], 12,  1200080426);
        c = this.md5Ff(c, d, a, b, x[i+ 6], 17, -1473231341);
        b = this.md5Ff(b, c, d, a, x[i+ 7], 22, -45705983);
        a = this.md5Ff(a, b, c, d, x[i+ 8], 7 ,  1770035416);
        d = this.md5Ff(d, a, b, c, x[i+ 9], 12, -1958414417);
        c = this.md5Ff(c, d, a, b, x[i+10], 17, -42063);
        b = this.md5Ff(b, c, d, a, x[i+11], 22, -1990404162);
        a = this.md5Ff(a, b, c, d, x[i+12], 7 ,  1804603682);
        d = this.md5Ff(d, a, b, c, x[i+13], 12, -40341101);
        c = this.md5Ff(c, d, a, b, x[i+14], 17, -1502002290);
        b = this.md5Ff(b, c, d, a, x[i+15], 22,  1236535329);

        a = this.md5Gg(a, b, c, d, x[i+ 1], 5 , -165796510);
        d = this.md5Gg(d, a, b, c, x[i+ 6], 9 , -1069501632);
        c = this.md5Gg(c, d, a, b, x[i+11], 14,  643717713);
        b = this.md5Gg(b, c, d, a, x[i+ 0], 20, -373897302);
        a = this.md5Gg(a, b, c, d, x[i+ 5], 5 , -701558691);
        d = this.md5Gg(d, a, b, c, x[i+10], 9 ,  38016083);
        c = this.md5Gg(c, d, a, b, x[i+15], 14, -660478335);
        b = this.md5Gg(b, c, d, a, x[i+ 4], 20, -405537848);
        a = this.md5Gg(a, b, c, d, x[i+ 9], 5 ,  568446438);
        d = this.md5Gg(d, a, b, c, x[i+14], 9 , -1019803690);
        c = this.md5Gg(c, d, a, b, x[i+ 3], 14, -187363961);
        b = this.md5Gg(b, c, d, a, x[i+ 8], 20,  1163531501);
        a = this.md5Gg(a, b, c, d, x[i+13], 5 , -1444681467);
        d = this.md5Gg(d, a, b, c, x[i+ 2], 9 , -51403784);
        c = this.md5Gg(c, d, a, b, x[i+ 7], 14,  1735328473);
        b = this.md5Gg(b, c, d, a, x[i+12], 20, -1926607734);

        a = this.md5Hh(a, b, c, d, x[i+ 5], 4 , -378558);
        d = this.md5Hh(d, a, b, c, x[i+ 8], 11, -2022574463);
        c = this.md5Hh(c, d, a, b, x[i+11], 16,  1839030562);
        b = this.md5Hh(b, c, d, a, x[i+14], 23, -35309556);
        a = this.md5Hh(a, b, c, d, x[i+ 1], 4 , -1530992060);
        d = this.md5Hh(d, a, b, c, x[i+ 4], 11,  1272893353);
        c = this.md5Hh(c, d, a, b, x[i+ 7], 16, -155497632);
        b = this.md5Hh(b, c, d, a, x[i+10], 23, -1094730640);
        a = this.md5Hh(a, b, c, d, x[i+13], 4 ,  681279174);
        d = this.md5Hh(d, a, b, c, x[i+ 0], 11, -358537222);
        c = this.md5Hh(c, d, a, b, x[i+ 3], 16, -722521979);
        b = this.md5Hh(b, c, d, a, x[i+ 6], 23,  76029189);
        a = this.md5Hh(a, b, c, d, x[i+ 9], 4 , -640364487);
        d = this.md5Hh(d, a, b, c, x[i+12], 11, -421815835);
        c = this.md5Hh(c, d, a, b, x[i+15], 16,  530742520);
        b = this.md5Hh(b, c, d, a, x[i+ 2], 23, -995338651);

        a = this.md5Ii(a, b, c, d, x[i+ 0], 6 , -198630844);
        d = this.md5Ii(d, a, b, c, x[i+ 7], 10,  1126891415);
        c = this.md5Ii(c, d, a, b, x[i+14], 15, -1416354905);
        b = this.md5Ii(b, c, d, a, x[i+ 5], 21, -57434055);
        a = this.md5Ii(a, b, c, d, x[i+12], 6 ,  1700485571);
        d = this.md5Ii(d, a, b, c, x[i+ 3], 10, -1894986606);
        c = this.md5Ii(c, d, a, b, x[i+10], 15, -1051523);
        b = this.md5Ii(b, c, d, a, x[i+ 1], 21, -2054922799);
        a = this.md5Ii(a, b, c, d, x[i+ 8], 6 ,  1873313359);
        d = this.md5Ii(d, a, b, c, x[i+15], 10, -30611744);
        c = this.md5Ii(c, d, a, b, x[i+ 6], 15, -1560198380);
        b = this.md5Ii(b, c, d, a, x[i+13], 21,  1309151649);
        a = this.md5Ii(a, b, c, d, x[i+ 4], 6 , -145523070);
        d = this.md5Ii(d, a, b, c, x[i+11], 10, -1120210379);
        c = this.md5Ii(c, d, a, b, x[i+ 2], 15,  718787259);
        b = this.md5Ii(b, c, d, a, x[i+ 9], 21, -343485551);

        a = this.safeAdd(a, olda);
        b = this.safeAdd(b, oldb);
        c = this.safeAdd(c, oldc);
        d = this.safeAdd(d, oldd);
      }
      return Array(a, b, c, d);
    },

    /*
     * These functions implement the four basic operations the algorithm uses.
     */
    md5Cmn: function(q, a, b, x, s, t) {
      return this.safeAdd(this.bitwiseRotateLeft(this.safeAdd(this.safeAdd(a, q), this.safeAdd(x, t)), s),b);
    },
    md5Ff: function(a, b, c, d, x, s, t) {
      return this.md5Cmn((b & c) | ((~b) & d), a, b, x, s, t);
    },
    md5Gg: function(a, b, c, d, x, s, t) {
      return this.md5Cmn((b & d) | (c & (~d)), a, b, x, s, t);
    },
    md5Hh: function(a, b, c, d, x, s, t) {
      return this.md5Cmn(b ^ c ^ d, a, b, x, s, t);
    },
    md5Ii: function(a, b, c, d, x, s, t) {
      return this.md5Cmn(c ^ (b | (~d)), a, b, x, s, t);
    },

    /*
     * Add integers, wrapping at 2^32. This uses 16-bit operations internally
     * to work around bugs in some JS interpreters.
     */
    safeAdd: function(x, y) {
      var lsw = (x & 0xFFFF) + (y & 0xFFFF);
      var msw = (x >> 16) + (y >> 16) + (lsw >> 16);
      return (msw << 16) | (lsw & 0xFFFF);
    },

    /*
     * Bitwise rotate a 32-bit number to the left.
     */
    bitwiseRotateLeft: function(num, cnt) {
      return (num << cnt) | (num >>> (32 - cnt));
    },
    sha512: function(string) {
        return this.hexSha512(string);
    },
    hexSha512: function(string) {
        return this.stringToHex(this.stringSha512(this.stringToStringUtf8(string)));
    },
    base64Sha512: function(string) {
        return this.stringToBase64(this.stringSha512(this.stringToStringUtf8(string)));
    },
    anySha512: function(string, e) {
        return this.stringToAny(this.stringSha512(this.stringToStringUtf8(string)), e);
    },
    hexHmacSha512: function(k, d) {
        return this.stringToHex(this.stringHmacSha512(this.stringToStringUtf8(k), this.stringToStringUtf8(d)));
    },
    base64HmacSha512: function(k, d) {
        return this.stringToBase64(this.stringHmacSha512(this.stringToStringUtf8(k), this.stringToStringUtf8(d)));
    },
    anyHmacSha512: function(k, d, e) {
        return this.stringToAny(this.stringHmacSha512(this.stringToStringUtf8(k), this.stringToStringUtf8(d)), e);
    },
    //Perform a simple self-test to see if the VM is working
    sha512Test: function() {
        return this.hexSha512("abc").toLowerCase() ==
        "ddaf35a193617abacc417349ae20413112e6fa4e89a97ea20a9eeee64b55d39a" +
        "2192992a274fc1a836ba3c23a3feebbd454d4423643ce80e2a9ac94fa54ca49f";
    },
    // Calculate the SHA-512 of a raw string
    stringSha512: function(string) {
        return this.bigEndianToString(this.bigEndianSha512(this.stringToBigEndian(string), string.length * 8));
    },
    // Calculate the HMAC-SHA-512 of a key and some data (raw strings)
    stringHmacSha512: function(key, data) {
        var bKey = this.stringToBigEndian(key);
        if(bKey.length > 32) bKey = this.bigEndianSha512(bKey, key.length * 8);
        var iPad = Array(32), oPad = Array(32);
        for(var i = 0; i < 32; i++) {
            iPad[i] = bKey[i] ^ 0x36363636;
            oPad[i] = bKey[i] ^ 0x5C5C5C5C;
        }
        var hash = this.bigEndianSha512(iPad.concat(this.stringToBigEndian(data)), 1024 + data.length * 8);
        return this.bigEndianToString(this.bigEndianSha512(oPad.concat(hash), 1024 + 512));
    },
    // Convert a raw string to a hex string
    stringToHex: function(input) {
        try {
            this.hexCase
        }
        catch(e) {
            this.hexCase=0;
        }
        var hexTab = this.hexCase ? "0123456789ABCDEF" : "0123456789abcdef";
        var output = "";
        var x;
        for(var i = 0; i < input.length; i++) {
            x = input.charCodeAt(i);
            output += hexTab.charAt((x >>> 4) & 0x0F)
            +  hexTab.charAt( x        & 0x0F);
        }
        return output;
    },
    // Convert a raw string to a base-64 string
    stringToBase64: function(input) {
        try {
            this.base64Pad
        }
        catch(e) {
            this.base64Pad='';
        }
        var tab = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
        var output = "";
        var len = input.length;
        for(var i = 0; i < len; i += 3) {
            var triplet = (input.charCodeAt(i) << 16)
            | (i + 1 < len ? input.charCodeAt(i+1) << 8 : 0)
            | (i + 2 < len ? input.charCodeAt(i+2)      : 0);
            for(var j = 0; j < 4; j++)
            {
                if(i * 8 + j * 6 > input.length * 8) output += this.base64Pad;
                else output += tab.charAt((triplet >>> 6*(3-j)) & 0x3F);
            }
        }
        return output;
    },
    // Convert a raw string to an arbitrary string encoding
    stringToAny: function(input, encoding) {
        var divisor = encoding.length;
        var i, j, q, x, quotient;

        /* Convert to an array of 16-bit big-endian values, forming the dividend */
        var dividend = Array(Math.ceil(input.length / 2));
        for(i = 0; i < dividend.length; i++) {
            dividend[i] = (input.charCodeAt(i * 2) << 8) | input.charCodeAt(i * 2 + 1);
        }

        // Repeatedly perform a long division. The binary array forms the dividend,
        // the length of the encoding is the divisor. Once computed, the quotient
        // forms the dividend for the next step. All remainders are stored for later
        // use.
        var fullLength = Math.ceil(input.length * 8 /
            (Math.log(encoding.length) / Math.log(2)));
        var remainders = Array(fullLength);
        for(j = 0; j < fullLength; j++) {
            quotient = Array();
            x = 0;
            for(i = 0; i < dividend.length; i++)
            {
                x = (x << 16) + dividend[i];
                q = Math.floor(x / divisor);
                x -= q * divisor;
                if(quotient.length > 0 || q > 0)
                    quotient[quotient.length] = q;
            }
            remainders[j] = x;
            dividend = quotient;
        }

        // Convert the remainders to the output string
        var output = "";
        for(i = remainders.length - 1; i >= 0; i--) {
            output += encoding.charAt(remainders[i]);
        }

        return output;
    },
    // Encode a string as utf-8. For efficiency, this assumes the input is valid utf-16
    stringToStringUtf8: function(input) {
        var output = "";
        var i = -1;
        var x, y;
        while(++i < input.length) {
            // Decode utf-16 surrogate pairs
            x = input.charCodeAt(i);
            y = i + 1 < input.length ? input.charCodeAt(i + 1) : 0;
            if(0xD800 <= x && x <= 0xDBFF && 0xDC00 <= y && y <= 0xDFFF)
            {
                x = 0x10000 + ((x & 0x03FF) << 10) + (y & 0x03FF);
                i++;
            }
            // Encode output as utf-8
            if(x <= 0x7F)
                output += String.fromCharCode(x);
            else if(x <= 0x7FF)
                output += String.fromCharCode(0xC0 | ((x >>> 6 ) & 0x1F),
                    0x80 | ( x         & 0x3F));
            else if(x <= 0xFFFF)
                output += String.fromCharCode(0xE0 | ((x >>> 12) & 0x0F),
                    0x80 | ((x >>> 6 ) & 0x3F),
                    0x80 | ( x         & 0x3F));
            else if(x <= 0x1FFFFF)
                output += String.fromCharCode(0xF0 | ((x >>> 18) & 0x07),
                    0x80 | ((x >>> 12) & 0x3F),
                    0x80 | ((x >>> 6 ) & 0x3F),
                    0x80 | ( x         & 0x3F));
        }
        return output;
    },
    // Encode a string as utf-16
    stringToStringUtf16le: function(input) {
        var output = "";
        for(var i = 0; i < input.length; i++)
            output += String.fromCharCode( input.charCodeAt(i)        & 0xFF,
                (input.charCodeAt(i) >>> 8) & 0xFF);
        return output;
    },
    stringToStringUtf16be: function(input) {
        var output = "";
        for(var i = 0; i < input.length; i++)
            output += String.fromCharCode((input.charCodeAt(i) >>> 8) & 0xFF,
                input.charCodeAt(i)        & 0xFF);
        return output;
    },
    // Convert a raw string to an array of big-endian words. Characters >255 have their high-byte silently ignored
    stringToBigEndian: function(input) {
        var output = Array(input.length >> 2);
        for(var i = 0; i < output.length; i++)
            output[i] = 0;
        for(var i = 0; i < input.length * 8; i += 8)
            output[i>>5] |= (input.charCodeAt(i / 8) & 0xFF) << (24 - i % 32);
        return output;
    },
    // Convert an array of big-endian words to a string
    bigEndianToString: function(input) {
        var output = "";
        for(var i = 0; i < input.length * 32; i += 8)
            output += String.fromCharCode((input[i>>5] >>> (24 - i % 32)) & 0xFF);
        return output;
    },
    // Calculate the SHA-512 of an array of big-endian dwords, and a bit length
    sha512Constants: null,
    bigEndianSha512: function(x, len)
    {
        if(this.sha512Constants == undefined) {
            // SHA512 constants
            this.sha512Constants = new Array(
                new this.int64(0x428a2f98, -685199838), new this.int64(0x71374491, 0x23ef65cd),
                new this.int64(-1245643825, -330482897), new this.int64(-373957723, -2121671748),
                new this.int64(0x3956c25b, -213338824), new this.int64(0x59f111f1, -1241133031),
                new this.int64(-1841331548, -1357295717), new this.int64(-1424204075, -630357736),
                new this.int64(-670586216, -1560083902), new this.int64(0x12835b01, 0x45706fbe),
                new this.int64(0x243185be, 0x4ee4b28c), new this.int64(0x550c7dc3, -704662302),
                new this.int64(0x72be5d74, -226784913), new this.int64(-2132889090, 0x3b1696b1),
                new this.int64(-1680079193, 0x25c71235), new this.int64(-1046744716, -815192428),
                new this.int64(-459576895, -1628353838), new this.int64(-272742522, 0x384f25e3),
                new this.int64(0xfc19dc6, -1953704523), new this.int64(0x240ca1cc, 0x77ac9c65),
                new this.int64(0x2de92c6f, 0x592b0275), new this.int64(0x4a7484aa, 0x6ea6e483),
                new this.int64(0x5cb0a9dc, -1119749164), new this.int64(0x76f988da, -2096016459),
                new this.int64(-1740746414, -295247957), new this.int64(-1473132947, 0x2db43210),
                new this.int64(-1341970488, -1728372417), new this.int64(-1084653625, -1091629340),
                new this.int64(-958395405, 0x3da88fc2), new this.int64(-710438585, -1828018395),
                new this.int64(0x6ca6351, -536640913), new this.int64(0x14292967, 0xa0e6e70),
                new this.int64(0x27b70a85, 0x46d22ffc), new this.int64(0x2e1b2138, 0x5c26c926),
                new this.int64(0x4d2c6dfc, 0x5ac42aed), new this.int64(0x53380d13, -1651133473),
                new this.int64(0x650a7354, -1951439906), new this.int64(0x766a0abb, 0x3c77b2a8),
                new this.int64(-2117940946, 0x47edaee6), new this.int64(-1838011259, 0x1482353b),
                new this.int64(-1564481375, 0x4cf10364), new this.int64(-1474664885, -1136513023),
                new this.int64(-1035236496, -789014639), new this.int64(-949202525, 0x654be30),
                new this.int64(-778901479, -688958952), new this.int64(-694614492, 0x5565a910),
                new this.int64(-200395387, 0x5771202a), new this.int64(0x106aa070, 0x32bbd1b8),
                new this.int64(0x19a4c116, -1194143544), new this.int64(0x1e376c08, 0x5141ab53),
                new this.int64(0x2748774c, -544281703), new this.int64(0x34b0bcb5, -509917016),
                new this.int64(0x391c0cb3, -976659869), new this.int64(0x4ed8aa4a, -482243893),
                new this.int64(0x5b9cca4f, 0x7763e373), new this.int64(0x682e6ff3, -692930397),
                new this.int64(0x748f82ee, 0x5defb2fc), new this.int64(0x78a5636f, 0x43172f60),
                new this.int64(-2067236844, -1578062990), new this.int64(-1933114872, 0x1a6439ec),
                new this.int64(-1866530822, 0x23631e28), new this.int64(-1538233109, -561857047),
                new this.int64(-1090935817, -1295615723), new this.int64(-965641998, -479046869),
                new this.int64(-903397682, -366583396), new this.int64(-779700025, 0x21c0c207),
                new this.int64(-354779690, -840897762), new this.int64(-176337025, -294727304),
                new this.int64(0x6f067aa, 0x72176fba), new this.int64(0xa637dc5, -1563912026),
                new this.int64(0x113f9804, -1090974290), new this.int64(0x1b710b35, 0x131c471b),
                new this.int64(0x28db77f5, 0x23047d84), new this.int64(0x32caab7b, 0x40c72493),
                new this.int64(0x3c9ebe0a, 0x15c9bebc), new this.int64(0x431d67c4, -1676669620),
                new this.int64(0x4cc5d4be, -885112138), new this.int64(0x597f299c, -60457430),
                new this.int64(0x5fcb6fab, 0x3ad6faec), new this.int64(0x6c44198c, 0x4a475817));
        }

        //Initial hash values
        var H = new Array(
            new this.int64(0x6a09e667, -205731576),
            new this.int64(-1150833019, -2067093701),
            new this.int64(0x3c6ef372, -23791573),
            new this.int64(-1521486534, 0x5f1d36f1),
            new this.int64(0x510e527f, -1377402159),
            new this.int64(-1694144372, 0x2b3e6c1f),
            new this.int64(0x1f83d9ab, -79577749),
            new this.int64(0x5be0cd19, 0x137e2179));

        var T1 = new this.int64(0, 0),
        T2 = new this.int64(0, 0),
        a = new this.int64(0,0),
        b = new this.int64(0,0),
        c = new this.int64(0,0),
        d = new this.int64(0,0),
        e = new this.int64(0,0),
        f = new this.int64(0,0),
        g = new this.int64(0,0),
        h = new this.int64(0,0),
        // Temporary variables not specified by the document
        s0 = new this.int64(0, 0),
        s1 = new this.int64(0, 0),
        Ch = new this.int64(0, 0),
        Maj = new this.int64(0, 0),
        r1 = new this.int64(0, 0),
        r2 = new this.int64(0, 0),
        r3 = new this.int64(0, 0);
        var j, i;
        var W = new Array(80);
        for(i=0; i<80; i++) {
            W[i] = new this.int64(0, 0);
        }
        // Append padding to the source string. The format is described in the FIPS.
        x[len >> 5] |= 0x80 << (24 - (len & 0x1f));
        x[((len + 128 >> 10)<< 5) + 31] = len;
        // 32 dwords is the block size
        for(i = 0; i<x.length; i+=32) {
            this.int64Copy(a, H[0]);
            this.int64Copy(b, H[1]);
            this.int64Copy(c, H[2]);
            this.int64Copy(d, H[3]);
            this.int64Copy(e, H[4]);
            this.int64Copy(f, H[5]);
            this.int64Copy(g, H[6]);
            this.int64Copy(h, H[7]);

            for(j=0; j<16; j++) {
                W[j].h = x[i + 2*j];
                W[j].l = x[i + 2*j + 1];
            }

            for(j=16; j<80; j++) {
                // Sigma1
                this.int64RightRotate(r1, W[j-2], 19);
                this.int64ReverseRightRotate(r2, W[j-2], 29);
                this.int64Shift(r3, W[j-2], 6);
                s1.l = r1.l ^ r2.l ^ r3.l;
                s1.h = r1.h ^ r2.h ^ r3.h;
                // Sigma0
                this.int64RightRotate(r1, W[j-15], 1);
                this.int64RightRotate(r2, W[j-15], 8);
                this.int64Shift(r3, W[j-15], 7);
                s0.l = r1.l ^ r2.l ^ r3.l;
                s0.h = r1.h ^ r2.h ^ r3.h;

                this.int64Add4(W[j], s1, W[j-7], s0, W[j-16]);
            }

            for(j = 0; j < 80; j++) {
                //Ch
                Ch.l = (e.l & f.l) ^ (~e.l & g.l);
                Ch.h = (e.h & f.h) ^ (~e.h & g.h);

                //Sigma1
                this.int64RightRotate(r1, e, 14);
                this.int64RightRotate(r2, e, 18);
                this.int64ReverseRightRotate(r3, e, 9);
                s1.l = r1.l ^ r2.l ^ r3.l;
                s1.h = r1.h ^ r2.h ^ r3.h;

                //Sigma0
                this.int64RightRotate(r1, a, 28);
                this.int64ReverseRightRotate(r2, a, 2);
                this.int64ReverseRightRotate(r3, a, 7);
                s0.l = r1.l ^ r2.l ^ r3.l;
                s0.h = r1.h ^ r2.h ^ r3.h;

                //Maj
                Maj.l = (a.l & b.l) ^ (a.l & c.l) ^ (b.l & c.l);
                Maj.h = (a.h & b.h) ^ (a.h & c.h) ^ (b.h & c.h);

                this.int64Add5(T1, h, s1, Ch, this.sha512Constants[j], W[j]);
                this.int64Add(T2, s0, Maj);

                this.int64Copy(h, g);
                this.int64Copy(g, f);
                this.int64Copy(f, e);
                this.int64Add(e, d, T1);
                this.int64Copy(d, c);
                this.int64Copy(c, b);
                this.int64Copy(b, a);
                this.int64Add(a, T1, T2);
            }
            this.int64Add(H[0], H[0], a);
            this.int64Add(H[1], H[1], b);
            this.int64Add(H[2], H[2], c);
            this.int64Add(H[3], H[3], d);
            this.int64Add(H[4], H[4], e);
            this.int64Add(H[5], H[5], f);
            this.int64Add(H[6], H[6], g);
            this.int64Add(H[7], H[7], h);
        }

        // Represent the hash as an array of 32-bit dwords
        var hash = new Array(16);
        for(i=0; i<8; i++) {
            hash[2*i] = H[i].h;
            hash[2*i + 1] = H[i].l;
        }
        return hash;
    },
    // A constructor for 64-bit numbers
    int64: function(h, l) {
        this.h = h;
        this.l = l;
        //this.toString = int64toString;
    },
    // Copies source into destination, assuming both are 64-bit numbers
    int64Copy: function(destination, source) {
        destination.h = source.h;
        destination.l = source.l;
    },
    // Right-rotates a 64-bit number by shift. Won't handle cases of shift>=32. The revrrot: function() is for that
    int64RightRotate: function(destination, x, shift) {
        destination.l = (x.l >>> shift) | (x.h << (32-shift));
        destination.h = (x.h >>> shift) | (x.l << (32-shift));
    },
    // Reverses the dwords of the source and then rotates right by shift. This is equivalent to rotation by 32+shift
    int64ReverseRightRotate: function(destination, x, shift) {
        destination.l = (x.h >>> shift) | (x.l << (32-shift));
        destination.h = (x.l >>> shift) | (x.h << (32-shift));
    },
    // Bitwise-shifts right a 64-bit number by shift. Won't handle shift>=32, but it's never needed in SHA512
    int64Shift: function(destination, x, shift) {
        destination.l = (x.l >>> shift) | (x.h << (32-shift));
        destination.h = (x.h >>> shift);
    },
    // Adds two 64-bit numbers. Like the original implementation, does not rely on 32-bit operations
    int64Add: function(destination, x, y) {
        var w0 = (x.l & 0xffff) + (y.l & 0xffff);
        var w1 = (x.l >>> 16) + (y.l >>> 16) + (w0 >>> 16);
        var w2 = (x.h & 0xffff) + (y.h & 0xffff) + (w1 >>> 16);
        var w3 = (x.h >>> 16) + (y.h >>> 16) + (w2 >>> 16);
        destination.l = (w0 & 0xffff) | (w1 << 16);
        destination.h = (w2 & 0xffff) | (w3 << 16);
    },
    // Same, except with 4 addends. Works faster than adding them one by one.
    int64Add4: function(destination, a, b, c, d) {
        var w0 = (a.l & 0xffff) + (b.l & 0xffff) + (c.l & 0xffff) + (d.l & 0xffff);
        var w1 = (a.l >>> 16) + (b.l >>> 16) + (c.l >>> 16) + (d.l >>> 16) + (w0 >>> 16);
        var w2 = (a.h & 0xffff) + (b.h & 0xffff) + (c.h & 0xffff) + (d.h & 0xffff) + (w1 >>> 16);
        var w3 = (a.h >>> 16) + (b.h >>> 16) + (c.h >>> 16) + (d.h >>> 16) + (w2 >>> 16);
        destination.l = (w0 & 0xffff) | (w1 << 16);
        destination.h = (w2 & 0xffff) | (w3 << 16);
    },
    // Same, except with 5 addends
    int64Add5: function(destination, a, b, c, d, e) {
        var w0 = (a.l & 0xffff) + (b.l & 0xffff) + (c.l & 0xffff) + (d.l & 0xffff) + (e.l & 0xffff);
        var w1 = (a.l >>> 16) + (b.l >>> 16) + (c.l >>> 16) + (d.l >>> 16) + (e.l >>> 16) + (w0 >>> 16);
        var w2 = (a.h & 0xffff) + (b.h & 0xffff) + (c.h & 0xffff) + (d.h & 0xffff) + (e.h & 0xffff) + (w1 >>> 16);
        var w3 = (a.h >>> 16) + (b.h >>> 16) + (c.h >>> 16) + (d.h >>> 16) + (e.h >>> 16) + (w2 >>> 16);
        destination.l = (w0 & 0xffff) | (w1 << 16);
        destination.h = (w2 & 0xffff) | (w3 << 16);
    }
});
var Security = new SecurityClass();