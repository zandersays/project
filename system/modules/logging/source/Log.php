<?php

/**
 * Static interface to a singleton Logger
 * for logging convenience.
 *
 * @author Kameron Sheffield
 * @version 08/30/2011
 */
class Log {

    /**
     * The singleton.
     *
     * @var Logger
     */
    private static $logger;

    /**
     * Lazy initialization of the singleton
     *
     * @return Logger
     */
    private static function getLogger() {
        if(!self::$logger instanceof Logger) {
            self::$logger = new Logger('Project');
        }
        return self::$logger;
    }

    /**
     * Log a message with the LogLevel 'error'
     *
     * @param string $message
     * @param string $tag
     */
    public static function error($message, $tag = '') {
        self::getLogger()->error($message, $tag);
    }

    /**
     * Log a message with the LogLevel 'warning'
     *
     * @param string $message
     * @param string $tag
     */
    public static function warning($message, $tag = '') {
        self::getLogger()->warning($message, $tag);
    }

    /**
     * Log a message with the LogLevel 'information'
     *
     * @param string $message
     * @param string $tag
     */
    public static function information($message, $tag = '') {
        self::getLogger()->information($message, $tag);
    }

    /**
     * Log a message with the LogLevel 'verbose'
     *
     * @param string $message
     * @param string $tag
     */
    public static function verbose($message, $tag = '') {
        self::getLogger()->verbose($message, $tag);
    }

    /**
     * Log a message with full control of all logging options.
     *
     * @param string $message
     * @param int $logLevel
     * @param string $className
     * @param string $tag
     * @param boolean $logToFile
     * @param boolean $logToDatabase
     */
    public static function write($message, $logLevel = LogLevel::Information, $className = 'Project', $tag = '', $logToFile = true, $logToDatabase = false) {
        self::getLogger()->write($message, $className, $logLevel, $tag, $logToFile, $logToDatabase);
    }

    /**
     *
     * @return boolean
     */
    public static function getLogToFile() {
        return self::getLogger()->getLogToFile();
    }

    /**
     *
     * @param booelan $logToFile
     */
    public static function setLogToFile($logToFile) {
        self::getLogger()->setLogToFile($logToFile);
    }

    /**
     *
     * @return boolean
     */
    public static function getLogToDatabase() {
        return self::getLogger()->getLogToDatabase();
    }

    /**
     *
     * @param boolean $logToDatabase
     */
    public static function setLogToDatabase($logToDatabase) {
        self::getLogger()->setLogToDatabase($logToDatabase);
    }

    /**
     * Get the level at which to filter log messages, see LogLevel.php
     *
     * @return int
     */
    public static function getLogLevel() {
        return self::getLogger()->getLogLevel();
    }

    /**
     * Set the level at which to filter log messages, see LogLevel.php
     *
     * @param int $logLevel
     */
    public static function setLogLevel($logLevel) {
        self::getLogger()->setLogLevel($logLevel);
    }

    /**
     * Get whether or not log entries will be echoed
     * out during execution.
     *
     * @return boolean
     */
    public static function getEchoLogEntries() {
        return LogManager::getInstance()->getIsEchoingMessages();
    }

    /**
     * Set whether or not log entries will be echoed
     * out during execution.
     *
     * @param boolean $echoLogEntries
     */
    public static function setEchoLogEntries($echoLogEntries) {
        LogManager::getInstance()->setIsEchoingMessages($echoLogEntries);
    }

    /**
     * Get the line ending that will be used
     * if logs are echoed out.
     *
     * @return boolean
     */
    public static function getLineEnding() {
        return LogManager::getInstance()->getLineEnding();
    }

    /**
     * Set the line ending that will be used
     * if the logs are echoed out.
     *
     * @param boolean $lineEnding
     */
    public static function setLineEnding($lineEnding) {
        LogManager::getInstance()->setLineEnding($lineEnding);
    }
}

?>
