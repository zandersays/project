<?php

/**
 * The main purpose of this class is to
 * control the verbosity of the log and
 * also provide a convenient api for logging.
 *
 * @author Kam Sheffield
 * @version 08/30/2011
 */
class Logger {

    /**
     *
     * @var string
     */
    protected $className;

    /**
     *
     * @var int
     */
    protected $logLevel;

    /**
     *
     * @var boolean
     */
    protected $logToFile;

    /**
     *
     * @var boolean
     */
    protected $logToDatabase;

    /**
     *
     * @param string $className
     * @param int $logLevel
     * @param boolean $logToFile
     * @param boolean $logToDatabase
     */
    public function __construct($className, $logLevel = LogLevel::Information, $logToFile = true, $logToDatabase = false) {
        $this->className = $className;
        $this->logLevel = $logLevel;
        $this->logToFile = $logToFile;
        $this->logToDatabase = $logToDatabase;
    }

    /**
     * Log a message with a LogLevel of 'error'.
     *
     * @param string $message
     * @param string tag
     * @return boolean Whether or not the operation succeeded
     */
    public function error($message, $tag = '') {
        return $this->write($message, $this->className, LogLevel::Error, $tag = '', $this->logToFile, $this->logToDatabase);
    }

    /**
     * Log a message with a LogLevel of 'warning'.
     *
     * @param string $message
     * @param string tag
     * @return boolean Whether or not the operation succeeded
     */
    public function warning($message, $tag = '') {
        return $this->write($message, $this->className, LogLevel::Warning, $tag = '', $this->logToFile, $this->logToDatabase);
    }

    /**
     * Log a message with a LogLevel of 'information'.
     *
     * @param string $message
     * @param string tag
     * @return boolean Whether or not the operation succeeded
     */
    public function information($message, $tag = '') {
        return $this->write($message, $this->className, LogLevel::Information, $tag = '', $this->logToFile, $this->logToDatabase);
    }

    /**
     * Log a message with a LogLevel of 'verbose'.
     *
     * @param string $message
     * @param string tag
     * @return boolean Whether or not the operation succeeded
     */
    public function verbose($message, $tag = '') {
        return $this->write($message, $this->className, LogLevel::Verbose, $tag, $this->logToFile, $this->logToDatabase);
    }

    /**
     * Log a message with full control over all parameters.
     *
     * @param string $message
     * @param string $className
     * @param int $logLevel
     * @param string $tag
     * @param boolean $logToFile
     * @param boolean $logToDatabase
     * @return boolean Whether or not the log operation succeeded
     */
    public function write($message, $className, $logLevel, $tag, $logToFile, $logToDatabase) {
        if($this->isValidLogLevel($logLevel)) {
            if($logLevel <= $this->logLevel) {
                return LogManager::getInstance()->writeToLogs($message, $className, $logLevel, $tag, $logToFile, $logToDatabase);
            }
            return false;
        }
        return false;
    }

    /**
     *
     * @return boolean
     */
    public function getLogToFile() {
        return $this->logToFile;
    }

    /**
     *
     * @param boolean $logToFile
     */
    public function setLogToFile($logToFile) {
        if(is_bool($logToFile)) {
            $this->logToFile = $logToFile;
        }
    }

    /**
     *
     * @return boolean
     */
    public function getLogToDatabase() {
        return $this->logToDatabase;
    }

    /**
     *
     * @param boolean $logToDatabase
     */
    public function setLogToDatabase($logToDatabase) {
        if(is_bool($logToDatabase)) {
            $this->logToDatabase = $logToDatabase;
        }
    }

    /**
     * See LogLevel.php
     *
     * @return int
     */
    public function getLogLevel() {
        return $this->logLevel;
    }

    /**
     * See LogLevel.php
     *
     * @param int $logLevel
     */
    public function setLogLevel($logLevel) {
        // validate the input as a valid log level
        if($this->isValidLogLevel($logLevel)) {
            $this->logLevel = $logLevel;
        }
    }

    /**
     * If the given LogLevel falls in the scope of valid LogLevels.
     *
     * @param int $logLevel
     * @return boolean
     */
    protected function isValidLogLevel($logLevel) {
        return is_int($logLevel) && $logLevel >= LogLevel::Error && $logLevel <= LogLevel::Verbose;
    }
}

?>
