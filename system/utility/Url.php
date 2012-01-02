<?php

class Url {
    
    public $domain;
    public $baseDomain;
    public $isBaseDomainValid;
    public $subDomain;
    public $path;
    public $port;
    public $protocol;
    public $url;              // the properly formatted url
    public $urlString;        // the actual url string that was passed into the constructor
    public $isWww;            // whether or not the original string has a www in it

    /**
     * Creates a new instance of an Url.
     * This call rejects any Url that
     * contains an IP address or file:///
     * as the prepended protocol.  If the string
     * provided cannot be parsed into a valid
     * Url an exception is thrown.
     *
     * @param string $url The string to parse into a Url object.
     * @exception If the $url cannot be parsed into a valid Url object.
     * @return Url A new Url object.
     */
    public function  __construct($url) {

        // set this field first
        $this->urlString = $url;

        // convert the url to lower case and trim white space to prevent any weirdness
        $url = strtolower($url);
        $url = trim($url);

        // check for an empty string
        if(empty($url)){
            throw new Exception('The argument provided cannot be an empty string');
        }

        // check to see if the url contains an ip address
        if($this->isIpAddress($url)){
            throw new Exception('The argument provided cannot contain an ip address: ' . $this->urlString);
        }

        // make the url valid if necessary and parse it
        $url = $this->makeValidUrl($url);
        $parsedUrl = parse_url($url);

        // populate all of the fields 
        if(array_key_exists(('host'), $parsedUrl)){
            $this->domain = $this->trimWww($parsedUrl['host']);
            $splitDomain = $this->splitBaseAndSubDomains($this->domain);
            $this->baseDomain = $splitDomain['baseDomain'];
            $this->subDomain = $splitDomain['subDomain'];
        }
        else{
            $this->domain = '';
            $this->baseDomain = '';
            $this->subDomain = '';
        }

        if(array_key_exists('path', $parsedUrl)){
            $this->path = $parsedUrl['path'];
        }
        else{
            $this->path = '';
        }

        if(array_key_exists('port', $parsedUrl)){
            $this->port = $parsedUrl['port'];
        }
        else{
            $this->port = 80;
        }

        if(array_key_exists('scheme', $parsedUrl)){
            $this->protocol = $parsedUrl['scheme'];
        }
        else{
            $this->protocol = 'http';
        }

        $this->isBaseDomainValid = $this->validateBaseDomain();

        $this->url = $this->__toString();

        return $this;
    }

    /**
     * Checks to see if there is an ip address
     * in the url string.  If so true is returned,
     * false otherwise.
     *
     * @param string $url
     * @return bool
     */
    private function isIpAddress($url){

        // check for a host property
        $parsedUrl = parse_url($url);
        if(array_key_exists('host', $parsedUrl)){
            $url = $parsedUrl['host'];
        }

        // explode on the dots and see if they are all numbers
        $explodedUrl = explode('.', $url);
        $count = 0;
        if(count($explodedUrl) == 4){
            foreach($explodedUrl as $digit){
                if(!is_numeric($digit)){
                    break;
                }
                else{
                    $count++;
                }
            }
        }

        // check the count of numerics found if four its a ip!
        if($count == 4){
            return true;
        }
        else{
            return false;
        }
    }

    /**
     * Checks to see if the url is invalid,
     * if it is an exception is thrown.  Otherwise
     * it tries to make the url valid and returns it.
     *
     * @param string $url
     * @return string
     * @exception If the $url does not contain a . or contains file://
     */
    private function makeValidUrl($url){

        // make sure there is a dot in the string
        if(strpos($url, '.') === false){
            throw new Exception('No delimeter (\'.\') between the base domain and top level domain was provided : ' . $this->urlString);
        }

        // make sure there is no file in the protocol
        if(strpos($url, 'file://') !== false || strpos($url, 'file://') !== false){
            throw new Exception('File parameter not allowed in the url: ' . $this->urlString);
        }

        // check to see if the protocol has been specified
        if(strpos($url, 'http://') === false && strpos($url, 'https://') === false && strpos($url, 'ftp://') === false){
            return 'http://' . $url;
        }

        return $url;
    }

    /**
     * Trims the prepended www from the url.
     *
     * @param string $url
     * @return string
     */
    private function trimWww($url){

        $wwwIndex = strpos($url, 'www');
        if($wwwIndex === false){
            $this->isWww = false;
            return $url;
        }
        else {
            $this->isWww = true;
            return substr_replace($url, '', $wwwIndex, 4);
        }
    }

