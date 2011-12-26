<?php

/**
 * Encapsulates the cUrl errno, error, and getinfo,
 * as well as the actual response from the request.
 *
 * @author Kam Sheffield
 * @version 09/28/2011
 */
class WebResponse {
    
    /**
     *
     * @var int
     */
    protected $curlErrno;
    
    /**
     *
     * @var string
     */
    protected $curlError;
    
    /**
     *
     * @var string
     */
    protected $url;
    
    /**
     *
     * @var string
     */
    protected $contentType;
    
    /**
     *
     * @var int
     */
    protected $httpCode;
    
    /**
     *
     * @var int
     */
    protected $headerSize;
    
    /**
     *
     * @var int
     */
    protected $requestSize;
    
    /**
     *
     * @var int
     */
    protected $fileTime;
    
    /**
     *
     * @var int
     */
    protected $sslVerifyResult;
    
    /**
     *
     * @var int
     */
    protected $redirectCount;
    
    /**
     *
     * @var int
     */
    protected $totalTime;
    
    /**
     *
     * @var int
     */
    protected $nameLookupTime;
    
    /**
     *
     * @var int
     */
    protected $connectTime;
    
    /**
     *
     * @var int
     */
    protected $preTransferTime;
    
    /**
     *
     * @var int
     */
    protected $uploadSize;
    
    /**
     *
     * @var int
     */
    protected $uploadSpeed;
    
    /**
     *
     * @var int
     */
    protected $uploadContentLength;
    
    /**
     *
     * @var int 
     */
    protected $downloadSize;
        
    /**
     *
     * @var int
     */
    protected $downloadSpeed;
            
    /**
     *
     * @var int
     */
    protected $downloadContentLength;
    
    /**
     *
     * @var int
     */
    protected $startTransferTime;
    
    /**
     *
     * @var int
     */
    protected $redirectTime;
    
    /**
     *
     * @var array
     */
    protected $certInfo;
    
    /**
     *
     * @var string
     */
    protected $requestHeader;
    
    /**
     *
     * @var string
     */
    protected $response;
    
    /**
     *
     * @param int $curlErrno
     * @param string $curlError
     * @param array $curlInfoArray
     * @param string $responseString 
     */
    public function __construct($curlErrno, $curlError, $curlInfoArray, $responseString) {
        $this->curlErrno = $curlErrno;
        $this->curlError = $curlError;        
        $this->url = $curlInfoArray['url'];
        $this->contentType = $curlInfoArray['content_type'];
        $this->httpCode = $curlInfoArray['http_code'];
        $this->headerSize = $curlInfoArray['header_size'];
        $this->requestSize = $curlInfoArray['request_size'];
        $this->fileTime = $curlInfoArray['filetime'];
        $this->sslVerifyResult = $curlInfoArray['ssl_verify_result'];
        $this->redirectCount = $curlInfoArray['redirect_count'];
        $this->totalTime = $curlInfoArray['total_time'];
        $this->nameLookupTime = $curlInfoArray['namelookup_time'];
        $this->connectTime = $curlInfoArray['connect_time'];
        $this->preTransferTime = $curlInfoArray['pretransfer_time'];
        $this->uploadSize = $curlInfoArray['size_upload'];
        $this->downloadSize = $curlInfoArray['size_download'];
        $this->downloadSpeed = $curlInfoArray['speed_download'];
        $this->uploadSpeed = $curlInfoArray['speed_upload'];
        $this->downloadContentLength = $curlInfoArray['download_content_length'];
        $this->uploadContentLength = $curlInfoArray['upload_content_length'];
        $this->startTransferTime = $curlInfoArray['starttransfer_time'];
        $this->redirectTime = $curlInfoArray['redirect_time'];        
        $this->certInfo = $curlInfoArray['certinfo'];
        $this->requestHeader = $curlInfoArray['request_header'];
        $this->response = $responseString;
    }
    
    /**
     *
     * @return string
     */
    public function __toString() {
        return 'WebResponse: {Url:'.$this->url.'}, {Header:'.$this->requestHeader.'}, {HTTP Code:'.$this->httpCode.'}, {Response:'.$this->getResponse().'}';
    }
    
    /**
     *
     * @return boolean
     */
    public function errorOccurred() {
        return !is_string($this->response) || $this->curlErrno != 0;
    }
    
    /**
     *
     * @return int
     */
    public function getCurlErrno() {
        return $this->curlErrno;
    }

    /**
     *
     * @return string
     */
    public function getCurlError() {
        return $this->curlError;
    }

    /**
     *
     * @return string
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     *
     * @return string
     */
    public function getContentType() {
        return $this->contentType;
    }

    /**
     *
     * @return int
     */
    public function getHttpCode() {
        return $this->httpCode;
    }
       
    /**
     *
     * @return int
     */
    public function getHeaderSize() {
        return $this->headerSize;
    }

    /**
     *
     * @return int
     */
    public function getRequestSize() {
        return $this->requestSize;
    }

    /**
     *
     * @return int
     */
    public function getFileTime() {
        return $this->fileTime;
    }

    /**
     *
     * @return int
     */
    public function getSslVerifyResult() {
        return $this->sslVerifyResult;
    }

    /**
     *
     * @return int
     */
    public function getRedirectCount() {
        return $this->redirectCount;
    }

    /**
     *
     * @return int
     */
    public function getTotalTime() {
        return $this->totalTime;
    }

    /**
     *
     * @return int
     */
    public function getNameLookupTime() {
        return $this->nameLookupTime;
    }

    /**
     *
     * @return int
     */
    public function getConnectTime() {
        return $this->connectTime;
    }

    /**
     *
     * @return int
     */
    public function getPreTransferTime() {
        return $this->preTransferTime;
    }

    /**
     *
     * @return int
     */
    public function getUploadSize() {
        return $this->uploadSize;
    }

    /**
     *
     * @return int
     */
    public function getUploadSpeed() {
        return $this->uploadSpeed;
    }

    /**
     *
     * @return int
     */
    public function getUploadConetentLength() {
        return $this->uploadContentLength;
    }

    /**
     *
     * @return int
     */
    public function getDownloadSize() {
        return $this->downloadSize;
    }

    /**
     *
     * @return int
     */
    public function getDownloadSpeed() {
        return $this->downloadSpeed;
    }

    /**
     *
     * @return int
     */
    public function getDownloadContentLength() {
        return $this->downloadContentLength;
    }

    /**
     *
     * @return int
     */
    public function getStartTransferTime() {
        return $this->startTransferTime;
    }

    /**
     *
     * @return int
     */
    public function getRedirectTime() {
        return $this->redirectTime;
    }

    /**
     *
     * @return int
     */
    public function getCertInfo() {
        return $this->certInfo;
    }

    /**
     *
     * @return string
     */
    public function getRequestHeader() {
        return $this->requestHeader;
    }

    /**
     *
     * @return string
     */
    public function getResponse() {
        if($this->response === false) {
            return '';
        }
        return $this->response;
    }
}

?>
