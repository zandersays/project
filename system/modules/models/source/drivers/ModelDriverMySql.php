<?php

/**
 * Implementation of a ModelDriver for
 * the DatabaseDriverMySql.
 *
 * @author Kam Sheffield
 * @version 09/14/2011
 */
class ModelDriverMySql extends ModelDriver {

    /**
     * Creates a new instance of the ModelDriverMySql.
     *
     * @param DatabaseDriverMySql $databaseDriver
     */
    public function __construct($databaseDriver) {
        parent::__construct($databaseDriver);
    }

    /**
     * Get an array containg the names of all of the
     * tables in the database connected to this driver.
     *
     * @return array
     */
    public function getTableNames() {
        try {
            $pdoStatement = $this->databaseDriver->prepare('SHOW TABLES');
            $pdoStatement->execute();
            $results = $pdoStatement->fetchAll(PDO::FETCH_ASSOC);
            $tableNames = array();
            $tableIndex = 'Tables_in_'.$this->databaseDriver->getDatabaseName();
            foreach($results as $table) {
                $tableNames[] = $table[$tableIndex];
            }
            return $tableNames;
        }
        catch(Exception $e) {
            throw new ModelException('An error occurred getting the table names for database: '.$this->databaseDriver->getDatabaseName().', Exception: '.$e->__toString(), $e);
        }
    }

    /**
     *
     * @param string $tableName
     * @return array
     */
    public function getDescriptionForTable($tableName) {
        try {
            $pdoStatement = $this->databaseDriver->prepare('DESCRIBE `'.$this->databaseDriver->getDatabaseName().'`.`'.$tableName.'`;');
            $pdoStatement->execute();
            return $pdoStatement->fetchAll(PDO::FETCH_ASSOC);
        }
        catch(Exception $e) {
            throw new ModelException('An error occurred getting the table description for table: '.$tableName.', Exception: '.$e->__toString(), $e);
        }
    }

    public function getIndexesForTable($tableName) {
        try {
            $pdoStatement = $this->databaseDriver->prepare('SHOW INDEXES FROM `'.$this->databaseDriver->getDatabaseName().'`.`'.$tableName.'`;');
            $pdoStatement->execute();
            return $pdoStatement->fetchAll(PDO::FETCH_ASSOC);
        }
        catch(Exception $e) {
            throw new ModelException('An error occurred getting the indexes for table: '.$tableName.', Exception: '.$e->__toString(), $e);
        }
    }

    public function getForeignKeysForTable($tableName) {
        try {
            $foreignKeys = $this->databaseDriver->getForeignKeysForTable($tableName);
            return $foreignKeys;
        }
        catch(Exception $e) {
            throw new ModelException('An error occurred getting the foreign keys for table: '.$tableName.', Exception: '.$e->__toString(), $e);
        }
    }

    public function getTablesRelatedToTable($tableName) {
        try {
            $relatedTables = $this->databaseDriver->getTablesRelatedToTable($tableName);
            return $relatedTables;
        }
        catch(Exception $e) {
            throw new ModelException('An error occurred getting the foreign keys for table: '.$tableName.', Exception: '.$e->__toString(), $e);
        }
    }

    public function createTable(Table $table, $emitSql = false) {
        $sql = 'CREATE TABLE IF NOT EXISTS `'.$this->databaseDriver->getDatabaseName().'`.`'.$table->getName().'` (';

        $columnString = '';
        foreach($table->getColumnArray() as $tableColumn) {
            $columnString .= $this->createTableColumnString($tableColumn).",\n  ";
        }

        $indexString = '';
        foreach($table->getIndexArray() as $tableIndex) {
            $indexString .= $this->createTableIndexString($tableIndex).",\n  ";
        }

        $foreignKeyConstraintString = '';
        foreach($table->getForeignKeyConstraintArray() as $foreignKeyConstraint) {
            $foreignKeyConstraintString .= $this->createForeignKeyConstraintString($foreignKeyConstraint).",\n  ";
        }

        $engineString = 'ENGINE = '.$table->getEngine();
        $characterSetString = 'DEFAULT CHARACTER SET = '.$table->getCharacterSet();

        $sql .= "\n  ".String::replaceLast(',', '', $columnString.$indexString.$foreignKeyConstraintString).")\n".$engineString."\n".$characterSetString.';';

        return $this->executeTableStatement($sql, $emitSql);
    }

