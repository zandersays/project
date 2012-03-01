<?php

/**
 * Description of ModelDatabaseContext
 *
 * @author Kam Sheffield
 * @version 09/13/2011
 */
class ModelDatabaseContext {

    /**
     *
     * @var string
     */
    protected $databaseId;

    /**
     *
     * @var ModelDriver
     */
    protected $modelDriver;

    /**
     *
     * @var boolean
     */
    protected $isPreparing;

    /**
     *
     * @var array
     */
    protected $insertTable;

    /**
     *
     * @var array
     */
    protected $updateTable;

    /**
     *
     * @var array
     */
    protected $deleteTable;

    /**
     *
     * @var type
     */
    protected $isDebug;

    /**
     *
     * @param DatabaseDriver $databaseDriver
     */
    public function __construct(DatabaseDriver $databaseDriver) {
        $this->databaseId = $databaseDriver->getId();
        $this->modelDriver = ModelDriverFactory::create($databaseDriver);
        $this->isPreparing = false;
        $this->insertTable = array();
        $this->updateTable = array();
        $this->deleteTable = array();
        $this->isDebug = false;
    }

    /**
     *
     * @return string
     */
    public function getDatabaseId() {
        return $this->databaseId;
    }

    /**
     *
     * @return boolean
     */
    public function isPreparing() {
        return $this->isPreparing;
    }

    /**
     *
     * @return boolean
     */
    public function isDebug() {
        return $this->isDebug;
    }

    /**
     *
     * @param boolean $isDebug
     */
    public function setIsDebug($isDebug) {
        $this->isDebug = $isDebug;
    }

    /**
     * Currently unused.
     */
    public function prepare() {
        /*
        if(!$this->isPreparing) {
            $this->isPreparing = true;
        }
        */
    }

    /**
     * Currently unused
     */
    public function commit() {
        /*
        if($this->isPreparing) {

            $this->isPreparing = false;
        }
        */
    }

    /**
     *
     * @param array $selectorProperties
     * @return array
     */
    public function selectModels(Array &$selectorProperties, $toSql = false) {
        $sqlAndBindArray = $this->modelDriver->prepareSelectStatementForSelector($selectorProperties);
        if($toSql || $this->isDebug) {
            $countStatement = $this->bindStatement($sqlAndBindArray['countSql'], $sqlAndBindArray['bindArray']);
            $selectStatement = $this->bindStatement($sqlAndBindArray['selectSql'], $sqlAndBindArray['bindArray']);
            return array(
                'countSql' => $countStatement,
                'selectSql' => $selectStatement
            );
        }
        else {
            return $this->modelDriver->modelSelect($sqlAndBindArray);
        }
    }

    /**
     *
     * @param array $selectorProperties
     * @param boolean $toSql
     * @return mixed
     */
    public function updateModelViaSelector(Array &$selectorProperties, $toSql = false) {
        if($this->isPreparing) {
            // TODO fill me in
        }
        else {
            // create a statement properties array
            $statementProperties = array(
                'statement' => '',
                'bindArray' => array(),
                'bindIndex' => 0
            );

            // create the sql to be used
            $statement = $this->modelDriver->prepareUpdateStatementForSelector($selectorProperties, $statementProperties);

            // are we looking at the sql or executing it?
            if($toSql || $this->isDebug) {
                return $this->bindStatement($statement, $statementProperties['bindArray']);
            }
            else {
                return $this->modelDriver->executePreparedStatement($statement, $statementProperties['bindArray']);
            }
        }
    }

    /**
     *
     * @param array $selectorProperties
     * @param boolean $toSql
     * @return mixed
     */
    public function deleteModelViaSelector(Array &$selectorProperties, $toSql = false) {
        if($this->isPreparing) {
            // TODO fill me in
        }
        else {
            // create a statement properties array
            $statementProperties = array(
                'statement' => '',
                'bindArray' => array(),
                'bindIndex' => 0
            );

            // create the sql to be used
            $statement = $this->modelDriver->prepareDeleteStatementForSelector($selectorProperties, $statementProperties);

            // are we looking at the sql or executing it?
            if($toSql || $this->isDebug) {
                return $this->bindStatement($statement, $statementProperties['bindArray']);
            }
            else {
                return $this->modelDriver->executePreparedStatement($statement, $statementProperties['bindArray']);
            }
        }
    }

