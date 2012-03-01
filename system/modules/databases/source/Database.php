<?php

/**
 * API for the Database module.
 *
 * This class wraps the DatabaseManager
 * and DatabaseDrivers into a nice API.
 *
 * @author Kam Sheffield
 * @version 08/23/2011
 */
class Database {

    /**
     * Get the DatabaseDriver attached to the
     * database with $databaseId.
     *
     * If the $databaseId is null the default driver is returned if there
     * is one, otherwise an exception is thrown.
     *
     * @param int $databaseDriverId The id of the database for the driver you want to get.
     * @return DatabaseDriver The driver for the database with the $databaseId specified.
     * @exception DatabaseException | If an error occurs during the database operation.
     */
    public static function getDatabaseDriver($databaseDriverId = null){
        return DatabaseManager::getInstance()->getDatabaseDriver($databaseDriverId);
    }

    /**
     * Get a list of all currently instanced DatabaseDrivers.
     *
     * @return array A list of all current DatabaseDrivers
     */
    public static function getAllDatabaseDrivers() {
        return DatabaseManager::getInstance()->getAllDatabaseDrivers();
    }

    /**
     * Adds a new database driver to the underlying
     * DatabaseManager.
     *
     * @param DatabaseDriver $databaseDriver The driver you want to add.
     * @exception DatabaseException : If a DatabaseDriver with the same Id already exists.
     */
    public static function addDatabaseDriver($databaseDriver){
        return DatabaseManager::getInstance()->addDatabaseDriver($databaseDriver);
    }

    /**
     * Removes the DatabaseDriver with the $databaseId
     * specified from the underlying DatabaseManager.
     *
     * @param int $databaseDriverId The id of the DatabaseDriver you want to remove.
     * @exception DatabaseException | If an error occurs during the database operation.
     */
    public static function removeDatabaseDriver($databaseDriverId){
        return DatabaseManager::getInstance()->removeDatabaseDriver($databaseDriverId);
    }

    /**
     * Get the id of the default DatabaseDriver.
     *
     * @return int The id of the default DatabaseDriver, or null if no default exists.
     */
    public function getDefaultDatabaseDriverId() {
        return DatabaseManager::getInstance()->getDefaultDatabaseDriverId();
    }

    /**
     * Set the id of the default DatabaseDriver.
     *
     * @param int $databaseDriverId The id of the new default DatabaseDriver.
     * @exception DatabaseException | If an error occurs during the database operation.
     */
    public function setDefaultDatabaseDriverId($databaseDriverId) {
        return DatabaseManager::getInstance()->setDefaultDatabaseDriverId($databaseDriverId);
    }

    /**
     * Get the default DatabaseDriver.
     *
     * @return DatabaseDriver The default DatabaseDriver.
     */
    public static function getDefaultDatabaseDriver() {
        return DatabaseManager::getInstance()->getDefaultDatabaseDriver();
    }

    /**
     * Set the default DatabaseDriver to use.
     *
     * @param DatabaseDriver $databaseDriver The DatabaseDriver you want to use as default.
     */
    public static function setDefaultDatabaseDriver($databaseDriver) {
        return DatabaseManager::getInstance()->setDefaultDatabaseDriver($databaseDriver);
    }

    /**
     * Whether or not the database
     * with $databaseId is currently in a transaction.
     *
     * If no $databaseDriverId is specified the default database is assumed.
     *
     * @param int $databaseDriverId The id of the database you want to check the transaction state on.
     * @return boolean Whether or not the database is currently in a transaction.
     * @exception DatabaseException | If an error occurs during the database operation.
     */
    public static function inTransaction($databaseDriverId = null) {
        $databaseDriver = DatabaseManager::getInstance()->getDatabaseDriver($databaseDriverId);
        return $databaseDriver->inTransaction();
    }

    /**
     * Start a transaction on the database
     * with the $databaseId specified.
     *
     * If no $databaseDriverId is specified the default database is assumed.
     *
     * @param int $databaseDriverId The id of the database you want to start a transaction on.
     * @exception DatabaseException | If an error occurs during the database operation.
     */
    public static function startTransaction($databaseDriverId = null) {
        $databaseDriver = DatabaseManager::getInstance()->getDatabaseDriver($databaseDriverId);
        return $databaseDriver->startTransaction();
    }

    /**
     * Commits a transaction on the database
     * with the $databaseId specified.
     *
     * If no $databaseDriverId is specified the default database is assumed.
     *
     * @param int $databaseDriverId The id of the database you want to commit a transaction on.
     * @exception DatabaseException | If an error occurs during the database operation.
     */
    public static function commitTransaction($databaseDriverId = null) {
        $databaseDriver = DatabaseManager::getInstance()->getDatabaseDriver($databaseDriverId);
        return $databaseDriver->commitTransaction();
    }

    /**
     * Rollback a transaction on the database
     * with the $databaseId specified.
     *
     * If no $databaseDriverId is specified the default database is assumed.
     *
     * @param int $databaseDriverId The id of the database you want to rollback a transaction on.
     * @exception DatabaseException | If an error occurs during the database operation.
     */
    public static function rollbackTransaction($databaseDriverId = null) {
        $databaseDriver = DatabaseManager::getInstance()->getDatabaseDriver($databaseDriverId);
        return $databaseDriver->rollbackTransaction();
    }

