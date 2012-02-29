<?php

/**
 * A ModelDriver contains a DatabaseDriver
 * and calls functions catered for the Models
 * module on the DatabaseDriver.
 *
 * @author Kam Sheffield
 * @version 09/13/2011
 */
abstract class ModelDriver {

    /**
     * The DatabaseDriver hooked to
     * this ModelDriver.
     *
     * @var DatabaseDriver
     */
    protected $databaseDriver;

    /**
     * Creates a new instance of a ModelDriver.
     *
     * @param DatabaseDriver $databaseDriver
     */
    public function __construct($databaseDriver) {
        $this->databaseDriver = $databaseDriver;
    }

    public function getLastInsertId() {
        return $this->databaseDriver->getLastInsertId();
    }

    public abstract function getTableNames();

    public abstract function getDescriptionForTable($tableName);

    public abstract function getIndexesForTable($tableName);

    public abstract function getForeignKeysForTable($tableName);

    public abstract function getTablesRelatedToTable($tableName);

    public abstract function parseDataTypeForPhp($sqlType);

    public abstract function parseDataTypeForPhpPdo($sqlType);

    public abstract function createTable(Table $table, $emitSql = false);

    public abstract function alterTable(TableAlterer $tableAlterer, $emitSql = false);

    public abstract function truncateTable($tableName, $emitSql = false);

    public abstract function dropTable($tableName, $emitSql = false);

    public abstract function prepareSelectStatementForSelector(Array &$selectorProperties);

    public abstract function prepareUpdateStatementForSelector(Array &$selectorProperties, Array &$statementProperties);

    public abstract function prepareDeleteStatementForSelector(Array &$selectorProperties, Array &$statementProperties);

    public abstract function prepareInsertStatementForModel(Array &$modelProperties, Array &$statementProperties);

    public abstract function prepareUpdateStatementForModel(Array &$modelProperties, Array &$statementProperties);

    public abstract function prepareDeleteStatementForModel(Array &$modelProperties, Array &$statementProperties);

    public abstract function executePreparedStatement($sqlStatement, Array &$bindArray);

    public abstract function modelSelect(Array &$sqlAndBindArray);
}

?>
