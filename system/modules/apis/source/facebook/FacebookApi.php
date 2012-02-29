<?php
class FacebookApi extends Api {

    public $commands = array(
        'dealerMoreInfo' => array(
            'arguments' => array(
                array('name' => 'name', 'required' => true),
                array('name' => 'email', 'required' => true),
                array('name' => 'phone', 'required' => true),
            ),
        ),
    );
        
    /**
     * The Application ID.
     *
     * @var string
     */
    public static $appId;

    /**
     * The Application API Secret.
     *
     * @var string
     */
    public static $apiSecret;

    /**
     * The data from the signed_request token.
     */
    public static $signedRequest;

    /**
     * A CSRF state variable to assist in the defense against CSRF attacks.
     */
    public static $state;

    /**
     * The OAuth access token received in exchange for a valid authorization
     * code. null means the access token has yet to be determined.
     *
     * @var string
     */
    public static $accessToken = null;

    /**
     * Indicates if the CURL based @ syntax for file uploads is enabled.
     *
     * @var boolean
     */
    public static $fileUploadSupport = false;
    
    public static $domainMap = array(
        'api' => 'https://api.facebook.com/',
        'api_video' => 'https://api-video.facebook.com/',
        'api_read' => 'https://api-read.facebook.com/',
        'graph' => 'https://graph.facebook.com/',
        'www' => 'https://www.facebook.com/',
    );
    
    public static function initialize($settings) {
        // Get the credentials for the current instance type (conditionally)
        $credentials = $settings['credentials'][String::lowerFirstCharacter(Project::getInstanceType())];
        
        // Bind the state and access token variables to the session
        self::$state = &$_SESSION['facebookApiState'];
        self::$accessToken = &$_SESSION['facebookApiAccessToken'];
        
        // Populate values from provided settings
        self::$appId = isset($credentials['appId']) ? $credentials['appId'] : self::$appId;
        self::$apiSecret = isset($credentials['apiSecret']) ? $credentials['apiSecret'] : self::$apiSecret;
        self::$accessToken = isset($credentials['accessToken']) ? $credentials['accessToken'] : self::getAccessToken();
    }
    
    /**
     * Determines the access token that should be used for API calls.
     * The first time this is called, self::$accessToken is set equal
     * to either a valid user access token, or it's set to the application
     * access token if a valid user access token wasn't available. Subsequent
     * calls return whatever the first call returned.
     *
     * @return string The access token
     */
    public static function getAccessToken() {
        if(self::$accessToken !== null && self::$accessToken != self::getApplicationAccessToken()) {
            // We've done this already and cached it. Just return.
            return self::$accessToken;
        }

        // First establish access token to be the application
        // access token, in case we navigate to the /oauth/access_token
        // endpoint, where SOME access token is required.
        self::$accessToken = self::getApplicationAccessToken();
        $userAccessToken = self::getUserAccessToken();
        if($userAccessToken) {
            self::$accessToken = $userAccessToken;
        }

        return self::$accessToken;
    }
    
    
    /**
     * Determines and returns the user access token, first using
     * the signed request if present, and then falling back on
     * the authorization code if present.  The intent is to
     * return a valid user access token, or false if one is determined
     * to not be available.
     *
     * @return string A valid user access token, or false if one
     *                could not be determined.
     */
    public static function getUserAccessToken() {
        // first, consider a signed request if it's supplied.
        // if there is a signed request, then it alone determines
        // the access token.
        $signedRequest = self::getSignedRequest();
        if($signedRequest) {
            // apps.facebook.com hands the access_token in the signed_request
            if(array_key_exists('oauth_token', $signedRequest)) {
                $accessToken = $signedRequest['oauth_token'];
                return $accessToken;
            }

            // The JS SDK puts a code in with the redirect_uri of ''
            if(array_key_exists('code', $signedRequest)) {
                $code = $signedRequest['code'];
                $accessToken = self::getAccessTokenFromCode($code, '');
                if($accessToken) {
                    return $accessToken;
                }
            }

            // signed request states there's no access token, so anything
            // stored should be cleared.
            return false; // respect the signed request's data, even
            // if there's an authorization code or something else
        }

        $code = self::getCode();
        if($code) {
            $accessToken = self::getAccessTokenFromCode($code);
            if($accessToken) {
                return $accessToken;
            }

            // code was bogus, so everything based on it should be invalidated.
            return false;
        }

        return false;
    }
    
        


