<?php
class Api extends Controller {

    // General variables
    public $outputTypes = array('raw', 'string', 'json');
    public $apiName;
    public $commandName;
    public $api;
    public $arguments = array();
    public $response;

    // Request options
    public $outputType = 'json';
    public $compressOutput = false;
    public $decompressCommandArguments = false;

    // API options
    public $httpsOnly = false;
    public $logging;
    public $policies;
    public $commands = array();

    public static function requireOnce($apiName) {
        Project::requireOnce(Project::getInstancePath().'controllers/apis/'.$apiName.'Api.php');
    }
    
    public function index($data) {
        // Make sure the API is specified
        if(!isset($data['apiName'])) {
            return $this->output(array('status' => 'failure', 'response' => 'Please specify an API.'));
        }

        $fullApiName = $data['apiName'].'Api';

        // Try to autoload the API
        Project::autoLoad(String::upperFirstCharacter($fullApiName));

        // Include the specified API if necessary
        if(!class_exists($fullApiName)) {
            //echo Project::getInstancePath().'controllers/apis/'.$fullApiName.'.php';
            $fileName = File::exists(Project::getInstancePath().'controllers/apis/'.$fullApiName.'.php', false);
            if($fileName) {
                require_once($fileName);
            }
            else {
                return $this->output(array('status' => 'failure', 'response' => 'API "'.$data['apiName'].'" not found.'));
            }
        }

        // Make sure the API exists
        if(!class_exists($fullApiName)) {
            return $this->output(array('status' => 'failure', 'response' => 'API "'.$data['apiName'].'" not found.'));
        }

        // Make sure the command is specified
        if(!isset($data['commandName'])) {
            return $this->output(array('status' => 'failure', 'response' => 'Please specify a command for the '.$data['apiName'].' API.'));
        }

        // Make sure the specified API command exists
        if(!method_exists($fullApiName, $data['commandName'])) {
            return $this->output(array('status' => 'failure', 'response' => 'The "'.$data['apiName'].'" API does not have the command "'.$data['commandName'].'".'));
        }

        // Create an instance of the specified API
        $this->api = new $fullApiName();

        // Initialize the request
        return $this->api->initializeRequest($data['apiName'], $data['commandName'], $data);
    }
    
    public function initializeRequest($apiName, $commandName, $data = array()) {
        $this->apiName = $apiName;
        $this->commandName = $commandName;

        // Combine all request variables (data (URL), GET, POST) into a single arguments array
        $this->arguments = Arr::merge($this->arguments, $data);
        $this->arguments = Arr::merge($this->arguments, $_GET);
        $this->arguments = Arr::merge($this->arguments, $_POST);
        // REST style post/put support (using data from body)
        $phpInput = json_decode(file_get_contents('php://input'), true);
        if(Arr::is($phpInput)) {
            $this->arguments = Arr::merge($this->arguments, $phpInput);
        }

        // Clean up the argument array
        if(isset($this->arguments['project'])) {
            unset($this->arguments['project']);
        }
        if(isset($this->arguments['apiName'])) {
            unset($this->arguments['apiName']);
        }
        if(isset($this->arguments['commandName'])) {
            unset($this->arguments['commandName']);
        }

        // Optionally turn on decompress command arguments
        if(isset($this->arguments['decompressCommandArguments']) && ($this->arguments['decompressCommandArguments'] === true || $this->arguments['decompressCommandArguments'] == 'true')) {
            //echo 'Switching on decompress command arguments';
            $this->decompressCommandArguments = true;
            $this->decompressCommandArguments();
            unset($this->arguments['decompressCommandArguments']);
        }

        // Optionally turn on compress output
        if(isset($this->arguments['compressOutput']) && ($this->arguments['compressOutput'] == true || $this->arguments['compressOutput'] == 'true')) {
            //echo 'Switching on compress output';
            $this->compressOutput = true;
            unset($this->arguments['compressOutput']);
        }

        // Optionally change output type
        if(isset($this->arguments['outputType']) && Arr::contains($this->arguments['outputType'], $this->outputTypes)) {
            //echo 'Setting output type to '.$this->arguments['outputType'];
            $this->outputType = $this->arguments['outputType'];
            unset($this->arguments['outputType']);
        }
        else if(isset($this->arguments['outputType'])) {
            return $this->output(array('status' => 'failure', 'response' => 'The output type "'.$this->arguments['outputType'].'" is not supported.'));
        }

        // Verify the request
        return $this->verifyRequest();
    }

