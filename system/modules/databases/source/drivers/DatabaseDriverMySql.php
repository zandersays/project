<?php

/**
 * Implementation of the DatabaseDriver for a MySql database.
 *
 * @author Kam Sheffield
 * @version 08/30/2011
 */
class DatabaseDriverMySql extends DatabaseDriver {

    /**
     * The socket the database is
     * running on if specified.
     *
     * @var string
     */
    protected $socket;

    /**
     * Creates a new instance of a DatabaseDriverMySqlDriver.
     *
     * @param string $databaseName
     * @param string $hostName
     * @param string $userName
     * @param string $password
     * @param array $databaseOptions
     * @exception PDOException : If an error occurs instantiating the driver
     */
    public function __construct($databaseName, $hostName, $userName, $password, $port = null, $socket = null, $databaseOptions = null) {
        // check to see if we are to use a persistent connection
        if(DatabaseManager::getInstance()->getUsePersistentConnections()) {
            $addPersistence = array(PDO::ATTR_PERSISTENT => true);
            if($databaseOptions != null) {
                $databaseOptions = ARR::merge($addPersistence, $databaseOptions);
            }
            else {
                $databaseOptions = $addPersistence;
            }
        }

        // create the connection string
        if($socket == null) {
            if($port == null) {
                $connectionString = 'mysql:host=' . $hostName . ';port=3306;dbname=' . $databaseName;
            }
            else {
                $connectionString = 'mysql:host=' . $hostName . ';port=' . $port . ';dbname=' . $databaseName;
            }
        }
        else {
            $connectionString = 'mysql:unix_socket=' . $socket . ';dbname=' . $databaseName;
        }

        $this->socket = $socket;

        if($databaseOptions == null) {
            $databaseOptions = array();
        }

        // instantiate all of the parent fields
        parent::__construct($databaseName, $hostName, $port, $userName, $password, $connectionString, DatabaseDriverTypes::MySQL, $databaseOptions, true);
    }


    /**
     * The socket this MySql database is
     * running on if it was specified, during
     * instantiation.
     *
     * @return string
     */
    public function getSocket() {
        return $this->socket;
    }

    /**
     *
     * @param string $tableName
     * @return array
     */
    public function getForeignKeysForTable($tableName) {
        try {
            $databaseDriver = new DatabaseDriverMySql('information_schema', $this->hostName, $this->userName, $this->password);
            $sql = 'SELECT * FROM KEY_COLUMN_USAGE WHERE
                        CONSTRAINT_SCHEMA = \''.$this->databaseName.'\' AND
                        TABLE_SCHEMA = \''.$this->databaseName.'\' AND
                        TABLE_NAME = \''.$tableName.'\';';
            $pdoStatement = $databaseDriver->prepare($sql);
            $pdoStatement->execute();
            $results = $pdoStatement->fetchAll(PDO::FETCH_ASSOC);
            return $results;
        }
        catch(Exception $e) {
            throw new DatabaseException('An error occurred retrieving the foreign keys for table: ' . $tableName .
                    ', for database: ' . $this->databaseName . ', Exception: ' . $e->__toString());
        }
    }

    /**
     *
     * @param string $tableName
     * @return array
     */
    public function getTablesRelatedToTable($tableName) {
        try {
            $databaseDriver = new DatabaseDriverMySql('information_schema', $this->hostName, $this->userName, $this->password);
            $sql = 'SELECT * FROM KEY_COLUMN_USAGE WHERE
                        CONSTRAINT_SCHEMA = \''.$this->databaseName.'\' AND
                        TABLE_SCHEMA = \''.$this->databaseName.'\' AND
                        REFERENCED_TABLE_NAME = \''.$tableName.'\';';
            $pdoStatement = $databaseDriver->prepare($sql);
            $pdoStatement->execute();
            $results = $pdoStatement->fetchAll(PDO::FETCH_ASSOC);
            return $results;
        }
        catch(Exception $e) {
            throw new DatabaseException('An error occurred retrieving all table relations for table: ' . $tableName .
                    ', for database: ' . $this->databaseName . ', Exception: ' . $e->__toString());
        }
    }

    /**
     *
     * @param string $tableName
     * @param array $logMessages
     * @return int
     */
    public function log($tableName, Array $logMessages) {
        $sql = 'INSERT INTO `'.$this->databaseName.'`.`'.$tableName.'` (`time_added`, `log_level`, `class_name`, `tag`, `message`) VALUES ';

        $logCount = count($logMessages);
        $count = 0;
        for($i = 0; $i < $logCount; $i++) {
            $sql .= '(NOW(), :v'.$count++.', :v'.$count++.', :v'.$count++.', :v'.$count++.'), ';
        }
        $sql = String::replaceLast(', ', ';', $sql);

        $pdoStatement = $this->prepare($sql);

        $newCount = 0;
        foreach($logMessages as $logMessage) {
            $pdoStatement->bindValue(':v'.$newCount, LogLevel::getString($logMessage['logLevel']), PDO::PARAM_STR);
            $newCount++;
            $pdoStatement->bindValue(':v'.$newCount, $logMessage['className'], PDO::PARAM_STR);
            $newCount++;
            $pdoStatement->bindValue(':v'.$newCount, $logMessage['tag'], PDO::PARAM_STR);
            $newCount++;
            $pdoStatement->bindValue(':v'.$newCount, $logMessage['message'], PDO::PARAM_STR);
            $newCount++;
        }

        assert($count == $newCount);

        $pdoStatement->execute();

        return $pdoStatement->rowCount();
    }
}

?>