    /**
     * Retrieve the signed request, either from a request parameter or,
     * if not present, from a cookie.
     *
     * @return string the signed request, if available, or null otherwise.
     */
    public static function getSignedRequest() {
        if(!self::$signedRequest) {
            if(isset($_REQUEST['signed_request'])) {
                self::$signedRequest = self::parseSignedRequest($_REQUEST['signed_request']);
            }
            else if(isset($_COOKIE[self::getSignedRequestCookieName()])) {
                self::$signedRequest = self::parseSignedRequest($_COOKIE[self::getSignedRequestCookieName()]);
            }
        }
        
        return self::$signedRequest;
    }

    /**
     * Determines the connected user by first examining any signed
     * requests, then considering an authorization code, and then
     * falling back to any persistent store storing the user.
     *
     * @return integer The id of the connected Facebook user,
     *                 or 0 if no such user exists.
     */
    public static function getUserFromAvailableData() {
        // if a signed request is supplied, then it solely determines
        // who the user is.
        $signed_request = self::getSignedRequest();
        if($signed_request) {
            if(array_key_exists('user_id', $signed_request)) {
                $user = $signed_request['user_id'];
                self::setPersistentData('user_id', $signed_request['user_id']);
                return $user;
            }

            // if the signed request didn't present a user id, then invalidate
            // all entries in any persistent store.
            return 0;
        }

        $user = self::getPersistentData('user_id', $default = 0);
        $persisted_access_token = self::getPersistentData('access_token');

        // use access_token to fetch user id if we have a user access_token, or if
        // the cached access token has changed.
        $access_token = self::getAccessToken();
        if($access_token &&
                $access_token != self::getApplicationAccessToken() &&
                !($user && $persisted_access_token == $access_token)) {
            $user = self::getUserIdFromAccessToken();
        }

        return $user;
    }

    /**
     * Get a Login URL for use with redirects. By default, full page redirect is
     * assumed. If you are using the generated URL with a window.open() call in
     * JavaScript, you can pass in display=popup as part of the $parameters.
     *
     * The parameters:
     * - redirect_uri: the url to go to after a successful login
     * - scope: comma separated list of requested extended perms
     *
     * @param array $parameters Provide custom parameters
     * @return string The URL for the login flow
     */
    public static function getLoginUrl($parameters=array()) {
        self::establishCsrfTokenState();
        $currentUrl = self::getCurrentUrl();

        // if 'scope' is passed as an array, convert to comma separated list
        $scopeParams = isset($parameters['scope']) ? $parameters['scope'] : null;
        if($scopeParams && is_array($scopeParams)) {
            $parameters['scope'] = implode(',', $scopeParams);
        }

        return self::getUrl(
                        'www', 'dialog/oauth', array_merge(array(
                            'client_id' => self::$appId,
                            'redirect_uri' => $currentUrl, // possibly overwritten
                            'state' => self::$state), $parameters));
    }

    /**
     * Get a Logout URL suitable for use with redirects.
     *
     * The parameters:
     * - next: the url to go to after a successful logout
     *
     * @param array $parameters Provide custom parameters
     * @return string The URL for the logout flow
     */
    public static function getLogoutUrl($parameters=array()) {
        return self::getUrl(
                        'www', 'logout.php', array_merge(array(
                            'next' => self::getCurrentUrl(),
                            'access_token' => self::getAccessToken(),
                                ), $parameters)
        );
    }

    /**
     * Get a login status URL to fetch the status from Facebook.
     *
     * The parameters:
     * - ok_session: the URL to go to if a session is found
     * - no_session: the URL to go to if the user is not connected
     * - no_user: the URL to go to if the user is not signed into facebook
     *
     * @param array $parameters Provide custom parameters
     * @return string The URL for the logout flow
     */
    public static function getLoginStatusUrl($parameters=array()) {
        return self::getUrl(
                        'www', 'extern/login_status.php', array_merge(array(
                            'api_key' => self::$appId,
                            'no_session' => self::getCurrentUrl(),
                            'no_user' => self::getCurrentUrl(),
                            'ok_session' => self::getCurrentUrl(),
                            'session_version' => 3,
                                ), $parameters)
        );
    }

    /**
     * Make an API call.
     *
     * @return mixed The decoded response
     */
    public static function api(/* polymorphic */) {
        $arguments = func_get_args();
        if(is_array($arguments[0])) {
            return self::restServer($arguments[0]);
        }
        else {
            return call_user_func_array(array('FacebookApi', 'graph'), $arguments);
        }
    }