    /**
     * Get the base domain of a url.
     * Based on : http://phosphorusandlime.blogspot.com/2007/08/php-get-base-domain.html
     *
     * @param string $fullDomain
     * @return string The base domain
     * @exception If the url does not contain a valid TLD
     */
    private function splitBaseAndSubDomains($fullDomain) {

        // generic tlds (source: http://en.wikipedia.org/wiki/Generic_top-level_domain)
        $G_TLD = array(
        'biz','com','edu','gov','info','int','mil','name','net','org',
        'aero','asia','cat','coop','jobs','mobi','museum','pro','tel','travel',
        'arpa','root',
        'berlin','bzh','cym','gal','geo','kid','kids','lat','mail','nyc','post','sco','web','xxx',
        'nato',
        'example','invalid','localhost','test',
        'bitnet','csnet','ip','local','onion','uucp',
        'co'   // note: not technically, but used in things like co.uk
        );

        // country tlds (source: http://en.wikipedia.org/wiki/Country_code_top-level_domain)
        $C_TLD = array(
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

        // break up domain, reverse
        $explodedDomain = explode('.', $fullDomain);
        $explodedDomain = array_reverse($explodedDomain);

        // if only 2 domain parts, that must be our domain
        if (count($explodedDomain) <= 2) {
            if(in_array($explodedDomain[0], $C_TLD) || in_array($explodedDomain[0], $G_TLD)){
                return array('baseDomain' => $fullDomain, 'subDomain' => '');
            }
            else{
                throw new Exception("A valid top level domain was not provided: " . $this->urlString);
            }
        }

        /*
            finally, with 3+ domain parts: obviously D0 is tld
            now, if D0 = ctld and D1 = gtld, we might have something like com.uk
            so, if D0 = ctld && D1 = gtld && D2 != 'www', domain = D2.D1.D0
            else if D0 = ctld && D1 = gtld && D2 == 'www', domain = D1.D0
            else domain = D1.D0
            these rules are simplified below
        */
        if (in_array($explodedDomain[0], $C_TLD) && in_array($explodedDomain[1], $G_TLD) && $explodedDomain[2] != 'www'){
            $baseDomain = $explodedDomain[2] . '.' . $explodedDomain[1] . '.' . $explodedDomain[0];
            $subDomain = $this->getSubDomain($explodedDomain, 3);
            return array('baseDomain' => $baseDomain, 'subDomain' => $subDomain);
        }
        else if(in_array($explodedDomain[0], $C_TLD) || in_array($explodedDomain[0], $G_TLD)){
            $baseDomain = $explodedDomain[1] . '.' . $explodedDomain[0];
            $subDomain = $this->getSubDomain($explodedDomain, 2);
            return array('baseDomain' => $baseDomain, 'subDomain' => $subDomain);
        }

        throw new Exception("A valid top level domain was not provided: " . $this->urlString);
    }

    /**
     * Returns the remaining strings in
     * a array of domain strings as one string.
     *
     * @param array $domainArray
     * @param int $startIndex
     * @return string
     */
    private function getSubDomain($domainArray, $startIndex){

        // loop through the remaining entries in the array
        // an concatenate them together
        $subDomain = '';
        for($i = $startIndex; $i < count($domainArray); $i++){
            if(empty($subDomain)){
                $subDomain = $domainArray[$i];
            }
            else{
                $subDomain = $domainArray[$i] . '.' . $subDomain;
            }
        }
        return $subDomain;
    }

    /**
     * Checks to see if the base domain is valid
     * for this url.
     *
     * @return bool
     */
    private function validateBaseDomain(){

            // Make sure there is something in the domain
            if(empty($this->baseDomain)) {
                return false;
            }

            // Check to see if the host resolves
            $host = gethostbyname($this->baseDomain);
            if($host == $this->baseDomain) {
                return false;
            }

            // Make sure the host doesn't resolve to OpenDNS
            if($host == '208.67.219.132') {
                return false;
            }

            return true;
    }
    
    public static function is($string) {
        return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $string);
    }

    public static function decode($string) {
        return urldecode($string);
    }

    public static function encode($string) {
        return urlencode($string);
    }
    
    public static function current($options = array()) {
        if(isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
            $protocol = 'https://';
        }
        else {
            $protocol = 'http://';
        }
        $currentUrl = $protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $parts = parse_url($currentUrl);

        $query = '';
        if(!empty($parts['query'])) {
            $parameters = explode('&', $parts['query']);
            $retainedParameters = array();
            foreach($parameters as $parameter) {
                // Drop any parameters if the parametersToStrip option is used
                if(isset($options['parametersToStrip'])) {
                    if(!Arr::contains(String::sub($parameter, 0, String::indexOf('=', $parameter)), $options['parametersToStrip'])) {
                        echo 'Keeping: '.$parameter;
                        $retainedParameters[] = $parameter;
                    }
                }
                else {
                    $retainedParameters[] = $parameter;
                }
            }

            if(!empty($retainedParameters)) {
                $query = '?'.implode($retainedParameters, '&');
            }
        }

        // Use port if non default
        $port =
                isset($parts['port']) &&
                (($protocol === 'http://' && $parts['port'] !== 80) ||
                ($protocol === 'https://' && $parts['port'] !== 443)) ? ':'.$parts['port'] : '';

        // Rebuild
        return $protocol.$parts['host'].$port.$parts['path'].$query;
    }
    
    public static function removeQueryParameter($parameter, $url) {
        $url = preg_replace('/(.*)(?|&)'.$parameter.'=[^&]+?(&)(.*)/i', '$1$2$4', $url.'&');
        $url = substr($url, 0, -1);
        return $url;
    }

    /**
     * The url as a string.
     *
     * @return string
     */
    public function  __toString() {

        $www = '';
        if($this->isWww){
            $www = 'www.';
        }

        if($this->port == 80){
            return $this->protocol . '://' . $www . $this->domain . $this->path . '/';
        }
        else{
            return $this->protocol . '://' . $www . $this->domain . ':' . $this->port . $this->path . '/';
        }
    }
}
?>