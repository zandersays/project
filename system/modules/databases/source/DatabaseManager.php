<?php

/**
 * Singleton that manages all of the
 * DatabaseDrivers and the interaction
 * between them and the static interface.
 * 
 * @author Kam Sheffield
 * @version 01/04/2011
 */
class DatabaseManager {
    
    /**
     * Global singleton instance of the DatabaseManager.
     *
     * @var DatabaseManager
     */
    private static $instance;

    /**
     * Array of DatabaseDrivers
     *
     * @var array
     */
    private $databaseDriverArray;

    /**
     * The id of the default database
     * driver for the DatabaseManager to use.
     * This value is null if not default has been
     * set.
     * 
     * @var int
     */
    private $defaultDatabaseDriverId;

    /**
     * Whether or not intialize PDO
     * drivers with persistent connections.
     *
     * @var boolean
     */
    private $pdoUsePersistentConnections;

    /**
     * Creates an instance of the DatabaseManager.
     */
    private function __construct() {
        $this->databaseDriverArray = array();
        $this->defaultDatabaseDriverId = null;
        $this->pdoUsePersistentConnections = true;
    }

    /**
     * Intentionally unimplemented to retain singleton design pattern.
     *
     * @exception MethodNotImplementedException
     */
    public function __clone() {
         trigger_error('Clone is not allowed.', E_USER_ERROR);
    }

    /**
     * Returns the global instance of the
     * DatabaseManager.
     *
     * @return DatabaseManager
     */
    public static function getInstance() {
        if (!(self::$instance instanceof DatabaseManager)) {
            self::$instance = new DatabaseManager();
        }

        return self::$instance;
    }

    /**
     * Gets the driver connected to the database
     * with the $databaseId specified.  If a DatabaseDriver
     * matching the $databaseId cannot be found
     * an exception is thrown.
     *
     * If the $databaseId is null the default driver is returned if there
     * is one, otherwise an exception is thrown.
     *
     * @param string $databaseDriverId The Id of the database you want to get.
     * @return DatabaseDriver The DatabaseDriver for the database specified.
     * @exception DatabaseException : If a database with $databaseId does not exist, or no default driver exists.
     */
    public function getDatabaseDriver($databaseDriverId = null) {
        if($databaseDriverId != null) {
            if(isset($this->databaseDriverArray[$databaseDriverId])) {
                return $this->databaseDriverArray[$databaseDriverId];
            }
            else {
                throw new DatabaseException('A database with id: ' . $databaseDriverId . ' does not exist in the database driver list.');
            }
        }
        else {
            if($this->defaultDatabaseDriverId != null) {
                if(isset($this->databaseDriverArray[$this->defaultDatabaseDriverId])) {
                    return $this->databaseDriverArray[$this->defaultDatabaseDriverId];
                }
                else {
                    throw new DatabaseException('The default database specified with id: ' .
                            $this->defaultDatabaseDriverId . ' does not exist in the database driver list.');
                }
            }
            else {
                throw new DatabaseException('You did not specify a database and no defualt database has been specified.');
            }
        }
    }

    /**
     * Get a list of all currently instanced DatabaseDrivers.
     *
     * @return array A list of all current DatabaseDrivers
     */
    public function getAllDatabaseDrivers() {
        $databaseDriverList = array();
        foreach($this->databaseDriverArray as $databaseDriver) {
            $databaseDriverList[] = $databaseDriver;
        }
        return $databaseDriverList;
    }

    /**
     * Get a DatabaseDriver by name.
     *
     * @return DatabaseDriver The located DatabaseDriver
     */
    public function getDatabaseDriverByDatabaseName($databaseName) {
        foreach($this->databaseDriverArray as $databaseDriver) {
            if($databaseDriver->getDatabaseName() == $databaseName) {
                return $databaseDriver;
            }
        }
        return null;
    }