    /**
     * Constructs and returns the name of the cookie that
     * potentially houses the signed request for the app user.
     * The cookie is not set by the BaseFacebook class, but
     * it may be set by the JavaScript SDK.
     *
     * @return string the name of the cookie that would house
     *         the signed request value.
     */
    public static function getSignedRequestCookieName() {
        return 'facebookSignedRequest_'.self::$appId;
    }

    /**
     * Get the authorization code from the query parameters, if it exists,
     * and otherwise return false to signal no authorization code was
     * discoverable.
     *
     * @return mixed The authorization code, or false if the authorization
     *               code could not be determined.
     */
    public static function getCode() {
        if(isset($_REQUEST['code'])) {
            //echo 'Calling getCode<br />';
            //echo self::$state.' vs '.$_REQUEST['state'];
            if(self::$state !== null && isset($_REQUEST['state']) && self::$state === $_REQUEST['state']) {
                // CSRF state has done its job, so clear it
                self::$state = null;
                return $_REQUEST['code'];
            }
            else {
                //self::logError('CSRF state token does not match one provided.');
                return false;
            }
        }

        return false;
    }

    /**
     * Retrieves the UID with the understanding that
     * self::$accessToken has already been set and is
     * seemingly legitimate.  It relies on Facebook's Graph API
     * to retrieve user information and then extract
     * the user ID.
     *
     * @return integer Returns the UID of the Facebook user, or 0
     *                 if the Facebook user could not be determined.
     */
    public static function getUserIdFromAccessToken() {
        try {
            $user = self::api('/me');
            return $user['id'];
        }
        catch(FacebookApiException $e) {
            return 0;
        }
    }
    
    public static function getUser($user = 'me') {
        return FacebookApi::api('/'.$user);
    }
    
    /**
     * Returns the access token that should be used for logged out
     * users when no authorization code is available.
     *
     * @return string The application access token, useful for gathering
     *                public information about users and applications.
     */
    public static function getApplicationAccessToken() {
        return self::$appId.'|'.self::$apiSecret;
    }

    /**
     * Lays down a CSRF state token for this process.
     *
     * @return void
     */
    public static function establishCsrfTokenState() {
        if(self::$state === null) {
            self::$state = md5(uniqid(mt_rand(), true));
        }
    }

    /**
     * Retrieves an access token for the given authorization code
     * (previously generated from www.facebook.com on behalf of
     * a specific user).  The authorization code is sent to graph.facebook.com
     * and a legitimate access token is generated provided the access token
     * and the user for which it was generated all match, and the user is
     * either logged in to Facebook or has granted an offline access permission.
     *
     * @param string $code An authorization code.
     * @return mixed An access token exchanged for the authorization code, or
     *               false if an access token could not be generated.
     */
    public static function getAccessTokenFromCode($code, $redirectUrl = null) {
        if(empty($code)) {
            return false;
        }

        if($redirectUrl === null) {
            $redirectUrl = self::getCurrentUrl();
        }

        try {
            // need to circumvent json_decode by calling oAuthRequest
            // directly, since response isn't JSON format.
            $accessTokenResponse =
                    self::oAuthRequest(
                    self::getUrl('graph', '/oauth/access_token'), $parameters = array('client_id' => self::$appId,
                'client_secret' => self::$apiSecret,
                'redirect_uri' => $redirectUrl,
                'code' => $code));
        }
        catch(FacebookApiException $e) {
            // most likely that user very recently revoked authorization.
            // In any event, we don't have an access token, so say so.
            echo 'Auth revoked';
            return false;
        }

        if(empty($accessTokenResponse)) {
            return false;
        }

        $response_params = array();
        parse_str($accessTokenResponse, $response_params);
        if(!isset($response_params['access_token'])) {
            return false;
        }

        return $response_params['access_token'];
    }

    /**
     * Invoke the old restserver.php endpoint.
     *
     * @param array $parameters Method call object
     *
     * @return mixed The decoded response object

     */
    public static function restServer($parameters) {
        // generic application level parameters
        $parameters['api_key'] = self::$appId;
        $parameters['format'] = 'json-strings';

        $result = Json::decode(self::oAuthRequest(self::getApiUrl($parameters['method']), $parameters), true);

        // results are returned, errors are thrown
        if(is_array($result) && isset($result['error_code'])) {
            $result = array(
                'status' => 'failure',
                'response' => $result['error']['message'],
                'errorCode' => $result['error_code'],
            );
        }
        else {
            $result = array(
                'status' => 'success',
                'response' => Arr::underscoreKeysToCamelCase($result),
            );
        }

        if($parameters['method'] === 'auth.expireSession' || $parameters['method'] === 'auth.revokeAuthorization') {
            self::destroySession();
        }

        return $result;
    }

