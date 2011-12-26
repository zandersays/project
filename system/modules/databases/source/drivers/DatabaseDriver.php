<?php

/**
 * This class defines the interface
 * to a database.  All database
 * specific logic must be implemented
 * by extending this class.
 *
 * @author Kam Sheffield
 * @version 08/30/2011
 */
abstract class DatabaseDriver {

    /**
     * The name of the database
     * this DatabaseDriver is
     * connected to.
     *
     * @var string
     */
    protected $databaseName;

    /**
     * The name of the host
     * that the database this
     * DatabaseDriver is connected to
     * resides on.
     *
     * @var string
     */
    protected $hostName;

    /**
     * The port the database
     * is running on.
     *
     * @var int
     */
    protected $port;

    /**
     * The user name for the
     * database connected to
     * this DatabaseDriver.
     *
     * @var string
     */
    protected $userName;

    /**
     * The password for the
     * database connected to
     * this DatabaseDriver.
     *
     * @var string
     */
    protected $password;

    /**
     * The string used to create
     * the PDO object that connects
     * to the database.
     *
     * @var string
     */
    protected $connectionString;

    /**
     * Hash of server name, hostname, and port for
     * the sql server connected to this DatabaseDriver
     * to uniquely identify it.
     *
     * @var string
     */
    protected $id;

    /**
     * An assosciative array of database
     * options to use upon connection to
     * the database.
     *
     * @var array
     */
    protected $databaseOptions;

    /**
     * Wether or not the current DatabaseDriver
     * supports transactions.
     *
     * @var boolean
     */
    protected $supportsTransactions;

    /**
     * PDO Object backing this DatabaseDriver.
     *
     * @var PDO
     */
    protected $pdo;

    /**
     * Whether or not the current
     * DatabaseDriver is in a transaction.
     *
     * @var boolean
     */
    protected $inTransaction;

    /**
     * The type of database this
     * DatabaseDriver is connected to,
     * ie MySQL, SQLite, MSSQL.
     *
     * @var string
     */
    private $databaseType;

    /**
     * Creates a new instance of a DatabaseDriver.
     *
     * @param string $hostName
     * @param string $databaseName
     * @param string $userName
     * @param string $password
     * @param PDO $pdo
     * @param string $databaseType
     * @param array $databaseOptions
     * @param boolean $supportsTransactions
     */
    public function __construct($databaseName, $hostName, $port, $userName, $password,
            $connectionString, $databaseType, $databaseOptions = array(), $supportsTransactions = false) {
        $this->databaseName = $databaseName;
        $this->hostName = $hostName;
        $this->port = $port;
        $this->userName = $userName;
        $this->password = $password;
        $this->connectionString = $connectionString;
        $this->id = $databaseName;
        //$this->id = md5($databaseName . $hostName . $port);
        $this->supportsTransactions = $supportsTransactions;
        $this->pdo = null;
        $this->inTransaction = false;
        $this->databaseType = $databaseType;
    }

