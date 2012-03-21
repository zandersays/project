<?php

Class Browser {

    public static $agent = '';
    public static $browserName = '';
    public static $version = '';
    public static $platform = '';
    public static $os = '';
    public static $isAol = false;
    public static $isMobile = false;
    public static $isRobot = false;
    public static $aolVersion = '';

    const BROWSER_UNKNOWN = 'unknown';
    const VERSION_UNKNOWN = 'unknown';
    const BROWSER_OPERA = 'Opera';                            // http://www.opera.com/
    const BROWSER_OPERA_MINI = 'Opera Mini';                  // http://www.opera.com/mini/
    const BROWSER_WEBTV = 'WebTV';                            // http://www.webtv.net/pc/
    const BROWSER_IE = 'Internet Explorer';                   // http://www.microsoft.com/ie/
    const BROWSER_POCKET_IE = 'Pocket Internet Explorer';     // http://en.wikipedia.org/wiki/Internet_Explorer_Mobile
    const BROWSER_KONQUEROR = 'Konqueror';                    // http://www.konqueror.org/
    const BROWSER_ICAB = 'iCab';                              // http://www.icab.de/
    const BROWSER_OMNIWEB = 'OmniWeb';                        // http://www.omnigroup.com/applications/omniweb/
    const BROWSER_FIREBIRD = 'Firebird';                      // http://www.ibphoenix.com/
    const BROWSER_FIREFOX = 'Firefox';                        // http://www.mozilla.com/en-US/firefox/firefox.html
    const BROWSER_ICEWEASEL = 'Iceweasel';                    // http://www.geticeweasel.org/
    const BROWSER_SHIRETOKO = 'Shiretoko';                    // http://wiki.mozilla.org/Projects/shiretoko
    const BROWSER_MOZILLA = 'Mozilla';                        // http://www.mozilla.com/en-US/
    const BROWSER_AMAYA = 'Amaya';                            // http://www.w3.org/Amaya/
    const BROWSER_LYNX = 'Lynx';                              // http://en.wikipedia.org/wiki/Lynx
    const BROWSER_SAFARI = 'Safari';                          // http://apple.com
    const BROWSER_IPHONE = 'iPhone';                          // http://apple.com
    const BROWSER_IPOD = 'iPod';                              // http://apple.com
    const BROWSER_IPAD = 'iPad';                              // http://apple.com
    const BROWSER_CHROME = 'Chrome';                          // http://www.google.com/chrome
    const BROWSER_ANDROID = 'Android';                        // http://www.android.com/
    const BROWSER_GOOGLEBOT = 'GoogleBot';                    // http://en.wikipedia.org/wiki/Googlebot
    const BROWSER_SLURP = 'Yahoo! Slurp';                     // http://en.wikipedia.org/wiki/Yahoo!_Slurp
    const BROWSER_W3CVALIDATOR = 'W3C Validator';             // http://validator.w3.org/
    const BROWSER_BLACKBERRY = 'BlackBerry';                  // http://www.blackberry.com/
    const BROWSER_ICECAT = 'IceCat';                          // http://en.wikipedia.org/wiki/GNU_IceCat
    const BROWSER_NOKIA_S60 = 'Nokia S60 OSS Browser';        // http://en.wikipedia.org/wiki/Web_Browser_for_S60
    const BROWSER_NOKIA = 'Nokia Browser';                    // * all other WAP-based browsers on the Nokia Platform
    const BROWSER_MSN = 'MSN Browser';                        // http://explorer.msn.com/
    const BROWSER_MSNBOT = 'MSN Bot';                         // http://search.msn.com/msnbot.htm
    // http://en.wikipedia.org/wiki/Msnbot  (used for Bing as well)
    const BROWSER_NETSCAPE_NAVIGATOR = 'Netscape Navigator';  // http://browser.netscape.com/ (DEPRECATED)
    const BROWSER_GALEON = 'Galeon';                          // http://galeon.sourceforge.net/ (DEPRECATED)
    const BROWSER_NETPOSITIVE = 'NetPositive';                // http://en.wikipedia.org/wiki/NetPositive (DEPRECATED)
    const BROWSER_PHOENIX = 'Phoenix';                        // http://en.wikipedia.org/wiki/History_of_Mozilla_Firefox (DEPRECATED)
    const PLATFORM_UNKNOWN = 'unknown';
    const PLATFORM_WINDOWS = 'Windows';
    const PLATFORM_WINDOWS_CE = 'Windows CE';
    const PLATFORM_APPLE = 'Apple';
    const PLATFORM_LINUX = 'Linux';
    const PLATFORM_OS2 = 'OS/2';
    const PLATFORM_BEOS = 'BeOS';
    const PLATFORM_IPHONE = 'iPhone';
    const PLATFORM_IPOD = 'iPod';
    const PLATFORM_IPAD = 'iPad';
    const PLATFORM_BLACKBERRY = 'BlackBerry';
    const PLATFORM_NOKIA = 'Nokia';
    const PLATFORM_FREEBSD = 'FreeBSD';
    const PLATFORM_OPENBSD = 'OpenBSD';
    const PLATFORM_NETBSD = 'NetBSD';
    const PLATFORM_SUNOS = 'SunOS';
    const PLATFORM_OPENSOLARIS = 'OpenSolaris';
    const PLATFORM_ANDROID = 'Android';
    const OPERATING_SYSTEM_UNKNOWN = 'unknown';

    public static function initialize() {
        self::$agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    }

    public static function setAgent() {
        if (self::$agent == '') {
            self::$agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        }
    }

    public static function isBrowser($browserName) {
        return( 0 == strcasecmp(self::getBrowser(), trim($browserName)));
    }

    public static function getBrowser() {
        self::setAgent();
        if (stripos(self::$agent, 'blackberry') !== false) {
            $browser = self::BROWSER_BLACKBERRY;
        } else if (stripos(self::$agent, 'aol') !== false) {
            $browser = "AOL";
        } else if (stripos(self::$agent, 'googlebot') !== false) {
            $browser = self::BROWSER_GOOGLEBOT;
        } else if (stripos(self::$agent, "msnbot") !== false) {
            $browser = self::BROWSER_MSNBOT;
        } else if (stripos(self::$agent, 'W3C-checklink') !== false) {
            $browser = self::BROWSER_W3CVALIDATOR;
        } else if (stripos(self::$agent, 'W3C_Validator') !== false) {
            $browser = self::BROWSER_W3CVALIDATOR;
        } else if (stripos(self::$agent, 'slurp') !== false) {
            $$browser = self::BROWSER_SLURP;
        } else if (stripos(self::$agent, 'microsoft internet explorer') !== false) {
            $browser = self::BROWSER_IE;
        } else if (stripos(self::$agent, 'msie') !== false && stripos(self::$agent, 'opera') === false) {
            $browser = self::BROWSER_IE;
            if (stripos(self::$agent, 'msnb') !== false) {
                $browser = self::BROWSER_MSN;
            }
        } else if (stripos(self::$agent, 'mspie') !== false || stripos(self::$agent, 'pocket') !== false) {
            $browser = self::BROWSER_POCKET_IE;
        } else if (stripos(self::$agent, 'opera mini') !== false) {
            $this->_browser_name = self::BROWSER_OPERA_MINI;
        } else if (stripos(self::$agent, 'opera') !== false) {
            $this->_browser_name = self::BROWSER_OPERA;
        } else if (stripos(self::$agent, 'Chrome') !== false) {
            $browser = self::BROWSER_CHROME;
        } else if (stripos(self::$agent, 'safari') === false) {
            if (preg_match("/Firefox[\/ \(]([^ ;\)]+)/i", self::$agent, $matches) || preg_match("/Firefox$/i", self::$agent, $matches)) {
                $browser = self::BROWSER_FIREFOX;
            }
        } else if (stripos(self::$agent, 'Safari') !== false && stripos(self::$agent, 'iPhone') === false && stripos(self::$agent, 'iPod') === false) {
            $browser = self::BROWSER_SAFARI;
        } else if (stripos(self::$agent, 'iPhone') !== false) {
            $browser = self::BROWSER_IPHONE;
        } else if (stripos(self::$agent, 'iPad') !== false) {
            $browser = self::BROWSER_IPAD;
        } else if (stripos(self::$agent, 'Android') !== false) {
            $browser = self::BROWSER_ANDROID;
        } else {
            $browser = self::$agent;
        }

        return $browser;
    }

    public function isChromeFrame() {
        return( strpos(self::$agent, "chromeframe") !== false );
    }

    public static function isMobile() {
        if (stripos(self::$agent, 'blackberry') !== false) {
            return true;
        } else if (stripos(self::$agent, 'slurp') !== false) {
            return true;
        } else if (stripos(self::$agent, 'mspie') !== false || stripos(self::$agent, 'pocket') !== false) {
            return true;
        } else if (stripos(self::$agent, 'opera mini') !== false) {
            return true;
        } else if (stripos(self::$agent, 'iPhone') !== false) {
            return true;
        } else if (stripos(self::$agent, 'iPad') !== false) {
            return true;
        } else if (stripos(self::$agent, 'Android') !== false) {
            return true;
        } else {
            return false;
        }
    }

    public static function getVersion() {
        if (stripos(self::$agent, 'blackberry') !== false) {
            $version = explode("/", stristr(self::$agent, "BlackBerry"));
            $version = explode(' ', $version[1]);
            $version = $version[0];
        } else if (stripos(self::$agent, 'aol') !== false) {
            $version = explode(' ', stristr(self::$agent, 'AOL'));
            $version = preg_replace('/[^0-9\.a-z]/i', '', $version[1]);
        } else if (stripos(self::$agent, 'googlebot') !== false) {
            $version = explode('/', stristr(self::$agent, 'googlebot'));
            $version = explode(' ', $version[1]);
            $version = str_replace(';', '', $version[0]);
        } else if (stripos(self::$agent, "msnbot") !== false) {
            $version = explode("/", stristr(self::$agent, "msnbot"));
            $version = explode(" ", $version[1]);
            $version = str_replace(";", "", $version[0]);
        } else if (stripos(self::$agent, 'W3C-checklink') !== false) {
            $version = explode('/', stristr(self::$agent, 'W3C-checklink'));
            $version = explode(' ', $version[1]);
            $version = $version[0];
        } else if (stripos(self::$agent, 'W3C_Validator') !== false) {
            $ua = str_replace("W3C_Validator ", "W3C_Validator/", self::$agent);
            $version = explode('/', stristr($ua, 'W3C_Validator'));
            $version = explode(' ', $version[1]);
            $version = $version[0];
        } else if (stripos(self::$agent, 'slurp') !== false) {
            $version = explode('/', stristr(self::$agent, 'Slurp'));
            $version = explode(' ', $version[1]);
            $version = $version[0];
        } else if (stripos(self::$agent, 'microsoft internet explorer') !== false) {
            $version = '1.0';
            $version = stristr(self::$agent, '/');
            if (preg_match('/308|425|426|474|0b1/i', $version)) {
                $version = ('1.5');
            }
        } else if (stripos(self::$agent, 'msie') !== false && stripos(self::$agent, 'opera') === false) {
            if (stripos(self::$agent, 'msnb') !== false) {
                $version = explode(' ', stristr(str_replace(';', '; ', self::$agent), 'MSN'));
                $version = str_replace(array('(', ')', ';'), '', $version[1]);
            } else {
                $version = explode(' ', stristr(str_replace(';', '; ', self::$agent), 'msie'));
                $version = str_replace(array('(', ')', ';'), '', $version[1]);
            }
        } else if (stripos(self::$agent, 'mspie') !== false || stripos(self::$agent, 'pocket') !== false) {
            $version = explode(' ', stristr(self::$agent, 'mspie'));
            if (stripos(self::$agent, 'mspie') !== false) {
                $version = $version[1];
            } else {
                $version = explode('/', self::$agent);
                $version = $version[1];
            }
        } else if (stripos(self::$agent, 'opera mini') !== false) {
            $resultant = stristr(self::$agent, 'opera mini');
            if (preg_match('/\//', $resultant)) {
                $version = explode('/', $resultant);
                $version = explode(' ', $version[1]);
                $version = $version[0];
            } else {
                $version = explode(' ', stristr($resultant, 'opera mini'));
                $version = $version[1];
            }
        } else if (stripos(self::$agent, 'opera') !== false) {
            if (preg_match('/Version\/(10.*)$/', $resultant, $matches)) {
                $version = $matches[1];
            } else if (preg_match('/\//', $resultant)) {
                $version = explode('/', str_replace("(", " ", $resultant));
                $version = explode(' ', $version[1]);
                $version = $version[0];
            } else {
                $version = explode(' ', stristr($resultant, 'opera'));
                $version = isset($version[1]) ? $version[1] : "";
            }
        } else if (stripos(self::$agent, 'Chrome') !== false) {
            $version = explode('/', stristr(self::$agent, 'Chrome'));
            $version = explode(' ', $version[1]);
            $version = $version[0];
        } else if (stripos(self::$agent, 'safari') === false) {
            if (preg_match("/Firefox[\/ \(]([^ ;\)]+)/i", self::$agent, $matches)) {
                $version = $matches[1];
            } else if (preg_match("/Firefox$/i", self::$agent, $matches)) {
                $version = "";
            }
        } else if (stripos(self::$agent, 'Safari') !== false && stripos(self::$agent, 'iPhone') === false && stripos(self::$agent, 'iPod') === false) {
            $version = explode('/', stristr(self::$agent, 'Version'));
            if (isset($version[1])) {
                $version = explode(' ', $version[1]);
                $version = $version[0];
            } else {
                $version = self::VERSION_UNKNOWN;
            }
        } else if (stripos(self::$agent, 'iPhone') !== false) {
            $version = explode('/', stristr(self::$agent, 'Version'));
            if (isset($version[1])) {
                $version = explode(' ', $version[1]);
                $version = $version[0];
            } else {
                $version = self::VERSION_UNKNOWN;
            }
        } else if (stripos(self::$agent, 'iPad') !== false) {
            $version = explode('/', stristr(self::$agent, 'Version'));
            if (isset($version[1])) {
                $version = explode(' ', $version[1]);
                $version = $version[0];
            } else {
                $version = self::VERSION_UNKNOWN;
            }
        } else if (stripos(self::$agent, 'Android') !== false) {
            $version = explode(' ', stristr(self::$agent, 'Android'));
            if (isset($version[1])) {
                $version = explode(' ', $version[1]);
                $version = $version[0];
            } else {
                $version = self::VERSION_UNKNOWN;
            }
        }



        return $version;
    }

    public static function getPlatform() {
        if (stripos(self::$agent, 'windows') !== false) {
            return self::PLATFORM_WINDOWS;
        } else if (stripos(self::$agent, 'iPad') !== false) {
            return self::PLATFORM_IPAD;
        } else if (stripos(self::$agent, 'iPod') !== false) {
            return self::PLATFORM_IPOD;
        } else if (stripos(self::$agent, 'iPhone') !== false) {
            return self::PLATFORM_IPHONE;
        } elseif (stripos(self::$agent, 'mac') !== false) {
            return self::PLATFORM_APPLE;
        } elseif (stripos(self::$agent, 'android') !== false) {
            return self::PLATFORM_ANDROID;
        } elseif (stripos(self::$agent, 'linux') !== false) {
            return self::PLATFORM_LINUX;
        } else if (stripos(self::$agent, 'Nokia') !== false) {
            return self::PLATFORM_NOKIA;
        } else if (stripos(self::$agent, 'BlackBerry') !== false) {
            return self::PLATFORM_BLACKBERRY;
        } elseif (stripos(self::$agent, 'FreeBSD') !== false) {
            return self::PLATFORM_FREEBSD;
        } elseif (stripos(self::$agent, 'OpenBSD') !== false) {
            return self::PLATFORM_OPENBSD;
        } elseif (stripos(self::$agent, 'NetBSD') !== false) {
            return self::PLATFORM_NETBSD;
        } elseif (stripos(self::$agent, 'OpenSolaris') !== false) {
            return self::PLATFORM_OPENSOLARIS;
        } elseif (stripos(self::$agent, 'SunOS') !== false) {
            return self::PLATFORM_SUNOS;
        } elseif (stripos(self::$agent, 'OS\/2') !== false) {
            return self::PLATFORM_OS2;
        } elseif (stripos(self::$agent, 'BeOS') !== false) {
            return self::PLATFORM_BEOS;
        } elseif (stripos(self::$agent, 'win') !== false) {
            return self::PLATFORM_WINDOWS;
        }
    }

    public static function getBrowserData() {
        return array(
            'browser' => self::getBrowser(),
            'version' => self::getVersion(),
            'platform' => self::getPlatform(),
            'isMobile' => self::isMobile(),
        );
    }

}

?>