    /**
     * Invoke the Graph API.
     *
     * @param string $path The path (required)
     * @param string $method The http method (default 'GET')
     * @param array $parameters The query/post data
     *
     * @return mixed The decoded response object

     */
    public static function graph($path, $method = 'GET', $parameters = array()) {
        if(is_array($method) && empty($parameters)) {
            $parameters = $method;
            $method = 'GET';
        }
        $parameters['method'] = $method; // method override as we always do a POST

        $result = Json::decode(self::oAuthRequest(self::getUrl('graph', $path), $parameters), true);

        // Results are returned, errors are thrown
        if(is_array($result) && isset($result['error'])) {
            $result = array(
                'status' => 'failure',
                'response' => $result['error']['message'],
            );
        }
        else {
            $result = array(
                'status' => 'success',
                'response' => Arr::underscoreKeysToCamelCase($result),
            );
        }

        return $result;
    }

    /**
     * Make a OAuth Request.
     *
     * @param string $url The path (required)
     * @param array $parameters The query/post data
     *
     * @return string The decoded response object

     */
    public static function oAuthRequest($url, $parameters) {
        if(!isset($parameters['access_token'])) {
            $parameters['access_token'] = self::getAccessToken();
        }

        // json_encode all params values that are not strings
        foreach($parameters as $key => $value) {
            if(!is_string($value)) {
                $parameters[$key] = json_encode($value);
            }
        }

        return self::makeRequest($url, $parameters);
    }