    /**
     * Opens a connection to the database.
     * The database will be connected to
     * automatically when a call that requires
     * a connection is used.
     */
    public function openConnection() {
        if(!$this->isConnected()) {
            $this->pdo = new PDO($this->connectionString, $this->userName, $this->password, $this->databaseOptions);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
    }

    /**
     * Closes the connection to the database.
     */
    public function closeConnection() {
        $this->pdo = null;
    }

    /**
     * Whether or not the driver is
     * currently connected to a database.
     *
     * @return boolean
     */
    public function isConnected() {
        if($this->pdo == null) {
            return false;
        }
        else {
            return true;
        }
    }

    /**
     * Get the hostname of the database this DatabaseDriver is connected to.
     *
     * @return string
     */
    public function getHostName() {
        return $this->hostName;
    }

    /**
     * Get the port this database
     * is running on.
     *
     * @return int
     */
    public function getPort() {
        return $this->port;
    }

    /**
     * Get the name of the database this DatabaseDriver is connected to.
     *
     * @return string
     */
    public function getDatabaseName() {
        return $this->databaseName;
    }

    /**
     * Whether or not the DatabaseDriver
     * supports transactions.
     *
     * @return boolean
     */
    public function supportsTransactions() {
        return $this->supportsTransactions;
    }

    /**
     * Get the type of database this
     * DatabaseDriver is connected to,
     * ie MySQL, SQLite, MSSQL.
     *
     * @var string
     */
    public function getDatabaseType() {
        return $this->databaseType;
    }

    /**
     * Begins a transaction on the database
     * attached to this driver.
     *
     * @exception DatabaseException : If the database operation failed
     */
    public function startTransaction() {
        try {
            // create the connection if needed
            $this->openConnection();

            // try to begin the transaction
            if($this->supportsTransactions) {
                if(!$this->pdo->beginTransaction()) {
                    throw new DatabaseException('Failed to begin the transaction, Error: ' . $this->pdo->errorInfo());
                }

                $this->inTransaction = true;
            }
            else {
                throw new DatabaseException('The current DatabaseDriver connected to: ' .
                        $this->databaseName . ', does not support transactions.');
            }
        }
        catch(PDOException $e) {
            throw new DatabaseException('Failed to begin the transaction, Exception: ' . $e->__toString());
        }
    }

    /**
     * Commits a transaction on the database
     * attached to this driver.
     *
     * @exception DatabaseException : If the database operation failed
     */
    public function commitTransaction() {
        try {
            if(!$this->isConnected()) {
                throw new DatabaseException('Failed to commit the transaction: You must be connected to database first!');
            }
            if($this->supportsTransactions) {
                if(!$this->pdo->commit()) {
                    throw new DatabaseException('Failed to commit the transaction, Error: ' . $this->pdo->errorInfo());
                }

                $this->inTransaction = false;
            }
            else {
                throw new DatabaseException('The current DatabaseDriver connected to: ' .
                        $this->databaseName . ', does not support transactions.');
            }
        }
        catch(PDOException $e) {
            throw new DatabaseException('Failed to commit the transaction, Exception: ' . $e->__toString());
        }
    }

    /**
     * Rollsback a transaction on the database
     * attached to this driver.
     *
     * @exception DatabaseException : If the database operation failed
     */
    public function rollbackTransaction() {
        try {
            if(!$this->isConnected()) {
                throw new DatabaseException('Failed to rool back the transaction: You must be connected to a database first!');
            }

            if($this->supportsTransactions) {
                if(!$this->pdo->rollBack()) {
                    throw new DatabaseException('Failed to rollback the transaction, Error: ' . $this->pdo->errorInfo());
                }

                $this->inTransaction = false;
            }
            else {
                throw new DatabaseException('The current DatabaseDriver connected to: ' .
                        $this->databaseName . ', does not support transactions.');
            }
        }
        catch(PDOException $e) {
            throw new DatabaseException('Failed to rollback the transaction, Exception: ' . $e->__toString());
        }
    }

    /**
     * Whether or not the DatabaseDriver
     * is currently in a transaction.
     *
     * @return boolean
     */
    public function inTransaction() {
        return $this->inTransaction;
    }

    /**
     * Quotes a string for the specific driver
     * backing this DatabaseDriver.
     *
     * @param string $sqlString
     * @return string Returns a quoted string that is theoretically safe to pass into an SQL statement.
     *                Returns FALSE if the driver does not support quoting in this way.
     */
    public function escapeString($sqlString) {
        $this->openConnection();
        return $this->pdo->quote($sqlString);
    }

    /**
     * Returns the id from the last inserted
     * row in the database.
     *
     * @return int
     */
    public function getLastInsertId() {
        $this->openConnection();
        return $this->pdo->lastInsertId();
    }

    /**
     * Get the value of the specified
     * PDO Attribute for the underlying
     * PDO Driver.
     *
     * @param int $pdoAttribute
     */
    public function getAttribute($pdoAttribute) {
        $this->openConnection();
        return $this->pdo->getAttribute($pdoAttribute);
    }

    /**
     * Set the value of the PDO Attribute
     * to $value for the underlying PDO Driver.
     *
     * @param int $pdoAttribute
     * @param mixed $value
     */
    public function setAttribute($pdoAttribute, $value) {
        $this->openConnection();
        return $this->pdo->setAttribute($pdoAttribute, $value);
    }

    /**
     * Creates a new prepared statement as
     * a PDOStatement object.
     *
     * @param string $statement
     * @param array $driverOptions
     * @return PDOStatement
     * @exception DatabaseException
     */
    public function prepare($statement, array $driverOpertions = array()) {
        try {
            $this->openConnection();
            return $this->pdo->prepare($statement);
        }
        catch(PDOException $e) {
            throw new DatabaseException('An error occurred while preparing a statement, Exception: ' . $e->__toString());
        }
    }

    /**
     * Creates a new query for the database,
     * and returns the results.
     *
     * @param string $sqlStatement The query you want to make
     * @return array An associative array with the results of the query, or an int that is the count of the number of affected rows
     * @exception DatabaseException | If an error occurs during the query
     */
    public function query($sqlStatement) {
        try {
            $this->openConnection();
            $sqlStatement = String::trim($sqlStatement);
            $pdoStatement = $this->pdo->query($sqlStatement);

            // TODO: We may want to look at a more efficient implementation of this later...
            if(String::startsWith('select ', String::lower($sqlStatement))) {
                return $pdoStatement->fetchAll(PDO::FETCH_ASSOC);
            }
            else {
                return $pdoStatement->rowCount();
            }
        }
        catch(PDOException $e) {
            throw new DatabaseException('An error occurred during the query, Exception: ' . $e->__toString());
        }
    }

    /**
     * Get the latest Sql state and PDO error
     * info in an associative array.
     *
     * @return array
     */
    public function getLastErrorInfo() {
        $this->openConnection();
        return $this->pdo->errorInfo();
    }

    /**
     * The Sql State from the last Sql operation on this driver.
     *
     * @return string
     */
    public function getLastSqlState() {
        $this->openConnection();
        return $this->pdo->errorCode();
    }

    /**
     * Get the id of this DatabaseDriver.
     *
     * @return string The id of this DatabaseDriver.
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Used by logging module to log messages to the database.
     */
    public abstract function log($tableName, Array $logMessages);
}

?>
