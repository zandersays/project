<?php

/**
 * Description of LoggerDatabase
 *
 * @author Kam Sheffield
 * @version 08/30/2011
 */
class DatabaseLog {

    /**
     *
     * @var string
     */
    protected $tableName;

    /**
     *
     * @var array
     */
    protected $buffer;

    /**
     *
     * @var boolean
     */
    protected $isBuffering;

    /**
     *
     * @var DatabaseDriver
     */
    protected $databaseDriver;

    /**
     *
     * @param string $tableName
     * @param DatabaseDriver $databaseDriver
     * @param boolean $isBuffering
     */
    public function __construct($tableName, DatabaseDriver $databaseDriver, $isBuffering) {
        $this->tableName = $tableName;
        $this->databaseDriver = $databaseDriver;
        $this->isBuffering = $isBuffering;
        $this->buffer = array();
    }

    public function write($message, $className, $logLevel, $tag) {
        $this->buffer[] = array(
            'message' => $message,
            'className' => $className,
            'logLevel' => $logLevel,
            'tag' => $tag
        );

        if($this->isBuffering) {
            return true;
        }
        else {
            $count = $this->databaseDriver->log($this->tableName, $this->buffer);
            $this->buffer = array();
            return $count > 0;
        }
    }

    public function commit() {
        $this->databaseDriver->log($this->tableName, $this->buffer);
    }
}

?>
