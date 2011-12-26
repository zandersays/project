<?php

/**
 *
 * @author Kam Sheffield
 * @version 08/30/2011
 */
class LogManager {

    /**
     * Singleton instance of the LogManager.
     *
     * @var LogManager
     */
    private static $instance;

    /**
     * Get the singleton instance of the LogManager.
     *
     * @return LogManager
     */
    public static function getInstance() {
        if (!(self::$instance instanceof LogManager)){
            self::$instance = new LogManager();
        }

        return self::$instance;
    }

    /**
     *
     * @var RollingLog
     */
    private $rollingLog;

    /**
     *
     * @var DatabaseLog
     */
    private $databaseLog;

    /**
     *
     * @var boolean
     */
    private $isEchoingMessages;

    /**
     *
     * @var string
     */
    private $lineEnding;

    private function __construct() {
        $settings = Project::getModuleSettings('Logging');

        // set up the rolling log, create one no matter what, (fail-safe)
        if(array_key_exists('rollingLog', $settings)) {
            $this->initRollingLog($settings['rollingLog']);
        }
        else {
            // pass in an empty array to get default settings
            $this->initRollingLog(array());
        }

        if(array_key_exists('databaseLog', $settings)) {
            $this->initDatabaseLog($settings['databaseLog']);
        }

        $this->isEchoingMessages = false;
        if(array_key_exists('echoMessages', $settings)) {
            $this->isEchoingMessages = $settings['echoMessages'];
        }

        $this->lineEnding = "\n";
        if(array_key_exists('lineEnding', $settings)) {
            $this->lineEnding = $settings['lineEnding'];
        }
    }

    private function initRollingLog(Array &$settings) {
        $isBuffering = false;
        if(array_key_exists('isBuffering', $settings)) {
            $isBuffering = $settings['isBuffering'];
        }

        $fileName = Project::getSiteTitle().'.log';
        if(array_key_exists('fileName', $settings)) {
            $fileName = $settings['fileName'];
        }

        $fileLocation = Project::getInstancePath();
        if(array_key_exists('fileLocation', $settings)) {
            $fileLocation = $settings['fileLocation'];
        }

        $maxFileSize = 1;
        if(array_key_exists('maxFileSize', $settings)) {
            $maxFileSize = $settings['maxFileSize'];
        }

        $maxFileCount = 3;
        if(array_key_exists('maxFileCount', $settings)) {
            $maxFileCount = $settings['maxFileCount'];
        }

        // TODO change when execution hooks are available
        $this->rollingLog = new RollingLog(false, $fileName, $fileLocation, $maxFileSize, $maxFileCount);
    }

    private function initDatabaseLog(Array &$settings) {
        $isBuffering = false;
        if(array_key_exists('isBuffering', $settings)) {
            $isBuffering = $settings['isBuffering'];
        }

        $databaseDriver = Database::getDatabaseDriver();
        if(array_key_exists('databaseName', $settings)) {
            $databaseDriver = Database::getDatabaseDriver($settings['databaseName']);
        }

        $tableName = 'project_log';
        if(array_key_exists('logTableName', $settings)) {
            $tableName = $settings['logTableName'];
        }

        if($databaseDriver != null) {
            // TODO change when execution hooks are available
            $this->databaseLog = new DatabaseLog($tableName, $databaseDriver, false);
        }
    }

    /**
     *
     * @param string $message
     * @param string $className
     * @param string $logLevel
     * @param string $tag
     * @param boolean $logToFile
     * @param boolean $logToDatabase
     * @return boolean
     */
    public function writeToLogs($message, $className, $logLevel, $tag, $logToFile, $logToDatabase) {
        $logWritten = true;

        $formattedMessage = '';
        if($this->isEchoingMessages || $logToFile) {
            // prepare the string message
            $formattedMessage = date("m/d/Y G:i:s T").' | '.$className.' '.String::upper(LogLevel::getString($logLevel)).': ';

            if(!String::isNullOrEmpty($tag)) {
                $formattedMessage .= '('.$tag.') ';
            }

            $formattedMessage .= $message;
        }

        if($logToFile) {
            $logWritten = $this->rollingLog->write($formattedMessage);
        }

        if($logToDatabase && $this->databaseLog != null) {
            $logWritten = $this->databaseLog->write($message, $className, $logLevel, $tag);
        }

        if($this->isEchoingMessages) {
            echo "$formattedMessage$this->lineEnding";
        }

        return $logWritten;
    }

    /**
     *
     * @return boolean
     */
    public function getIsEchoingMessages() {
        return $this->isEchoingMessages;
    }

    /**
     *
     * @param boolean $isEchoingMessages
     */
    public function setIsEchoingMessages($isEchoingMessages) {
        if(is_bool($isEchoingMessages)) {
            $this->isEchoingMessages = $isEchoingMessages;
        }
    }

    /**
     *
     * @return string
     */
    public function getLineEnding() {
        return $this->lineEnding;
    }

    /**
     *
     * @param string $lineEnding
     */
    public function setLineEnding($lineEnding) {
        if(is_string($lineEnding)) {
            $this->lineEnding = $lineEnding;
        }
    }
}

?>