    /**
     * Escapes a string for the specific driver
     * backing the database for the id specified.
     *
     * If no $databaseDriverId is specified the default database is assumed.
     *
     * @param string $sqlString The SQL string you want to escape.
     * @param int $databaseDriverId The id of the database you want to escape the string for.
     * @return string Returns an escaped string that is theoretically safe to pass into a SQL statement.
     *                Returns FALSE if the driver does not support quoting in this way.
     * @exception DatabaseException | If an error occurs during the database operation
     */
    public static function escapeString($sqlString, $databaseDriverId = null) {
        $databaseDriver = DatabaseManager::getInstance()->getDatabaseDriver($databaseDriverId);
        return $databaseDriver->escapeString($sqlString);
    }

    /**
     * Returns the id from the last inserted
     * row in the database for the database with
     * the id specified.
     *
     * If no $databaseDriverId is specified the default database is assumed.
     *
     * @param int $databaseDriverId The id of the database you want to get the last insert id on.
     * @return int The last insert id on that database.
     * @exception DatabaseException | If an error occurs during the database operation.
     */
    public static function getLastInsertId($databaseDriverId = null) {
        $databaseDriver = DatabaseManager::getInstance()->getDatabaseDriver($databaseDriverId);
        return $databaseDriver->getLastInsertId();
    }

    /**
     * Get whether or not databases will
     * attempt to use persistent connections.
     *
     * @return boolean
     */
    public static function getUsePersistentConnections(){
        return DatabaseManager::getInstance()->getUsePersistentConnections();
    }

    /**
     * Set whether or not databases will
     * attempt to use persistent connections.
     *
     * @param boolean $value
     */
    public static function setUsePersistentConnections($value = true){
        return DatabaseManager::getInstance()->setUsePersistentConnections($value);
    }

    /**
     * Get the value of the PDOAttribute
     * specified associated with the database specified.
     *
     * If no $databaseDriverId is specified the default database is assumed.
     *
     * @param int $pdoAttribute The PDOAttribute you want to get.
     * @param int $databaseDriverId The id of the database you want to use for this operation.
     * @return mixed The value of that PDOAttribute.
     * @exception DatabaseException | If an error occurs during the database operation.
     */
    public static function getAttribute($pdoAttribute, $databaseDriverId = null) {
        $databaseDriver = DatabaseManager::getInstance()->getDatabaseDriver($databaseDriverId);
        return $databaseDriver->getAttribute($pdoAttribute);
    }

    /**
     * Set the PDOAttribute specifed with
     * the value specified for the database
     * with the id specified.
     *
     * If no $databaseDriverId is specified the default database is assumed.
     *
     * @param int $pdoAttribute The PDOAttribute you want to set.
     * @param int $value The value you want to set the PDOAttribute to.
     * @param int $databaseDriverId The id of the database you want to use for this operation.
     * @return bool Whether or not the operation was successful
     * @exception DatabaseException | If an error occurs during the database operation.
     */
    public static function setAttribute($pdoAttribute, $value, $databaseDriverId = null) {
        $databaseDriver = DatabaseManager::getInstance()->getDatabaseDriver($databaseDriverId);
        return $databaseDriver->setAttribute($pdoAttribute, $value);
    }

    /**
     * Get an associative array containing
     * the last error for the database specified.
     *
     * If no $databaseDriverId is specified the default database is assumed.
     *
     * @param int $databaseDriverId The id of the database you want to use for this operation.
     * @return array The last error info.
     * @exception DatabaseException | If an error occurs during the database operation.
     */
    public static function getLastErrorInfo($databaseDriverId = null) {
        $databaseDriver = DatabaseManager::getInstance()->getDatabaseDriver($databaseDriverId);
        return $databaseDriver->getLastErrorInfo();
    }

    /**
     * Get the SQL state returned from the
     * last SQL operation on the database specified.
     *
     * If no $databaseDriverId is specified the default database is assumed.
     *
     * @param int $databaseDriverId The id of the database you want to use for this operation.
     * @return string The last SQL state.
     * @exception DatabaseException | If an error occurs during the database operation.
     */
    public static function getLastSqlState($databaseDriverId = null) {
        $databaseDriver = DatabaseManager::getInstance()->getDatabaseDriver($databaseDriverId);
        return $databaseDriver->getLastSqlState();
    }

    /**
     * Create a new prepared statement for the
     * database specified.
     *
     * If no $databaseDriverId is specified the default database is assumed.
     *
     * @param string $sqlStatement The SQL to prepare.
     * @param int $databaseDriverId The id of the database you want to use for this operation.
     * @param array $driverOptions Any additional options you want passed into the driver.
     * @return PDOStatement A PDOStatment object for executing the prepared statement.
     * @exception DatabaseException | If an error occurs during the database operation.
     */
    public static function prepare($sqlStatement, $databaseDriverId = null, $driverOptions = null) {
        if($driverOptions == null) {
            $driverOptions = array();
        }

        $databaseDriver = DatabaseManager::getInstance()->getDatabaseDriver($databaseDriverId);
        return $databaseDriver->prepare($sqlStatement, $driverOptions);
    }

    /**
     * Create and run a SQL statement
     * against the database specified.
     * The query string should be properly escaped.
     *
     * If no $databaseDriverId is specified the default database is assumed.
     *
     * @param string $sqlStatement The SQL you want to use.
     * @param int $databaseDriverId The id of the database you want to use for this operation.
     * @return array An associative array with the results of the query, or an int that is the count of the number of affected rows.
     * @exception DatabaseException | If an error occurs during the database operation.
     */
    public static function query($sqlStatement, $databaseDriverId = null) {
        $databaseDriver = DatabaseManager::getInstance()->getDatabaseDriver($databaseDriverId);
        return $databaseDriver->query($sqlStatement);
    }
}
?>
