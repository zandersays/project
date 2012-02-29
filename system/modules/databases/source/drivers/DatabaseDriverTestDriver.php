<?php

/**
 * Implmentation of DatabaseDriver used
 * to test the abstract DatabaseDriver class
 * in unit tests.
 *
 * @author Kam Sheffield
 * @version 01/04/2011
 */
class DatabaseDriverTestDriver extends DatabaseDriver {

    /**
     * Creates a new instance of a DatabaseDriver.
     *
     * @param string $databaseName
     * @param string $hostName
     * @param int $port
     * @param string $userName
     * @param string $password
     * @param array $databaseOptions
     * @param bool $supportsTransactions
     */
    public function  __construct($databaseName, $hostName, $port, $userName,
            $password, $databaseOptions = array(), $supportsTransactions = false) {

        // use a mysql database for testing
        $connectionString = 'mysql:host=' . $hostName . ';port=' . $port . ';dbname=' . $databaseName;

        parent::__construct($databaseName, $hostName, $port, $userName, $password, $connectionString,
                DatabaseDriverTypes::TestDatabaseDriver, $databaseOptions, $supportsTransactions);
    }

    public function  getRelatedTables($tableName) {
        throw new Exception('Method not implemented');
    }

    public function log($tableName, Array $logMessages) {
        return 1;
    }
}

?>
