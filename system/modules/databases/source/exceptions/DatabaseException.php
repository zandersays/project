<?php

/**
 * Any exception that is thrown by the
 * Databases module will either be an instance
 * of this Exception or inherit from this
 * Exception.
 *
 * @author Kam Sheffield
 * @version 12/21/2010
 */
class DatabaseException extends Exception {

    /**
     * Creates a new instance of a
     * DatabaseException.
     *
     * @param string $message The message for the Exception.
     */
    public function __construct($message) {
        parent::__construct($message, 0);
    }
}

?>
