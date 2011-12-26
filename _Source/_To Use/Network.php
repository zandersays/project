<?php

class Network {
    
    public static function getBaseDomain($hostname) {
        $debug = 0;
        $baseDomain = "";

        // Generic tlds (source: http://en.wikipedia.org/wiki/Generic_top-level_domain)
        $genericTlds = array(
            'biz','com','edu','gov','info','int','mil','name','net','org',
            'aero','asia','cat','coop','jobs','mobi','museum','pro','tel','travel',
            'arpa','root',
            'berlin','bzh','cym','gal','geo','kid','kids','lat','mail','nyc','post','sco','web','xxx',
            'nato',
            'example','invalid','localhost','test',
            'bitnet','csnet','ip','local','onion','uucp',
            'co' // Note: not technically, but used in things like co.uk
        );

        // Country tlds (source: http://en.wikipedia.org/wiki/Country_code_top-level_domain)
        $countryTlds = array(
            // active
            'ac','ad','ae','af','ag','ai','al','am','an','ao','aq','ar','as','at','au','aw','ax','az',
            'ba','bb','bd','be','bf','bg','bh','bi','bj','bm','bn','bo','br','bs','bt','bw','by','bz',
            'ca','cc','cd','cf','cg','ch','ci','ck','cl','cm','cn','co','cr','cu','cv','cx','cy','cz',
            'de','dj','dk','dm','do','dz','ec','ee','eg','er','es','et','eu','fi','fj','fk','fm','fo',
            'fr','ga','gd','ge','gf','gg','gh','gi','gl','gm','gn','gp','gq','gr','gs','gt','gu','gw',
            'gy','hk','hm','hn','hr','ht','hu','id','ie','il','im','in','io','iq','ir','is','it','je',
            'jm','jo','jp','ke','kg','kh','ki','km','kn','kr','kw','ky','kz','la','lb','lc','li','lk',
            'lr','ls','lt','lu','lv','ly','ma','mc','md','mg','mh','mk','ml','mm','mn','mo','mp','mq',
            'mr','ms','mt','mu','mv','mw','mx','my','mz','na','nc','ne','nf','ng','ni','nl','no','np',
            'nr','nu','nz','om','pa','pe','pf','pg','ph','pk','pl','pn','pr','ps','pt','pw','py','qa',
            're','ro','ru','rw','sa','sb','sc','sd','se','sg','sh','si','sk','sl','sm','sn','sr','st',
            'sv','sy','sz','tc','td','tf','tg','th','tj','tk','tl','tm','tn','to','tr','tt','tv','tw',
            'tz','ua','ug','uk','us','uy','uz','va','vc','ve','vg','vi','vn','vu','wf','ws','ye','yu',
            'za','zm','zw',
            // inactive
            'eh','kp','me','rs','um','bv','gb','pm','sj','so','yt','su','tp','bu','cs','dd','zr'
        );

        // Get domain
        $fullDomain = $hostname;

        // Break up domain, reverse
        $domain = explode('.', $fullDomain);
        $domain = array_reverse($domain);

        // First check for IP address
        if(count($domain) == 4 && is_numeric($domain[0]) && is_numeric($domain[3])) {
            return $fullDomain;
        }

        // if only 2 domain parts, that must be our domain
        if(count($domain) <= 2) {
            return $fullDomain;
        }

        /*
        Finally, with 3+ domain parts: obviously D0 is tld
        now, if D0 = ctld and D1 = gtld, we might have something like com.uk
        so, if D0 = ctld && D1 = gtld && D2 != 'www', domain = D2.D1.D0
        else if D0 = ctld && D1 = gtld && D2 == 'www', domain = D1.D0
        else domain = D1.D0
        these rules are simplified below
        */
        if(in_array($domain[0], $countryTlds) && in_array($domain[1], $genericTlds) && $domain[2] != 'www') {
            $fullDomain = $domain[2] . '.' . $domain[1] . '.' . $domain[0];
        }
        else {
            $fullDomain = $domain[1] . '.' . $domain[0];;
        }

        return $fullDomain;
    }

    public static function isValidIpV4Address($ipAddress) {
        $return = true;
        $temp = explode(".", $ipAddress);
        if(count($temp) < 4) {
            $return = false;
        }
        else {
            foreach($temp as $sub) {
                if($return != false) {
                    if(!eregi("^([0-9])", $sub)) {
                        $return = false;
                    }
                    else {
                        $return = true;
                    }
                }
            }
        }
        return $return;
    }

    public static function getUrlContent($url, $postData = null) {
        // Handle objects and arrays
        $curlHandler = curl_init();
        curl_setopt($curlHandler, CURLOPT_URL, $url);
        curl_setopt($curlHandler, CURLOPT_FAILONERROR, 1);
        curl_setopt($curlHandler, CURLOPT_TIMEOUT, 20); // Time out in seconds
        curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, 1);
        if($postData != null) {
            foreach($postData as $key => &$value) {
                if(is_object($value) || is_array($value)) {
                    $value = json_encode($value);
                }
            }
            curl_setopt($curlHandler, CURLOPT_POSTFIELDS, $postData);
        }
        $request = curl_exec($curlHandler);

        if(!$request) {
            $response = array('status' => 'failure', 'response' => 'CURL error '.curl_errno($curlHandler).': '.curl_error($curlHandler));
        }
        else {
            $response = array('status' => 'success', 'response' => $request);
        }

        return $response;
    }
}

?>
