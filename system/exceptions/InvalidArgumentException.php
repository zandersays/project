<?php

/**
 * This exception occurs when an argument
 * passed to a function is in some way
 * not suitable for processing and the
 * method must abort.
 *
 * @author Kam Sheffield
 */
class InvalidArgumentException extends Exception {

    /**
     * The reaason for the exception.
     * 
     * @var string
     */
    private $message;

    /**
     * Creates a new instance of an
     * InvalidArgumentException.
     *
     * @param string $argument
     * @param string $functionName
     */
    public function  __construct($functionName, $argument, $reason, $code = 0, Exception $previousException = null) {
        $this->message = "The function: $functionName, was not able to operate on " .
                "the argument: $argument, because $reason";
        parent::__construct($this->message, $code, $previousException);
    }

    /**
     * The error message from this Exception.
     *
     * @return string
     */
    public function  __toString() {
        return $this->message;
    }
}

?>