    public function createTableColumnString(TableColumn $tableColumn) {
        if($tableColumn->getName() == null || $tableColumn->getDataType() == null) {
            throw new ModelException('createTableColumnString(): A name and datatype are required to before a column can be instanced.');
        }

        $sql = '`'.$tableColumn->getName().'` '.$tableColumn->getDataType();
        if($tableColumn->getLength() != null) {
            $sql .= '('.$tableColumn->getLength().')';
        }

        if($tableColumn->getIsUnsigned()) {
            $sql .= ' UNSIGNED';
        }

        if($tableColumn->getIsBinary()) {
            $sql .= ' BINARY';
        }

        if($tableColumn->getIsZeroFill()) {
            $sql .= ' ZEROFILL';
        }

        if($tableColumn->getIsNonNull()) {
            $sql .= ' NOT NULL';
        }
        else {
            $sql .= ' NULL';
        }

        if($tableColumn->getIsAutoIncrementing()) {
            $sql .= ' AUTO_INCREMENT';
        }

        if($tableColumn->getDefaultValue() != null) {
            $sql .= ' DEFAULT \''.$tableColumn->getDefaultValue().'\'';
        }

        return $sql;
    }

    public function createTableIndexString(TableIndex $tableIndex) {
        if(count($tableIndex->getColumnsIndexedArray()) <= 0 || String::isNullOrEmpty($tableIndex->getName()) ||  String::isNullOrEmpty($tableIndex->getType())) {
            throw new ModelException('createTableIndexString(): A indexed column, name, and type are all required for a table index');
        }

        $sql = '';

        if($tableIndex->getType() == TableIndexType::Primary) {
            $sql .= 'PRIMARY KEY (';
            foreach($tableIndex->getColumnsIndexedArray() as $columnToIndex) {
                $sql .= '`'.$columnToIndex['columnName'].'`, ';
            }
            $sql = String::replaceLast(', ', ')', $sql);
            return $sql;
        }
        if($tableIndex->getType() == TableIndexType::Spatial) {
            $sql .= TableIndexType::Spatial.' INDEX';
        }
        else if($tableIndex->getType() == TableIndexType::Unique) {
            $sql .= TableIndexType::Unique.' INDEX';
        }
        else if($tableIndex->getType() == TableIndexType::FullText) {
            $sql .= TableIndexType::FullText.' INDEX';
        }
        else {
            $sql .= 'INDEX';
        }

        $sql .= ' `'.$tableIndex->getName().'` (';
        foreach($tableIndex->getColumnsIndexedArray() as $columnToIndex) {
            $sql .= '`'.$columnToIndex['columnName'].'`';
            if($columnToIndex['length'] != null) {
                $sql .= '('.$columnToIndex['length'].')';
            }

            if($columnToIndex['isOrderAscending']) {
                $sql .= ' ASC, ';
            }
            else {
                $sql .= ' DESC, ';
            }
        }
        $sql = String::replaceLast(', ', '', $sql);
        $sql .= ')';
        return $sql;
    }

    public function createForeignKeyConstraintString(ForeignKeyConstraint $foreignKeyConstraint) {
        $sql = 'CONSTRAINT `'.$foreignKeyConstraint->getName().'`';

        $fkString = 'FOREIGN KEY (`'.$foreignKeyConstraint->getColumnName().'`)';
        $refString = 'REFERENCES `'.$this->databaseDriver->getDatabaseName().'`.`'.$foreignKeyConstraint->getReferencedTableName().'` (`'.$foreignKeyConstraint->getReferencedColumnName().'`)';
        $deleteString = 'ON DELETE '.$foreignKeyConstraint->getDeleteAction();
        $updateString = 'ON UPDATE '.$foreignKeyConstraint->getUpdateAction();

        $sql .= "\n    ".$fkString."\n    ".$refString."\n    ".$deleteString."\n    ".$updateString;
        return $sql;
    }

