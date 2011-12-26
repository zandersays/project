<?php
class Cookie {

    public static $expiration = 0;
    public static $domain = '';
    public static $path = '/';
    public static $signing = true;
    public static $signingSalt = 'phr4m3wrk';
    public static $httpsOnly = false;
    public static $httpProtocolOnly = false;

    public static function get($key, $signing = null) {
        // Handle default signing options
        if($signing === null) {
            $signing = Cookie::$signing;
        }

        // Check if the cookie exists
        if(!isset($_COOKIE[$key])) {
            //echo 'Cookie with key '.$key.' does not exist.';
            return null;
        }
        else {
            //echo 'Cookie with key '.$key.' does exist.';
        }

        // Get the cookie value
        $cookie = $_COOKIE[$key];

        // If cookie signing is enabled, process signed cookies
        if($signing == true) {
            //echo 'Cookie signing is on.';

            // Find the position of the split between salt and contents
            $split = strlen(Cookie::salt($key, null));

            if(isset($cookie[$split]) AND $cookie[$split] === ':') {
                // Get the salt and value
                list($hash, $value) = explode(':', $cookie, 2);

                // Check if the salt is valid
                if(Cookie::salt($key, $value) === $hash) {
                    return $value;
                }
                // If the salt is invalid, remove the cookie
                else {
                    Cookie::delete($key);
                    return null;
                }
            }
            else {
                return null;
            }
        }
        else {
            return $cookie;
        }
    }

    public static function set($key, $value, $options = array()) {
        // Set the cookie settings using options or use defaults
        $expiration = isset($options['expiration']) ? $options['expiration'] : Cookie::$expiration;
        $path = isset($options['path']) ? $options['path'] : Cookie::$path;
        $domain = isset($options['domain']) ? $options['domain'] : Cookie::$domain;
        $httpsOnly = isset($options['httpsOnly']) ? $options['httpsOnly'] : Cookie::$httpsOnly;
        $httpProtocolOnly = isset($options['httpProtocolOnly']) ? $options['httpProtocolOnly'] : Cookie::$httpProtocolOnly;
        $signing = isset($options['signing']) ? $options['signing'] : Cookie::$signing;

        // Set the correct expiration time
        if($expiration !== 0) {
            $expiration += time();
        }

        // If signing is enabled
        if($signing) {
            // Add the salt to the cookie value
            $value = Cookie::salt($key, $value).':'.$value;

            return setcookie($key, $value, $expiration, $path, $domain, $httpsOnly, $httpProtocolOnly);
        }
        else {
            return setcookie($key, $value, $expiration, $path, $domain, $httpsOnly, $httpProtocolOnly);
        }
    }

    public static function delete($key) {
        unset($_COOKIE[$key]);

        return Cookie::set($key, null, array('expiration' => -86400));
    }

    public static function salt($key, $value) {
        // Use the user agent in the salting process
        $agent = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : 'Unknown';

        return String::sub(hash('sha512', $agent.$key.$value.Cookie::$signingSalt), 0, 8);
    }

    public static function parse($cookieHeaderLine) {
        $cookieHeaderLine = preg_replace('/^Set-Cookie: /i', '', trim($cookieHeaderLine));
        $cookieStringArray = (strpos($cookieHeaderLine, ';') !== false ) ? explode(';', $cookieHeaderLine) : array($cookieHeaderLine);
        $cookie = array();

        foreach($cookieStringArray as $data) {
            $cookieData = explode('=', $data);
            $cookieData[0] = trim($cookieData[0]);

            // Get the cookie expires time
            if($cookieData[0] == 'expires') {
                $cookieExpiresDateTime = new DateTime($cookieData[1]);
                $cookieData[1] = $cookieExpiresDateTime->format('U');
            }

            // Check to see if the cookie is secure
            if($cookieData[0] == 'secure') {
                $cookieData[1] = true;
            }

            // Handle any cookie meta variables
            if(in_array($cookieData[0], array('domain', 'expires', 'path', 'secure', 'comment'))) {
                $cookie[trim($cookieData[0])] = $cookieData[1];
            }
            else {
                $cookie['key'] = $cookieData[0];
                $cookie['value'] = $cookieData[1];
            }
        }

        $formattedCookie = array();
        foreach($cookie as $key => $value) {
            if($key != 'key') {
                $formattedCookie[$cookie['key']][$key] = $value;
            }
        }

        return $formattedCookie;
    }

    public static function build($data) {
        if(is_array($data)) {
            $cookie = '';
            foreach($data as $d) {
                $cookie[] = $d['value']['key'] . '=' . $d['value']['value'];
            }
            if(count($cookie) > 0) {
                return trim(implode('; ', $cookie));
            }
        }
        return false;
    }

}

?>