    /**
     * Adds a DatabaseDriver to the DatabaseManager.
     * If no default driver has been set, the driver
     * added will become the default.
     *
     * @param DatabaseDriver $databaseDriver The DatabaseDriver you want to add.
     * @exception DatabaseException : If a DatabaseDriver with the same id already exists.
     */
    public function addDatabaseDriver($databaseDriver) {
        // check to see if a driver with the same id already exists, this should not happen unless the same driver is added twice
        if(array_key_exists($databaseDriver->getId(), $this->databaseDriverArray)) {
            throw new DatabaseException('A DatabaseDriver already exists with the same identifier as the DatabaseDriver you attempted to add');
        }
        else {
            // add it to the driver list
            $this->databaseDriverArray[$databaseDriver->getId()] = $databaseDriver;

            // set this as default if there is not one
            if($this->defaultDatabaseDriverId == null) {
                $this->defaultDatabaseDriverId = $databaseDriver->getId();
            }
        }
    }

    /**
     * Removes the DatabaseDriver with the Id
     * specified from the DatabaseManager.
     * If the driver specified is the current default,
     * the default is set to null, a new default will
     * have to be specified using setDefaultDatabase().
     *
     * @param string $databaseDriverId The Id of the driver you want to remove.
     * @return boolean Whether or not the operation was successful.
     * @exception DatabaseException : If a DatabaseDriver with $databaseId does not exist.
     */
    public function removeDatabaseDriver($databaseDriverId) {
        // check to see if this is the default
        if($this->defaultDatabaseDriverId == $databaseDriverId) {
            $this->defaultDatabaseDriverId = null;
        }

        // remove it from the database array
        if(isset($this->databaseDriverArray[$databaseDriverId])) {
            $this->databaseDriverArray[$databaseDriverId]->closeConnection();
            unset($this->databaseDriverArray[$databaseDriverId]);
        }
        else {
            throw new DatabaseException('A database with id: ' . $databaseDriverId . ' does not exist in the database list.');
        }
    }

    /**
     * Get the id of the default DatabaseDriver.
     *
     * @return int The id of the default DatabaseDriver, or null if no default exists.
     */
    public function getDefaultDatabaseDriverId() {
        return $this->defaultDatabaseDriverId;
    }

    /**
     * Set the id of the default DatabaseDriver.
     *
     * @param int $databaseDriverId The id of the new default DatabaseDriver.
     * @exception DatabaseException | If an error occurs during the database operation.
     */
    public function setDefaultDatabaseDriverId($databaseDriverId) {
        // make sure the database trying to be set to default exists in the database list
        if(array_key_exists($databaseDriverId, $this->databaseDriverArray)) {
            $this->defaultDatabaseDriverId = $databaseDriverId;
        }
        else {
            $message = 'The default database you are trying to set has not been added to the DatabaseManager';
            throw new DatabaseException($message);
        }
    }

    /**
     * Get the instance of the default DatabaseDriver.
     *
     * @return DatabaseDriver The instance of the default DatabaseDriver.
     */
    public function getDefaultDatabaseDriver() {
        return $this->getDatabaseDriver($this->defaultDatabaseDriverId);
    }    

    /**
     * Set the default DatabaseDriver with the DatabaseDriver
     * specified.  If the DatabaseDriver has not been added it
     * will be, and then set to default.
     *
     * @param DatabaseDriver $databaseDriver The DatabaseDriver you want to be the default.
     */
    public function setDefaultDatabaseDriver($databaseDriver) {
        if(!array_key_exists($databaseDriver->getId(), $this->databaseDriverArray)) {
            $this->addDatabaseDriver($databaseDriver);
            $this->setDefaultDatabaseDriverId($databaseDriver->getId());
        }
        else {
            $this->setDefaultDatabaseDriverId($databaseDriver->getId());
        }
    }

    /**
     * Get whether or not the Databases will
     * attempt to use persistent connections.
     * 
     * @return boolean 
     */
    public function getUsePersistentConnections() {
        return $this->pdoUsePersistentConnections;
    }

    /**
     * Set whether or not the Databases will
     * attempt to use persistent connections.
     *
     * @param boolean $value
     */
    public function setUsePersistentConnections($value = true) {
        $this->pdoUsePersistentConnections = $value;
    }       
}

?>
