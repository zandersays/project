<?php
class RestApi {
    var $url;
    var $api;
    var $apiUser;
    var $apiKey;
    var $method;
    var $decompressCommandArguments = false;
    var $compressOutput = false;

    function __construct($url, $api, $apiUser, $apiKey, $method = 'POST') {
        $this->url = $url;
        $this->api = $api;
        $this->apiUser = $apiUser;
        $this->apiKey = $apiKey;
        $this->method = $method;
    }

    function request($command, $arguments) {
        $data = array();
        $data['api'] = $this->api;
        $data['apiUser'] = $this->apiUser;
        $data['apiKey'] = $this->apiKey;
        $data['command'] = $command;

        // If we are sending the API compressed input
        if($this->decompressCommandArguments) {
            $data['decompressCommandArguments'] = 'true';
            $data['commandArguments'] = urlencode(gzdeflate(json_encode($arguments), 9));
        }
        else {
            $data = array_merge($data, $arguments);
        }
        // If we want compressed output back from the request
        if($this->compressOutput) {
            $data['compressOutput'] = 'true';
        }

        if(strtolower($this->method) == 'post') {
            $response = Network::getUrlContent($this->url, $data);
        }
        else {
            $queryString = '?';
            foreach($data as $key => $value) {
                $queryString .= $key.'='.$value.'&';
            }
            $response = Network::getUrlContent($this->url.$queryString, 'GET');
        }

        // If we want compressed output that means we need to decompress it when we get it
        if($this->compressOutput) {
            $response['response'] = gzuncompress($response['response']);
        }

        if(Utility::isJson($response['response'])) {
            $response = json_decode($response['response']);
        }
        else {
            $response = array('status' => 'failure', 'response' => array('message' => 'The request did not receive a JSON response.', 'response' => $response));
        }

        return $response;
    }
}
?>