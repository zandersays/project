<?php
abstract class Api {
    var $request;
    var $apiUser;
    var $apiUserKey;
    var $apiUserKeyId;
    var $privileges;
    var $commands = array();
    var $apiDatabase = 'wallspotting';
    var $compressOutput = false;
    var $decompressCommandArguments = false;

    function initialize($request) {
        $this->request = $request;
        // Decompress the command if we need to
        if($this->decompressCommandArguments) {
            // Fix PHP string decompression
            $gzuncompressFix = tempnam('/tmp', 'gz_fix');
            file_put_contents($gzuncompressFix, "\x1f\x8b\x08\x00\x00\x00\x00\x00".urldecode($this->request['commandArguments']));
            $decompressedCommandArguments = file_get_contents('compress.zlib://' . $gzuncompressFix);
            $decompressedCommandArguments = json_decode($decompressedCommandArguments);

            // Set the decompressed command arguments in the request
            foreach($decompressedCommandArguments as $decompressedCommandArgumentKey => $decompressedCommandArgument) {
                $this->request[$decompressedCommandArgumentKey] = $decompressedCommandArgument;
            }
        }
        $this->verifyRequest();
        $this->processRequest();
    }

    function output($status, $response, $log = true) {
        if($log) {
            $this->logRequest($status, $response);
        }
        $output = json_encode(array("status" => $status, "response" => $response));
        if($this->compressOutput) {
            header("Content-Encoding: gzip");
            echo gzcompress($output, 9);
        }
        else {
            echo $output;
        }
        exit();
    }

    function logRequest($status, $response) {
    // Log the request
        if(!isset($this->apiUserKeyId)) {
            $apiUserKeyId = 'NULL';
        }
        else {
            $apiUserKeyId = $this->apiUserKeyId;
        }
        if(isset($this->request['command'])) {
            $command = $this->request['command'];
        }
        else {
            $command = "NULL";
        }
        $columnValuesArray = array(
            'api_user_key_id' => $apiUserKeyId,
            'api' => get_class($this),
            'command' => $command,
            'status' => $status,
            'response' => $response,
            'ip_added_by' => abs(ip2long($_SERVER['REMOTE_ADDR'])),
            'time_added' => 'NOW()',
        );
        $query = Database::createRecord($this->apiDatabase, 'api_history', $columnValuesArray);
        if($query['status'] != 'success') {
            $this->output("failure", 'There was an error logging the API request.', false);
        }
    }

    function verifyRequest() {
        // Validate the required fields
        if(!isset($this->request['apiUser']) || empty($this->request['apiUser'])) {
            $this->output("failure", "\"apiUser\" variable is required.");
        }
        $this->apiUser = $this->request['apiUser'];
        if(!isset($this->request['apiKey']) || empty($this->request['apiKey'])) {
            $this->output("failure", "\"apiKey\" variable is required.");
        }
        $this->apiUserKey = $this->request['apiKey'];
        if(!isset($this->request['command']) || empty($this->request['command'])) {
            $this->output("failure", "\"command\" variable is required.");
        }

        // Verify the username matches the key
        $mysql = Database::connect($this->apiDatabase);
        $sql = 'api_user_key.id, api_user_key.privileges FROM api_user_key, api_user WHERE api_user.id = api_user_key.api_user_id AND api_user.username = \''.mysql_escape_string($this->apiUser).'\' AND api_user_key.key = \''.mysql_escape_string($this->apiUserKey).'\'';
        $query = Database::select($this->apiDatabase, $sql);
        if($query['status'] == 'success') {
            $this->apiUserKeyId = $query['response'][0]->id;
            $this->privileges = $query['response'][0]->privileges;
        }
        else {
            $this->output("failure", "Invalid apiUser and apiKey combination.");
        }

        // Verify the user has the appropriate privileges
        if($this->privileges != "all") {
            $privileges = json_decode($privileges);
            if($privileges[$this->request['command']] != "yes") {
                $this->output("failure", "You do not have privileges to perform this command.");
            }
        }

        // Verify the command is valid
        if(!isset($this->commands[$this->request['command']])) {
            $this->output("failure", "\"".$this->request['command']."\" is an unknown command.");
        }

        // Verify all of the required variables are present
        foreach($this->commands[$this->request['command']]['requiredArguments'] as $requiredArguments) {
            if(!isset($this->request[$requiredArguments]) || empty($this->request[$requiredArguments])) {
                $this->output("failure", "The command \"".$this->request['command']."\" requires the argument \"".$requiredArguments."\" to be set.");
            }
        }
    }

    function processRequest() {
        $arguments = array();
        foreach($this->commands[$this->request['command']]['arguments'] as $argument) {
            // Don't break if they do not include an optional argument
            if(isset($this->request[$argument])) {
                $argumentToAdd = $this->request[$argument];

                // Automatically convert JSON strings into objects
                if(is_string($argumentToAdd) && Utility::isJson($argumentToAdd)) { // Handle raw JSON
                    $argumentToAdd = json_decode($argumentToAdd);
                }
                else if(is_string($argumentToAdd) && Utility::isJson(stripslashes($argumentToAdd))) { // Handle JSON that has been escaped
                    $argumentToAdd = json_decode(stripslashes($argumentToAdd));
                }
                else if(is_string($argumentToAdd) && Utility::isJson(urldecode($argumentToAdd))) { // Handle JSON that has been URL encoded
                    $argumentToAdd = json_decode(urldecode($argumentToAdd));
                }
                else if(is_string($argumentToAdd) && Utility::isJson(urldecode(stripslashes($argumentToAdd)))) { // Handle JSON that has been URL encoded and slashed
                    $argumentToAdd = json_decode(urldecode(stripslashes($argumentToAdd)));
                }

                $arguments[] = $argumentToAdd;
            }
        }

        $response = call_user_method_array($this->request['command'], $this, $arguments);
        $this->output($response['status'], $response['response']);
    }
}

?>