    public function alterTable(TableAlterer $tableAlterer, $emitSql = false) {
        $sql = 'ALTER TABLE `'.$tableAlterer->getTable()->getName()."`\n";

        if(!String::isNullOrEmpty($tableAlterer->getNewTableName())) {
            $sql .= ' RENAME `'.$tableAlterer->getNewTableName()."`,\n";
        }

        foreach($tableAlterer->getColumnsToAdd() as $columnToAdd) {
            $sql .= ' ADD '.$this->createTableColumnString($columnToAdd).",\n";
        }

        foreach($tableAlterer->getColumnsToAlter() as $columnName => $alteredColumn) {
            $sql .= ' CHANGE `'.$columnName.'` '.$this->createTableColumnString($alteredColumn).",\n";
        }

        foreach($tableAlterer->getColumnsToRemove() as $columnToRemove) {
            $sql .= ' DROP COLUMN `'.$columnToRemove."`,\n";
        }

        foreach($tableAlterer->getIndexesToAdd() as $indexToAdd) {
            $sql .= ' ADD '.$this->createTableIndexString($indexToAdd).",\n";
        }

        foreach($tableAlterer->getIndexesToRemove() as $indexToRemove) {
            $sql .= ' DROP INDEX `'.$indexToRemove."`,\n";
        }

        if(!String::isNullOrEmpty($tableAlterer->getNewCharacterSet())) {
            $sql .= ' CONVERT TO CHARACTER SET '.$tableAlterer->getNewCharacterSet().",\n";
        }

        if(!String::isNullOrEmpty($tableAlterer->getNewEngine())) {
            $sql .= ' ENGINE = '.$tableAlterer->getNewEngine().",\n";
        }

        $sql = String::replaceLast(',', ';', $sql);

        foreach($tableAlterer->getForeignKeysToAdd() as $foreignKeyToAdd) {
            $sql .= 'ALTER TABLE `'.$tableAlterer->getTable()->getName()."`\n ADD ".$this->createForeignKeyConstraintString($foreignKeyToAdd).";\n";
        }

        foreach($tableAlterer->getForeignKeysToRemove() as $foreignKeyToRemove) {
            $sql .= 'ALTER TABLE `'.$tableAlterer->getTable()->getName()."`\n DROP `".$foreignKeyToRemove."`;\n";
        }

        return $this->executeTableStatement($sql, $emitSql);
    }

    public function truncateTable($tableName, $emitSql = false) {
        return $this->executeTableStatement('TRUNCATE TABLE `'.$tableName.'`', $emitSql);
    }

    public function dropTable($tableName, $emitSql = false) {
        return $this->executeTableStatement('DROP TABLE `'.$tableName.'` CASCADE', $emitSql);
    }

    private function executeTableStatement($sql, $emitSql) {
        if($emitSql) {
            return $sql;
        }

        $returnState = array();
        $exceptionOccurred = false;
        $statement = $this->databaseDriver->prepare($sql);

        try {
            $statement->execute();
        }
        catch(Exception $e) {
            $exceptionOccurred = true;
            $returnState['exception'] = $e->__toString();
        }

        $errorInfo = $statement->errorInfo();
        $returnState['sqlState'] = $errorInfo[0];
        $returnState['errorCode'] = $errorInfo[1];
        $returnState['errorMessage'] = $errorInfo[2];

        if($statement->errorCode() == '00000' && !$exceptionOccurred) {
            $returnState['status'] = 'success';
        }
        else {
            $returnState['status'] = 'failure';
        }

        return $returnState;
    }

    public function parseDataTypeForPhp($sqlType) {
        if(String::contains('int', $sqlType) || String::contains('bit', $sqlType)) {
            return 'int';
        }
        else if(String::contains('char', $sqlType) || String::contains('binary', $sqlType) || String::contains('blob', $sqlType)) {
            return 'string';
        }
        else if(String::contains('bool', $sqlType)) {
            return 'bool';
        }
        else if(String::contains('enum', $sqlType) || String::contains('set', $sqlType) || String::contains('text', $sqlType)) {
            return 'string';
        }
        else if(String::contains('float', $sqlType) || String::contains('double', $sqlType) || String::contains('dec', $sqlType)) {
            return 'float';
        }
        else if(String::contains('date', $sqlType) || String::contains('time', $sqlType) || String::contains('year', $sqlType)) {
            return 'string';
        }
        else {
            return 'string';
        }
    }

