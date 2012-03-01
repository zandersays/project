<?php

/**
 * The ModelException is used to wrap
 * any exception that originates from
 * the Models module.
 *
 * @author Kam Sheffield
 * @version 8/20/2011
 */
class ModelException extends Exception {

    /**
     * Exception code for ModelException
     */
    const Code = 1001;

    /**
     * Creates a new instance of a ModelException.
     *
     * @param string $message
     * @param Exception $exception
     */
    public function __construct($message, Exception $exception = null) {
        parent::__construct($message, self::Code, $exception);
    }

    /**
     *
     * @return string
     */
    public function __toString() {
        $string = 'ModelException: '.$this->message;
        if($this->getPrevious() != null) {
            $string .= ', SourceException: ' . $this->getPrevious()->__toString();
        }

        return $string;
    }
}

?>