    public function decompressCommandArguments() {
        // Fix PHP string decompression
        $gzuncompressFix = tempnam('/tmp', 'gz_fix');
        file_put_contents($gzuncompressFix, "\x1f\x8b\x08\x00\x00\x00\x00\x00".urldecode($this->arguments['commandArguments']));
        $decompressedCommandArguments = file_get_contents('compress.zlib://' . $gzuncompressFix);
        $decompressedCommandArguments = json_decode($decompressedCommandArguments);

        // Set the decompressed command arguments in the request
        if(!$decompressedCommandArguments) {
            return $this->output(array('status' => 'failure', 'response' => 'Could not decompress command arguments.'));
        }
        foreach($decompressedCommandArguments as $decompressedCommandArgumentKey => $decompressedCommandArgument) {
            $this->arguments[$decompressedCommandArgumentKey] = $decompressedCommandArgument;
        }

        unset($this->arguments['commandArguments']);
    }

    public function verifyRequest() {
        // Make sure the command is accessible
        if(!Arr::hasKey($this->commandName, $this->commands)) {
            return $this->output(array('status' => 'failure', 'response' => 'The "'.$this->apiName.'" API command "'.$this->commandName.'" is not accessible.'));
        }

        //print_r($this->arguments); exit();

        // Make sure all of the required arguments are present and (TODO) typed correctly
        if(isset($this->commands[$this->commandName]['arguments'])) {
            foreach($this->commands[$this->commandName]['arguments'] as $argument) {
                if(isset($argument['required']) && $argument['required'] === true && (!isset($this->arguments[$argument['name']]) || empty($this->arguments[$argument['name']]))) {
                    return $this->output(array('status' => 'failure', 'response' => 'The "'.$this->apiName.'" API command "'.$this->commandName.'" requires the argument "'.$argument['name'].'".'));
                }
            }
        }

        // Process the request
        return $this->processRequest();
    }
    
    public static function processArgument($argument) {
        // Check to see if the argument to add is an object or an array, if so, decode it
        if(Json::is($argument)) {
            $argument = Json::decode($argument);
        }       
        
        return $argument;
    }

    public function processRequest() {
        // The arguments to send to the invoked function
        $arguments = array();
        
        // If there are arguments specified in the commands variable of the API
        if(isset($this->commands[$this->commandName]['arguments']) && Arr::is($this->commands[$this->commandName]['arguments'])) {
            foreach($this->commands[$this->commandName]['arguments'] as $argument) {
                // If the argument is set
                if(isset($this->arguments[$argument['name']])) {
                    $arguments[] = self::processArgument($this->arguments[$argument['name']]);
                }
                
                // If the command argument 'options' is set to true, gather all of the other passed arguments and put them in an array
                if(isset($argument['options']) && $argument['options']) {
                    //echo 'Options is set.'; print_r($this->arguments);
                    $options = array();
                    foreach($this->arguments as $argumentKey => $argumentValue) {
                        $options[$argumentKey] = self::processArgument($argumentValue);    
                    }
                    
                    $arguments[] = $options;
                }
            }
        }
        
        
        // Log the request
        if($this->logging) {
            $this->logRequest($this->commandName, $this->arguments);
        }

        // Call the method
        try {
            //$response = call_user_method_array($this->commandName, $this, $arguments);

            // Old:
            // call_user_method_array('view', $this, $args);
            // New:
            //call_user_func_array(array($this, 'view'), $args);
            
            $response = call_user_func_array(array($this, $this->commandName), $arguments);

            return $this->output($response);
        }
        catch(Exception $exception) {
            //print_r($exception);
            return $this->output(array('status' => 'failure', 'response' => 'There was an exception while performing the command "'.$this->commandName.'" on the "'.$this->apiName.'" API.', 'exception' => $exception->getMessage()));
        }
    }

    public function output($response) {
        // TODO: Maybe this should go on the api/ route instead of here?
        Router::setHeader('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Disable IE caching for AJAX requests
        Router::setHeader('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        Router::setHeader('Cache-Control: no-cache, must-revalidate');
        Router::setHeader('Pragma: no-cache');
        //Router::setHeader('Content-Type: application/json');
                
        if($this->compressOutput) {
            Router::setHeader('Content-Encoding: gzip');
            if(String::is($response)) {
                $response = gzcompress($response, 9);
            }
            else {
                $response = gzcompress(Json::encode($response), 9);
            }
        }
        else {
            if($this->outputType == 'raw' || $this->outputType == 'string') {
                $response = $response;
            }
            else if($this->outputType == 'json') {
                $response = Json::encode($response);
            }
        }
                
        return $response;
    }
    
    public static function logRequest($command, $arguments) {
        $apiLog = new ApiLog();
        $apiLog->setCommand($command);
        $apiLog->setArguments($arguments);
        $apiLog->setTimeAdded(Time::dateTime());
        $apiLog->setIpAddedBy(Network::ipV4ToLongInteger());
        $apiLog->save();
    }

}
?>