    public function parseDataTypeForPhpPdo($sqlType) {
        if(String::contains('int', $sqlType) || String::contains('bit', $sqlType)) {
            return PDO::PARAM_INT;
        }
        else if(String::contains('char', $sqlType) || String::contains('binary', $sqlType) || String::contains('blob', $sqlType)) {
            return PDO::PARAM_STR;
        }
        else if(String::contains('bool', $sqlType)) {
            return PDO::PARAM_BOOL;
        }
        else if(String::contains('enum', $sqlType) || String::contains('set', $sqlType) || String::contains('text', $sqlType)) {
            return PDO::PARAM_STR;
        }
        else if(String::contains('float', $sqlType) || String::contains('double', $sqlType) || String::contains('dec', $sqlType)) {
            return PDO::PARAM_INT;
        }
        else if(String::contains('date', $sqlType) || String::contains('time', $sqlType) || String::contains('year', $sqlType)) {
            return PDO::PARAM_STR;
        }
        else {
            return PDO::PARAM_STR;
        }
    }

    public function prepareSelectStatementForSelector(Array &$selectorProperties) {
        // if raw sql was specified, ignore everything else
        if($selectorProperties['sql'] != null) {
            $countSql = '';
            if($selectorProperties['isCount']) {
                $countSql = 'SELECT COUNT(*) ';
                $countSql .= String::sub($selectorProperties['sql'], String::position("FROM", $selectorProperties['sql']), String::length($selectorProperties['sql']));
            }

            return array(
                'selectSql' => $selectorProperties['sql'],
                'countSql' => $countSql,
                'bindArray' => array(),
                'isSelect' => $selectorProperties['isSelect'],
                'isCount' => $selectorProperties['isCount']
            );
        }

        $fieldArray = array();
        $baseTable = $selectorProperties['tableName'];
        $tableString = '`'.$baseTable.'`';      // the list of tables to get the columns from
        $columnString = ' ';     // the list of columns to select
        $joinString = '';
        $whereString = '';
        $count = 0;
        $statementProperties = array('bindIndex' => 0, 'bindArray' => array());
        foreach($selectorProperties['tableArray'] as $selectorData) {
            $tableName = $selectorData['tableName'];

            // build the string of columns we are selecting
            if($selectorData['withColumns'] === null) {
                // if no columns array was selected then we know we are selecting all columns
                $columnString .= '`'.$tableName.'`.*, ';
            }
            else {
                $primaryKey = Model::getModelMeta($selectorData['tableName'], 'primaryKey');
                if(!Arr::contains($primaryKey, $selectorData['withColumns'])) {
                    $columnString .= '`'.$tableName.'`.`'.$primaryKey.'`, ';
                }
                else {
                    foreach($selectorData['withColumns'] as $columnName) {
                        $columnString .= '`'.$tableName.'`.`'.$columnName.'`, ';
                    }
                }
            }

            // build the string of relations to enforce
            if(count($selectorData['withRelations'] > 0)) {
                foreach($selectorData['withRelations'] as $relationArray) {
                    $joinString .= ' LEFT OUTER JOIN `'.$relationArray['relatedTable'].'` AS `'.$relationArray['relatedTableAlias'].'` ON `'.$relationArray['table'].'`.`'.$relationArray['column'].'` = `'.$relationArray['relatedTableAlias'].'`.`'.$relationArray['relatedColumn'].'`';
                }
            }

            // build the string of where statements
            if(count($selectorData['statements']) > 0) {
                $whereString .= '';
                foreach($selectorData['statements'] as $statement) {
                    if($statement['type'] == 'filterBy') {
                        $whereString .= $this->prepareFilterBy($tableName, $statement, $statementProperties);
                    }
                    else {
                        if($statement['concatenator'] != null) {
                            $whereString .= $statement['concatenator'].' ';
                        }
                        $whereString .= $statement['sql'].' ';
                    }
                }
            }
        }
        $columnString = String::replaceLast(', ', ' ', $columnString);
        $whereString = String::replaceLast(' ', '', $whereString);

        // handle the orderby clauses
        $orderByAscendingString = '';
        if(count($selectorProperties['orderByAscending']) > 0 ) {
            foreach($selectorProperties['orderByAscending'] as $columnName) {
                $orderByAscendingString .= '`'.$baseTable.'`.`'.$columnName.'`, ';
            }
            $orderByAscendingString = String::replaceLast(', ', '', $orderByAscendingString);
        }

        $orderByDescendingString = '';
        if(count($selectorProperties['orderByDescending']) > 0 ) {
            foreach($selectorProperties['orderByDescending'] as $columnName) {
                $orderByDescendingString .= '`'.$baseTable.'`.`'.$columnName.'`, ';
            }
            $orderByDescendingString = String::replaceLast(', ', '', $orderByDescendingString);
        }

        // handle the limit clause
        $limitString = '';
        if($selectorProperties['limit'] != null) {
            $limitString = $selectorProperties['limit']['offset'].', '.$selectorProperties['limit']['rowCount'];
        }

        // add select distinct if required
        $sql = '';
        if($selectorProperties['isDistinct']) {
            $sql = 'SELECT DISTINCT'.$columnString.'FROM '.$tableString;
        }
        else {
            $sql = 'SELECT'.$columnString.'FROM '.$tableString;
        }

        // append the join
        if(!empty($joinString)) {
            $sql .= $joinString;
        }

        // append the where string
        if(!empty($whereString)) {
            $sql .= ' WHERE '.$whereString;
        }

        // add orderbys
        if(!empty($orderByAscendingString)) {
            $sql .= ' ORDER BY '.$orderByAscendingString.' ASC';
        }

        if(!empty($orderByDescendingString)) {
            if(!empty($orderByAscendingString)) {
                $sql .= ', '.$orderByDescendingString.' DESC';
            }
            else {
                $sql .= ' ORDER BY '.$orderByDescendingString.' DESC';
            }
        }

        // add limit
        if(!empty($limitString)) {
            $sql .= ' LIMIT '.$limitString;
        }

        $sql .= ';';

        // handle the get count
        $countSql = '';
        if($selectorProperties['isCount'] == true) {
            $countSql = 'SELECT COUNT(*) FROM '.$tableString;

            if(!empty($joinString)) {
                $countSql .= $joinString;
            }

            // append the where string
            if(!empty($whereString)) {
                $countSql .= ' WHERE '.$whereString;
            }

            $countSql .= ';';
        }

        return array(
            'selectSql' => $sql,
            'countSql' => $countSql,
            'bindArray' => $statementProperties['bindArray'],
            'isSelect' => $selectorProperties['isSelect'],
            'isCount' => $selectorProperties['isCount']
        );
    }