    /**
     *
     * @param string $tableName
     * @param array $changedFields
     */
    public function insertModel($tableName, Array $changedFields) {
        if($this->isPreparing) {
            $this->insertTable[] = array(
                'tableName' => $tableName,
                'fieldArray' => $changedFields
            );
        }
        else {
            $modelProperties = array(
                array(
                    'tableName' => $tableName,
                    'fieldArray' => $changedFields
                )
            );

            // create the statement properties object to pass in
            $statementProperties = array(
                'statement' => '',
                'bindArray' => array(),
                'bindIndex' => 0
            );

            $statement = $this->modelDriver->prepareInsertStatementForModel($modelProperties, $statementProperties);
            if($this->isDebug) {
                return $this->bindStatement($statement, $statementProperties['bindArray']);
            }
            return $this->modelDriver->executePreparedStatement($statement, $statementProperties['bindArray']);
        }
    }

    /**
     *
     * @param string $tableName
     * @param string $primaryKeyName
     * @param string $primaryKeyValue
     * @param array $changedFields
     */
    public function updateModel($tableName, $primaryKeyName, $primaryKeyValue, Array $changedFields) {
        if($this->isPreparing) {
            $this->updateTable[] = array(
                'tableName' => $tableName,
                'primaryKeyName' => $primaryKeyName,
                'primaryKeyValue' => $primaryKeyValue,
                'fieldArray' => $changedFields
            );
        }
        else {
            $modelProperties = array(
                array(
                    'tableName' => $tableName,
                    'primaryKeyName' => $primaryKeyName,
                    'primaryKeyValue' => $primaryKeyValue,
                    'fieldArray' => $changedFields
                )
            );

            // create the statement properties object to pass in
            $statementProperties = array(
                'statement' => '',
                'bindArray' => array(),
                'bindIndex' => 0
            );

            $statement = $this->modelDriver->prepareUpdateStatementForModel($modelProperties, $statementProperties);
            if($this->isDebug) {
                return $this->bindStatement($statement, $statementProperties['bindArray']);
            }
            return $this->modelDriver->executePreparedStatement($statement, $statementProperties['bindArray']);
        }
    }



    /**
     *
     * @param string $tableName
     * @param string $primaryKeyName
     * @param string $primaryKeyValue
     */
    public function deleteModel($tableName, $primaryKeyName, $primaryKeyValue) {
        if($this->isPreparing) {
            // add the model info to the delete table
            if(!array_key_exists($tableName, $this->deleteTable)) {
                $this->deleteTable[$tableName] = array();
            }

            if(!array_key_exists($primaryKeyName, $this->deleteTable[$tableName])) {
                $this->deleteTable[$tableName][$primaryKeyName] = array();
            }

            $this->deleteTable[$tableName][$primaryKeyName][] = $primaryKeyValue;
        }
        else {
            // create an array that looks like the delete table with only one entry
            $modelProperties = array(
                    $tableName => array(
                        $primaryKeyName => array(
                            $primaryKeyValue
                        )
                    )
                );

            // create the statement properties object to pass in
            $statementProperties = array(
                'statement' => '',
                'bindArray' => array(),
                'bindIndex' => 0
            );

            // create the delete statement
            $statement = $this->modelDriver->prepareDeleteStatementForModel($modelProperties, $statementProperties);
            if($this->isDebug) {
                return $this->bindStatement($statement, $statementProperties['bindArray']);
            }
            // execute the delete statement
            return $this->modelDriver->executePreparedStatement($statement, $statementProperties['bindArray']);
        }
    }

    private function bindStatement($statement, Array &$bindArray) {
        if(!String::isNullOrEmpty($statement)) {
            foreach($bindArray as $tableName => $columnsToBind) {
                foreach($columnsToBind as $columnName => $bindPropertyArray) {
                    foreach($bindPropertyArray as $bindProperty) {
                        $statement = String::replace($bindProperty['key'], '\''.$bindProperty['value'].'\'', $statement);
                    }
                }
            }
        }
        return $statement;
    }

    /**
     *
     * @param string $tableName
     * @param string $primaryKeyName
     * @param string $primaryKeyValue
     * @return array
     */
    public function refreshModel($tableName, $primaryKeyName, $primaryKeyValue) {
        if($primaryKeyValue === null || empty($primaryKeyValue)){
            //TODO biggest assumption in the ORM
            $primaryKeyValue = $this->modelDriver->getLastInsertId();
        }

        $results = array();
        try {
            $databaseDriver = Database::getDatabaseDriver($this->databaseId);
            $results = $databaseDriver->query('SELECT * FROM `'.$tableName.'` WHERE `'.$primaryKeyName.'` = '.$primaryKeyValue.';');
        }
        catch(Exception $e) {
            throw new ModelException('An error occurred during refreshModel()', $e);
        }

        if($results != null && count($results) > 0) {
            return $results[0];
        }
        else {
            return array();
        }
    }
}

?>