    /**
     * Makes an HTTP request. This method can be overridden by subclasses if
     * developers want to do fancier things or use something other than curl to
     * make the request.
     *
     * @param string $url The URL to make the request to
     * @param array $parameters The parameters to use for the POST body
     * @param CurlHandler $ch Initialized curl handle
     *
     * @return string The response text
     */
    public static function makeRequest($url, $parameters, $ch=null) {
        //echo 'Calling: '.$url.'<br />';
        
        if(!$ch) {
            $ch = curl_init();
        }
        
        $CURL_OPTS = array(
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_USERAGENT => 'facebook-php-3.1',
        );

        $opts = $CURL_OPTS;
        if(self::$fileUploadSupport) {
            $opts[CURLOPT_POSTFIELDS] = $parameters;
        }
        else {
            $opts[CURLOPT_POSTFIELDS] = http_build_query($parameters, null, '&');
        }
        $opts[CURLOPT_URL] = $url;

        // disable the 'Expect: 100-continue' behaviour. This causes CURL to wait
        // for 2 seconds if the server does not support this header.
        if(isset($opts[CURLOPT_HTTPHEADER])) {
            $existing_headers = $opts[CURLOPT_HTTPHEADER];
            $existing_headers[] = 'Expect:';
            $opts[CURLOPT_HTTPHEADER] = $existing_headers;
        }
        else {
            $opts[CURLOPT_HTTPHEADER] = array('Expect:');
        }

        curl_setopt_array($ch, $opts);
        $result = curl_exec($ch);

        if(curl_errno($ch) == 60) { // CURLE_SSL_CACERT
            self::logError('Invalid or no certificate authority found, '.
                    'using bundled information');
            curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__).'/fb_ca_chain_bundle.crt');
            $result = curl_exec($ch);
        }

        if($result === false) {
            $e = new FacebookApiException(array(
                        'error_code' => curl_errno($ch),
                        'error' => array(
                            'message' => curl_error($ch),
                            'type' => 'CurlException',
                        ),
                    ));
            curl_close($ch);
            throw $e;
        }
        curl_close($ch);
        
        //echo 'Returned: <br />';
        //print_r($result);
        //echo '<br /><br />';
        
        return $result;
    }

    /**
     * Parses a signed_request and validates the signature.
     *
     * @param string $signedRequest A signed token
     * @return array The payload inside it or null if the sig is wrong
     */
    public static function parseSignedRequest($signedRequest) {
        list($encodedSignature, $payload) = explode('.', $signedRequest, 2);

        // decode the data
        $signature = self::base64UrlDecode($encodedSignature);
        $data = Json::decode(self::base64UrlDecode($payload), true);

        if(strtoupper($data['algorithm']) !== 'HMAC-SHA256') {
            self::logError('Unknown algorithm. Expected HMAC-SHA256');
            return null;
        }

        // check sig
        $expected_sig = hash_hmac('sha256', $payload, self::$apiSecret, $raw = true);
        if($signature !== $expected_sig) {
            self::logError('Bad Signed JSON signature!');
            return null;
        }

        return $data;
    }

    /**
     * Build the URL for api given parameters.
     *
     * @param $method String the method name.
     * @return string The URL for the given parameters
     */
    public static function getApiUrl($method) {
        static $READ_ONLY_CALLS =
        array('admin.getallocation' => 1,
            'admin.getappproperties' => 1,
            'admin.getbannedusers' => 1,
            'admin.getlivestreamvialink' => 1,
            'admin.getmetrics' => 1,
            'admin.getrestrictioninfo' => 1,
            'application.getpublicinfo' => 1,
            'auth.getapppublickey' => 1,
            'auth.getsession' => 1,
            'auth.getsignedpublicsessiondata' => 1,
            'comments.get' => 1,
            'connect.getunconnectedfriendscount' => 1,
            'dashboard.getactivity' => 1,
            'dashboard.getcount' => 1,
            'dashboard.getglobalnews' => 1,
            'dashboard.getnews' => 1,
            'dashboard.multigetcount' => 1,
            'dashboard.multigetnews' => 1,
            'data.getcookies' => 1,
            'events.get' => 1,
            'events.getmembers' => 1,
            'fbml.getcustomtags' => 1,
            'feed.getappfriendstories' => 1,
            'feed.getregisteredtemplatebundlebyid' => 1,
            'feed.getregisteredtemplatebundles' => 1,
            'fql.multiquery' => 1,
            'fql.query' => 1,
            'friends.arefriends' => 1,
            'friends.get' => 1,
            'friends.getappusers' => 1,
            'friends.getlists' => 1,
            'friends.getmutualfriends' => 1,
            'gifts.get' => 1,
            'groups.get' => 1,
            'groups.getmembers' => 1,
            'intl.gettranslations' => 1,
            'links.get' => 1,
            'notes.get' => 1,
            'notifications.get' => 1,
            'pages.getinfo' => 1,
            'pages.isadmin' => 1,
            'pages.isappadded' => 1,
            'pages.isfan' => 1,
            'permissions.checkavailableapiaccess' => 1,
            'permissions.checkgrantedapiaccess' => 1,
            'photos.get' => 1,
            'photos.getalbums' => 1,
            'photos.gettags' => 1,
            'profile.getinfo' => 1,
            'profile.getinfooptions' => 1,
            'stream.get' => 1,
            'stream.getcomments' => 1,
            'stream.getfilters' => 1,
            'users.getinfo' => 1,
            'users.getloggedinuser' => 1,
            'users.getstandardinfo' => 1,
            'users.hasapppermission' => 1,
            'users.isappuser' => 1,
            'users.isverified' => 1,
            'video.getuploadlimits' => 1);
        $name = 'api';
        if(isset($READ_ONLY_CALLS[strtolower($method)])) {
            $name = 'api_read';
        }
        else if(strtolower($method) == 'video.upload') {
            $name = 'api_video';
        }
        return self::getUrl($name, 'restserver.php');
    }

    /**
     * Build the URL for given domain alias, path and parameters.
     *
     * @param $name string The name of the domain
     * @param $path string Optional path (without a leading slash)
     * @param $parameters array Optional query parameters
     *
     * @return string The URL for the given parameters
     */
    public static function getUrl($name, $path='', $parameters=array()) {
        $url = self::$domainMap[$name];
        if($path) {
            if($path[0] === '/') {
                $path = substr($path, 1);
            }
            $url .= $path;
        }
        if($parameters) {
            $url .= '?'.http_build_query($parameters, null, '&');
        }

        return $url;
    }

    /**
     * Returns the Current URL, stripping it of known FB parameters that should
     * not persist.
     *
     * @return string The current URL
     */
    public static function getCurrentUrl() {
        return Url::current(array(
            'parametersToStrip' => array(
                'code',
                'state',
                'signed_request',
            ),
        ));
    }

    /**
     * Base64 encoding that doesn't need to be urlencode()ed.
     * Exactly the same as base64_encode except it uses
     *   - instead of +
     *   _ instead of /
     *
     * @param string $input base64UrlEncoded string
     * @return string
     */
    public static function base64UrlDecode($input) {
        return base64_decode(strtr($input, '-_', '+/'));
    }

    /**
     * Destroy the current session
     */
    public static function destroySession() {
        self::$accessToken = null;
        self::$state = null;
    }

}
?>