    public function prepareUpdateStatementForSelector(Array &$selectorProperties, Array &$statementProperties) {
        // was raw sql supplied?
        if($selectorProperties['sql'] != null) {
            // make sure they ended their sql with a ';'
            $length = String::length($selectorProperties['sql']);
            if($selectorProperties['sql']{$length - 1} != ';') {
                $selectorProperties['sql'] .= ';';
            }

            // add the statement to the total statement and return
            $statementProperties['statement'] .= $selectorProperties['sql'];
            return $selectorProperties['sql'];
        }

        // get the table name
        $tableName = $selectorProperties['tableName'];

        // start the sql
        $sql = 'UPDATE `'.$tableName.'` SET ';

        foreach($selectorProperties['fieldArray'] as $columnName => $columnValue) {
            $sql .= '`'.$tableName.'`.`'.$columnName.'` = ';
            if($columnValue{0} == ':') {
                $sql .= $columnValue.', ';
            }
            else {
                $binderKey = ':v'.$statementProperties['bindIndex'];
                $sql .= $binderKey.', ';
                $statementProperties['bindArray'][$tableName][$columnName][] = array('key' => $binderKey, 'value' => $columnValue);
                $statementProperties['bindIndex']++;
            }
        }

        $sql = String::replaceLast(', ', ' ', $sql);

        // parse the filterbys to create the where clause
        if(count($selectorProperties['filterByArray']) > 0) {
            $sql .= 'WHERE ';
            $sql .= $this->prepareFilterBy($tableName, $selectorProperties['filterByArray'], $statementProperties);
        }

        $sql = String::replaceLast(' ', ';', $sql);

        // append the statement to the total statement and return it
        $statementProperties['statement'] .= $sql;
        return $sql;
    }

