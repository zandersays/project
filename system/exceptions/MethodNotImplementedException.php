<?php

/**
 * This exception occurs when a given
 * method has only been stubbed out and
 * no function or implementation currently exists.
 *
 * @author Kam
 */
class MethodNotImplementedException extends Exception {

    /**
     * The reason for the exception.
     * 
     * @var string
     */
    private $message;

    /**
     * Creates a new instance of an
     * MethodNotImplementedException.
     *
     * @param string $methodName
     */
    public function  __construct($methodName, $code = 0, Exception $previousException = null) {
        $this->message = "The method: $methodName is currently not implemented";
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
