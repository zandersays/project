<?php
/**
 * Abstraction for a cUrl request.
 *
 * @author Kam Sheffield
 * @version 09/28/2011
 */
class WebRequest {

    const Get = 'GET';

    const Post = 'POST';

    const Put = 'PUT';

    const Delete = 'DELETE';

    /**
     *
     * @var string
     */
    protected $url;

    /**
     *
     * @var string
     */
    protected $method;

    /**
     *
     * @var array
     */
    protected $headers;

    /**
     *
     * @var string
     */
    protected $username;

    /**
     *
     * @var string
     */
    protected $password;

    /**
     *
     * @var array
     */
    protected $variables;

    /**
     *
     * @var array
     */
    protected $cookies;
    
    /**
     *
     * @var array
     */
    protected $postVariables;    
    
    /**
     *
     * @var string
     */
    protected $body;
    
    /**
     *
     * @var int
     */
    protected $failOnError;
    
    /**
     *
     * @var int
     */
    protected $timeOutInSeconds;
    
    /**
     *
     * @var int
     */
    protected $returnTransfer;
    
    /**
     *
     * @var string
     */
    protected $referrer;

    /**
     *
     * @param string $url
     * @param int $method
     */
    public function __construct($url = '', $method = WebRequest::Get) {
        $this->url = $url;
        $this->method = String::upper($method);
        $this->headers = array();
        $this->username = null;
        $this->password = null;
        $this->variables = array();
        $this->body = array();
        $this->failOnError = 0;
        $this->timeOutInSeconds = 20;
        $this->returnTransfer = 1;
        $this->referrer = null;
        $this->cookies = array();
        $this->postVariables = array();
    }
    
    public function setPostVariables($postVariables) {
        $this->postVariables = $postVariables;
    }
    
    public function getPostVariables() {
        return $this->postVariables;
    }
    
    public function setCookies($cookies) {
        $this->cookies = $cookies;
    }
    
    public function getCookies() {
        return $this->cookies;
    }
    
    public function addCookie($cookie) {
        $this->cookies[] = $cookie;
        
        return $this;
    }
    
    public function setReferrer($referrer) {
        $this->referrer = $referrer;
    }
    
    public function getReferrer() {
        return $this->referrer;
    }
    
    public function setReturnTransfer($returnTransfer) {
        $this->returnTransfer = $returnTransfer;
    }
    
    public function getReturnTransfer() {
        return $this->returnTransfer;
    }
    
    public function setTimeoutInSeconds($timeOutInSeconds) {
        $this->timeOutInSeconds = $timeOutInSeconds;
    }
    
    public function getTimeoutInSeconds() {
        return $this->timeOutInSeconds;
    }
    
    public function getFailOnError() {
        return $this->failOnError;
    }
    
    public function setFailOnError($failOnError) {
        $this->failOnError = $failOnError();
    }
    
    public function getUrl() {
        return $this->url;
    }

    public function setUrl($url) {
        $this->url = $url;
    }

    public function getMethod() {
        return $this->method;
    }

    public function setMethod($method) {
        $this->method = $method;
    }

    public function getHeader($header) {
        if(array_key_exists($header, $this->headers)) {
            return $this->headers[$header];
        }
        return null;
    }

    public function addHeader($header, $value) {
        $this->headers[$header] = $value;
    }

    public function getHeaders() {
        return $this->headers;
    }

    public function setHeaders(Array $headers) {
        $this->headers = $headers;
    }

    public function getVariable($variable) {
        if(array_key_exists($variable, $this->variables)) {
            return $this->variables[$variable];
        }
        return null;
    }

    public function setVariable($variable, $value) {
        $this->variables[$variable] = $value;
    }

    public function getVariables() {
        return $this->variables;
    }
    
    public function setVariables($requestVariables, $removeEmptyVariables = false) {
        // Remove any null variables
        if($removeEmptyVariables) {
            $requestVariables = Arr::filter($requestVariables);
        }
                
        $this->variables = $requestVariables;    
    }