    public function prepareDeleteStatementForSelector(Array &$selectorProperties, Array &$statementProperties) {
        // was raw sql supplied?
        if($selectorProperties['sql'] != null) {
            // make sure they ended their sql with a ';'
            $length = String::length($selectorProperties['sql']);
            if($selectorProperties['sql']{$length - 1} != ';') {
                $selectorProperties['sql'] .= ';';
            }

            // add the statement to the total statement and return
            $statementProperties['statement'] .= $selectorProperties['sql'];
            return $selectorProperties['sql'];
        }

        // get the table name
        $tableName = $selectorProperties['tableName'];

        // start the sql
        $sql = 'DELETE FROM `'.$tableName.'` ';

        // are there any filterbys to do?
        if(count($selectorProperties['filterByArray']) > 0) {
            $sql .= 'WHERE ';

            // parse the filterbys to create the where clause
            $sql .= $this->prepareFilterBy($tableName, $selectorProperties['filterByArray'], $statementProperties);
        }

        $sql = String::replaceLast(' ', ';', $sql);

        // append the statement to the total statement and return it
        $statementProperties['statement'] .= $sql;
        return $sql;
    }

    private function prepareFilterBy($tableName, Array &$filterByArray, Array &$statementProperties) {
        $whereString = '';
        if(!Arr::isAssociative($filterByArray)) {
            foreach($filterByArray as $filterBy) {
                if($filterBy['concatenator'] != null) {
                   $whereString .= $filterBy['concatenator'].' ';
                }

                if(Arr::is($filterBy['value'])) {
                    $whereString .= '(';
                    foreach($filterBy['value'] as $value) {
                        $whereString .= $this->filterByEntry($tableName, $filterBy['column'], $value, $filterBy['comparator'], $filterBy['flags'], $statementProperties).'OR ';
                    }
                    $whereString = String::replaceLast(' OR ', ') ', $whereString);
                }
                else {
                    $whereString .= $this->filterByEntry($tableName, $filterBy['column'], $filterBy['value'], $filterBy['comparator'], $filterBy['flags'], $statementProperties);
                }
            }
        }
        else {
            if($filterByArray['concatenator'] != null) {
               $whereString .= $filterByArray['concatenator'].' ';
            }

            if(Arr::is($filterByArray['value'])) {
                $whereString .= '(';
                foreach($filterByArray['value'] as $value) {
                    $whereString .= $this->filterByEntry($tableName, $filterByArray['column'], $value, $filterByArray['comparator'], $filterByArray['flags'], $statementProperties).'OR ';
                }
                $whereString = String::replaceLast(' OR ', ') ', $whereString);
            }
            else {
                $whereString .= $this->filterByEntry($tableName, $filterByArray['column'], $filterByArray['value'], $filterByArray['comparator'], $filterByArray['flags'], $statementProperties);
            }
        }

        return $whereString;
    }

    private function filterByEntry($tableName, $columnName, $columnValue, $comparator, $flags, Array &$statementProperties) {
        $whereString = '';

        // handle the flags
        if(FilterByFlags::isCaseInSensitive($flags)) {
            $whereString .= 'LOWER(`'.$tableName.'`.`'.$columnName.'`) '.$comparator.' ';

            // if we are not injecting sql then lower case the input
            if($columnValue[0] != ':') {
                $columnValue = String::lower($columnValue);
            }
        }
        else {
            $whereString .= '`'.$tableName.'`.`'.$columnName.'` '.$comparator.' ';
        }

        // add the bind information
        if($columnValue[0] == ':') {
            $whereString .= String::subString($columnValue, 1).' ';
        }
        else {
            $binderKey = ':v'.$statementProperties['bindIndex'];
            $whereString .= $binderKey.' ';
            $statementProperties['bindArray'][$tableName][$columnName][] = array('key' => $binderKey, 'value' => $columnValue);
            $statementProperties['bindIndex']++;
        }

        return $whereString;
    }

