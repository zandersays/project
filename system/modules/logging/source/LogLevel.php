<?php

/**
 * All of the different LogLevels for
 * the Logging module.
 *
 * @author Kam Sheffield
 * @version 08/30/2011
 */
class LogLevel {

    /**
     * Error
     */
    const Error = 0;

    /**
     * Warning
     */
    const Warning = 1;

    /**
     * Information
     */
    const Information = 2;

    /**
     * Verbose
     */
    const Verbose = 3;

    /**
     *
     * @param int $logLevel
     * @return boolean
     */
    public static function isError($logLevel) {
        return LogLevel::Error == $logLevel;
    }

    /**
     *
     * @param int $logLevel
     * @return boolean
     */
    public static function isWarning($logLevel) {
        return LogLevel::Warning == $logLevel;
    }

    /**
     *
     * @param int $logLevel
     * @return boolean
     */
    public static function isInformation($logLevel) {
        return LogLevel::Information == $logLevel;
    }

    /**
     *
     * @param int $logLevel
     * @return boolean
     */
    public static function isVerbose($logLevel) {
        return LogLevel::Verbose == $logLevel;
    }

    /**
     *
     * @param int $logLevel
     * @return string
     */
    public static function getString($logLevel) {
        switch($logLevel) {
            case LogLevel::Error:
                return 'error';
            case LogLevel::Warning:
                return 'warning';
            case LogLevel::Information:
                return 'information';
            case LogLevel::Verbose:
                return 'verbose';
            default:
                return 'information';
        }
    }
}

?>