    public function getUsername() {
        return $this->username;
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function getPassword() {
        return $this->password;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function getBody() {
        return $this->body;
    }

    public function setBody($body) {
        $this->body = $body;
    }

    protected function createQueryString(Array $variables) {
        $string = '';
        
        foreach($variables as $key => $value) {
            $string .= '&'.$key.'='.$value;
        }
        
        $string = String::replace('&', '?', $string, 1);
       
        return $string;
    }

    /**
     *
     * @return WebResponse
     */
    public function execute() {
        // an array to hold all the cUrl options
        $optionsArray = array();

        // init cUrl
        $curl = curl_init();

        // set the method and the request body
        switch($this->method) {
            case WebRequest::Delete:
                $optionsArray[CURLOPT_CUSTOMREQUEST] = 'DELETE';
                break;

            case WebRequest::Post:
                $optionsArray[CURLOPT_POST] = true;
                $optionsArray[CURLOPT_POSTFIELDS] = $this->body;
                break;

            case WebRequest::Put:
                $optionsArray[CURLOPT_PUT] = true;

                // for a curl put we have to create a file for it to 'PUT'
                $encodedContent = $this->body;
                if (empty($encodedContent)) {
                    $encodedContent = '';
                }
                    
                    // create the file to put
                    $tempFile = fopen('php://temp/maxmemory:256000', 'w');
                    if (!$tempFile) {
                        throw new Exception('Error creating temp file for put operation');
                    }
                    if (!fwrite($tempFile, $encodedContent)) {
                        throw new Exception('Error creating temp file for put operation');
                    }
                    if (fseek($tempFile, 0) == -1) {
                        throw new Exception('Error creating temp file for put operation');
                    }

                    $optionsArray[CURLOPT_BINARYTRANSFER] = true;
                    $optionsArray[CURLOPT_INFILE] = $tempFile;
                    $optionsArray[CURLOPT_INFILESIZE] = strlen($encodedContent);
                
                
                break;

            default:
                $optionsArray[CURLOPT_HTTPGET] = true;
                break;
        }

        // Set the query string variables if we need to
        if(count($this->variables) > 0) {
            $this->url .= $this->createQueryString($this->variables);
        }

        // set all of the base options
        $optionsArray[CURLOPT_URL] = $this->url;
        $optionsArray[CURLOPT_FAILONERROR] = $this->failOnError;
        $optionsArray[CURLOPT_TIMEOUT] = $this->timeOutInSeconds;
        $optionsArray[CURLOPT_FOLLOWLOCATION] = true;
        $optionsArray[CURLOPT_RETURNTRANSFER] = $this->returnTransfer;
        $optionsArray[CURLOPT_HEADER] = 1;
        $optionsArray[CURLINFO_HEADER_OUT] = true;
                
        // Conditionally set the referrer
        if($this->referrer != null) {
            $optionsArray[CURLOPT_REFERER] = $this->referrer;
        }
        
        // Conditionally set cookies
        if(!empty($this->cookies)) {
            $cookieString = '';
            foreach($this->cookies as $key => $value) {
                if(Arr::is($value)) {
                    $cookieString = $key.'='.$value['value'].'; ';
                }
                else {
                    $cookieString = $key.'='.$value.'; ';
                }
            }
            if(!empty($cookieString)) {
                $optionsArray[CURLOPT_COOKIE] = $cookieString;
            }
        }
        
        // Conditionally set post variables
        if(!empty($this->postVariables)) {
            $optionsArray[CURLOPT_POSTFIELDS] = $this->postVariables;
        }

        // Set a username and password
        if($this->username != null && $this->password != null) {
            $optionsArray[CURLOPT_USERPWD] = $this->username.':'.$this->password;
        }

        // Set the headers
        if($this->headers != null && count($this->headers) > 0) {
            // build the headers the way curl wants them...
            $curlHeaders = array();
            foreach($this->headers as $key => $value) {
                $curlHeaders[] = $key.': '.$value;
            }
            $optionsArray[CURLOPT_HTTPHEADER] = $curlHeaders;
        }
        
        // Set all of the options
        curl_setopt_array($curl, $optionsArray);
        
        // Run the request        
        $response = curl_exec($curl);
        
        
        
        // Get the meta info
        $curlErrorNumber = curl_errno($curl);
        $curlError = curl_error($curl);
        $curlInfoArray = curl_getinfo($curl);
        
        
        
        // Figure out where the header ends
        $headerEndPosition = $curlInfoArray['header_size'];
        
        // Separate the header from the body
        $responseHeader = String::sub($response, 0, $headerEndPosition);
        $responseBody = String::sub($response, $headerEndPosition, String::length($response));

        // Close curl
        curl_close($curl);
        

        return new WebResponse($curlErrorNumber, $curlError, $curlInfoArray, $responseHeader, $responseBody);
    }
}

?>