    public function prepareInsertStatementForModel(Array &$modelProperties, Array &$statementProperties) {
        $sql = '';
        $bindIndex = $statementProperties['bindIndex'];
        foreach($modelProperties as $modelProperty) {
            $tableName = $modelProperty['tableName'];
            $sql .= 'INSERT INTO `'.$tableName.'` (';
            $values = ') VALUES (';
            foreach($modelProperty['fieldArray'] as $columnName => $columnValue) {
                $sql .= '`'.$columnName.'`, ';

                // This needs to be fixed
                if(!String::isNullOrEmpty($columnValue) && $columnValue{0} == ':' && $columnValue != ':)' && $columnValue != ':D' && $columnValue != ':]' && 1 == 2) {
                    $values .= String::sub($columnValue, 1, String::length($columnValue)).', ';
                }
                else {
                    $bindValue = ':v'.$bindIndex;
                    $values .= $bindValue.', ';
                    $statementProperties['bindArray'][$tableName][$columnName][] = array('key' => $bindValue, 'value' => $columnValue);
                    $bindIndex++;
                }
            }
            $values = String::replaceLast(', ', ');', $values);
            $sql = String::replaceLast(', ', $values, $sql);
        }

        $statementProperties['statement'] .= $sql;
        $statementProperties['bindIndex'] = $bindIndex;
        
        //echo $sql; exit();

        return $sql;
    }

    public function prepareUpdateStatementForModel(Array &$modelProperties, Array &$statementProperties) {
        $sql = '';
        $bindIndex = $statementProperties['bindIndex'];
        foreach($modelProperties as $modelProperty) {
            $tableName = $modelProperty['tableName'];
            $sql .= 'UPDATE `'.$tableName.'` SET ';
            foreach($modelProperty['fieldArray'] as $columnName => $columnValue) {
                if($columnValue{0} == ':') {
                    $sql .= '`'.$columnName.'` = '.String::sub($columnValue, 1, String::length($columnValue)).', ';
                }
                else {
                    $bindValue = ':v'.$bindIndex;
                    $sql .= '`'.$columnName.'` = '.$bindValue.', ';
                    $statementProperties['bindArray'][$tableName][$columnName][] = array('key' => $bindValue, 'value' => $columnValue);
                    $bindIndex++;
                }
            }
            $sql = String::replaceLast(', ', ' WHERE ', $sql);

            $bindValue = ':v'.$bindIndex;
            $sql .= '`'.$modelProperty['primaryKeyName'].'` = '.$bindValue.';';
            $statementProperties['bindArray'][$tableName][$modelProperty['primaryKeyName']][] = array('key' => $bindValue, 'value' => $modelProperty['primaryKeyValue']);
            $bindIndex++;
        }

        $statementProperties['statement'] .= $sql;
        $statementProperties['bindIndex'] = $bindIndex;

        return $sql;
    }

    public function prepareDeleteStatementForModel(Array &$modelProperties, Array &$statementProperties) {
        $sql = '';
        $bindIndex = $statementProperties['bindIndex'];
        foreach($modelProperties as $tableName => $primaryKeyArray) {
            foreach($primaryKeyArray as $primaryKeyName => $primaryKeyValues) {
                $sql .= 'DELETE FROM `'.$tableName.'` WHERE `'.$primaryKeyName.'` IN (';
                foreach($primaryKeyValues as $primaryKeyValue) {
                    $bindValue = ':v'.$bindIndex;
                    $sql .= $bindValue.', ';
                    $statementProperties['bindArray'][$tableName][$primaryKeyName][] = array('key' => $bindValue, 'value' => $primaryKeyValue);
                    $bindIndex++;
                }
            }
            $sql = String::replaceLast(', ', ');', $sql);
        }

        $statementProperties['statement'] .= $sql;
        $statementProperties['bindIndex'] = $bindIndex;

        return $sql;
    }

    public function executePreparedStatement($sqlStatement, Array &$bindArray) {
        try {
            // create the statement
            $pdoStatement = $this->databaseDriver->prepare($sqlStatement);

            // bind the insert values
            foreach($bindArray as $tableName => $columnsToBind) {
                $fieldMetaArray = Model::getModelMeta($tableName, 'columns');
                foreach($columnsToBind as $columnName => $bindPropertyArray) {
                    $pdoType = $fieldMetaArray[$columnName];
                    foreach($bindPropertyArray as $bindProperty) {
                        $pdoStatement->bindValue($bindProperty['key'], $bindProperty['value'], $pdoType);
                    }
                }
            }
            
            // run the statement
            $pdoStatement->execute();

            // return the row count
            return $pdoStatement->rowCount();
        }
        catch(Exception $e) {
            throw new ModelException('An error occurred executing the SQL statement, Exception: '.$e->__toString(), $e);
        }
    }

    public function modelSelect(Array &$sqlAndBindArray) {
        try {
            $selectPdoStatement = null;
            if($sqlAndBindArray['isSelect']) {
                // create the pdo statement for the actual sql statement
                $selectPdoStatement = $this->databaseDriver->prepare($sqlAndBindArray['selectSql']);
            }

            $countPdoStatement = null;
            if($sqlAndBindArray['isCount']) {
                $countPdoStatement = $this->databaseDriver->prepare($sqlAndBindArray['countSql']);
            }

            // bind all the values
            foreach($sqlAndBindArray['bindArray'] as $tableName => $columnsToBind) {
                //TODO fix this kludge, we are getting an alias to a table here that is breaking the meta call...
                if(String::isNumeric(String::lastCharacter($tableName))) {
                    $tableName = String::subString($tableName, 0, String::lastIndexOf('_', $tableName));
                }

                $fieldMetaArray = Model::getModelMeta($tableName, 'columns');
                foreach($columnsToBind as $columnName => $bindPropertyArray) {
                    $pdoType = $fieldMetaArray[$columnName];
                    foreach($bindPropertyArray as $bindProperty) {
                        if($selectPdoStatement != null) {
                            $selectPdoStatement->bindValue($bindProperty['key'], $bindProperty['value'], $pdoType);
                        }

                        if($countPdoStatement != null) {
                            $countPdoStatement->bindValue($bindProperty['key'], $bindProperty['value'], $pdoType);
                        }
                    }
                }
            }

            $count = null;
            if($countPdoStatement != null) {
                $countPdoStatement->execute();
                $results = $countPdoStatement->fetchAll(PDO::FETCH_NUM);
                $count = $results[0][0];
            }

            $results = null;
            if($selectPdoStatement != null) {
                // execute the statement
                $selectPdoStatement->execute();

                // get the results as an integer indexed array
                $resultArray = $selectPdoStatement->fetchAll(PDO::FETCH_NUM);
                if(count($resultArray) > 0) {
                    // build an array of fully qualified column names
                    $qualifiedColumnNames = array();
                    for($i = 0; $i < $selectPdoStatement->columnCount(); $i++) {
                        $columnMeta = $selectPdoStatement->getColumnMeta($i);
                        $qualifiedColumnNames[$i] = $columnMeta['table'].'.'.$columnMeta['name'];
                    }

                    // merge the integer indexed array with the fully qualified column names
                    $fullyQualifiedResults = array();
                    foreach($resultArray as $resultRow) {
                        $fullyQualifiedRow = array();
                        for($i = 0; $i < count($resultRow); $i++) {
                            $fullyQualifiedRow[$qualifiedColumnNames[$i]] = $resultRow[$i];
                        }
                        $fullyQualifiedResults[] = $fullyQualifiedRow;
                    }

                    $results = $fullyQualifiedResults;
                }
            }

            return array(
                'data' => $results,
                'count' => $count
            );
        }
        catch(Exception $e) {
            throw new ModelException('An error occurred executing the SQL statement: '.$e->__toString(), $e);
        }
    }
}

?>
