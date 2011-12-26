<?php

/**
 * Description of Selector
 *
 * @author Kam Sheffield
 * @version 09/14/2011
 */
class ModelSelector extends Selector {

    /**
     * Maintains the order in which the tables will
     * be ordered in the SQL query.
     *
     * @var array
     */
    protected $tableQueue;

    /**
     * Maintains which table is currently having
     * filterBy, withRelation, and other edit
     * operations applied to it.
     *
     * @var array
     */
    protected $tableStack;

    /**
     *
     * @var array
     */
    protected $aliasTable;

    /**
     *
     * @var boolean
     */
    protected $isDistinct;

    /**
     *
     * @var boolean
     */
    protected $isCount;

    /**
     *
     * @var boolean
     */
    protected $isSelect;

    /**
     *
     * @var array
     */
    protected $limit;

    /**
     *
     * @var array
     */
    protected $orderByAscending;

    /**
     *
     * @var array
     */
    protected $orderByDescending;

    /**
     * Instantiates a new instance of a Selector.
     *
     * @param string $tableName The name of the base table to SELECT from.
     */
    public function __construct($tableName) {
        parent::__construct($tableName);
        
        $this->tableQueue = array();
        $this->tableStack = array();        
        $this->aliasTable = array();

        $table = array(
            'tableName' => $this->getAlias($tableName),
            'modelName' => String::underscoresToCamelCase($tableName, true),
            'parent' => null,
            'withColumns' => null,
            'withRelations' => array(),
            'relationKey' => null,
            'statements' => array()
        );

        $this->tableQueue[] = &$table;
        $this->tableStack[] = &$table;

        $this->isDistinct = false;
        $this->isCount = false;
        $this->isSelect = false;
        $this->limit = null;
        $this->orderByAscending = array();
        $this->orderByDescending = array();
    }
    
    /**
     *
     * @param string $column
     * @param mixed $value
     * @param string $comparator
     * @param int $flags
     * @return ModelSelector
     */
    public function filterBy($column, $value, $comparator = '=', $flags = 0) {        
        // get the current table we are manipulating
        $currentTable = &$this->tableStack[count($this->tableStack) - 1];

        // string off the table if a fully qualified name was given
        $column = $this->stripTableFromColumnName($column);

        $currentTable['statements'][] = array(
            'type' => 'filterBy',
            'column' => $column,
            'value' => $value,
            'comparator' => $comparator,
            'flags' => $flags,
            'concatenator' => $this->nextConcatenator,
        );

        if($this->nextConcatenator != null) {
            $this->nextConcatenator = null;
        }

        return $this;
    }

    /**
     *
     * @param string $sql
     * @return ModelSelector
     */
    public function where($sql) {
        // get the current table we are manipulating
        $currentTable = &$this->tableStack[count($this->tableStack) - 1];

        $currentTable['statements'][] = array(
            'type' => 'where',
            'sql' => $sql,
            'concatenator' => $this->nextConcatenator,
        );

        if($this->nextConcatenator != null) {
            $this->nextConcatenator = null;
        }

        return $this;
    }

    /**
     *
     * @return ModelSelector
     */
    public function andWith() {
        parent::andWith();
        return $this;
    }

    /**
     *
     * @return ModelSelector
     */
    public function orWith() {
        parent::orWith();
        return $this;
    }

    /**
     *
     * @param string $tableName
     * @param string $columnName
     * @return ModelSelector
     */
    public function withRelation($tableName, $columnName) {
        // if the input happens to be a model name, change it to a table name instead
        $modelName = '';
        if(String::isCamelCase($tableName)) {
            $modelName = $tableName;
            $tableName = String::camelCaseToUnderscores($tableName);
        }
        else {
            $modelName = String::underscoresToCamelCase($tableName, true);
        }

        // get the meta data for the table
        $foriegnKeyMeta = Model::getModelMeta($tableName, 'foreignKeys');

        // the meta key is a fully qualified table.column, so we need to figure out what we need
        $metaKey = $columnName;
        if(!String::contains('.', $columnName)) {
            // create a meta key
            $metaKey = $tableName.'.'.$columnName;
        }
        else {
            if($foriegnKeyMeta[$metaKey]['type'] == 'inbound') {
                $tableName = String::subString($columnName, 0, String::lastIndexOf('.', $columnName));
            }
            // strip off the table if a fully qualified name was given for the column
            $columnName = $this->stripTableFromColumnName($columnName);
        }

        // get the current table we are manipulating
        $currentTable = &$this->tableStack[count($this->tableStack) - 1];
        
        // depending on the type of relation we are working with, depends on how we organize the meta for the join expression
        if($foriegnKeyMeta[$metaKey]['type'] == 'inbound') {
            //if(String::con)

            // get the alias we need
            $alias = $this->getAlias($foriegnKeyMeta[$metaKey]['table'], true);

            // add the join info
            $currentTable['withRelations'][] = array(
                'table' => $tableName,     // this is the base table
                'column' => $columnName,
                'relatedTable' => $foriegnKeyMeta[$metaKey]['table'],
                'relatedTableAlias' => $alias,
                'relatedColumn' => $foriegnKeyMeta[$metaKey]['column']
            );

            // add the table to the stack
            $table = array(
                'tableName' => $alias,
                'modelName' => $modelName,
                'parent' => $this->getAlias($tableName),
                'withColumns' => null,
                'withRelations' => array(),
                'relationKey' => $metaKey,
                'statements' => array()
            );

            // fix the parent if we are doing a self referential join
            if($table['parent'] == $table['tableName']) {
                $table['parent'] = $this->getPreviousAlias($table['parent']);
            }

            $this->tableStack[] = &$table;
            $this->tableQueue[] = &$table;
        }
        else {
            // get the alias we need
            $alias = $this->getAlias($tableName, true);

            // add the join info
            $currentTable['withRelations'][] = array(
                'table' => $foriegnKeyMeta[$metaKey]['table'],      // this is the base table
                'column' => $foriegnKeyMeta[$metaKey]['column'],    // this is the base table column
                'relatedTable' => $tableName,                       // this is the table we are joining
                'relatedTableAlias' => $alias,                      // this creates an AS clause
                'relatedColumn' => $columnName                      // this is the column we are joining
            );

            // add the table to the stack
            $table = array(
                'tableName' => $alias,
                'modelName' => $modelName,
                'parent' => $this->getAlias($foriegnKeyMeta[$metaKey]['table']),
                'withColumns' => null,
                'withRelations' => array(),
                'relationKey' => $metaKey,
                'statements' => array()
            );

            // fix the parent if we are doing a self referential join
            if($table['parent'] == $table['tableName']) {
                $table['parent'] = $this->getPreviousAlias($table['parent']);
            }

            $this->tableStack[] = &$table;
            $this->tableQueue[] = &$table;
        }

        return $this;
    }

    /**
     *
     * @return ModelSelector
     */
    public function close() {
        // don't close if there is only one table on the stack
        if(count($this->tableStack) > 1) {
            // remove the last element from the open table stack
            $tableData = array_pop($this->tableStack);
        }
        return $this;
    }

    /**
     *
     * @param string $columns
     * @return ModelSelector
     */
    public function withColumns() {
        // get the current table
        $currentTable = &$this->tableStack[count($this->tableStack) - 1];
        $currentTable['withColumns'] = array();

        // append each column
        $arguments = func_get_args();
        foreach($arguments as $columnName) {
            // string off the table if a fully qualified name was given
            $columnName = $this->stripTableFromColumnName($columnName);
            $currentTable['withColumns'][] = $columnName;
        }

        return $this;
    }

    /**
     *
     * @param string $columnName Any number of comma delimited strings representing columns of a Model.
     * @return ModelSelector
     */
    public function orderByAscending($columnName) {
        $arguments = func_get_args();
        foreach($arguments as $argument) {
            $argument = $this->stripTableFromColumnName($argument);
            $this->orderByAscending[] = $argument;
        }
        return $this;
    }

    /**
     *
     * @param string $columnName Any number of comma delimited strings representing columns of a Model.
     * @return ModelSelector
     */
    public function orderByDescending($columnName) {
        $arguments = func_get_args();
        foreach($arguments as $argument) {
            $argument = $this->stripTableFromColumnName($argument);
            $this->orderByDescending[] = $argument;
        }
        return $this;
    }

    /**
     *
     * @param int $offset
     * @param int $rowCount
     * @return ModelSelector
     */
    public function limit($offset, $rowCount) {
        $this->limit = array('offset' => $offset, 'rowCount' => $rowCount);
        return $this;
    }

    /**
     *
     * @return ModelSelector
     */
    public function asDistinct() {
        $this->isDistinct = true;
        return $this;
    }

    /**
     * Supply raw SQL to the ModelSelector.
     * This function overrides any previous function
     * calls on the ModelSelector.  Only the SQL
     * specified here will be executed.
     *
     * @param string $sql
     * @return ModelSelector
     */
    public function usingSql($sql) {
        parent::usingSql($sql);
        return $this;
    }

    /**
     * Causes the ModelSelector to
     * return the count of the items
     * returned by your query.
     *
     * @return ModelSelector
     */
    public function count() {
        $this->isCount = true;
        return $this;
    }

    /**
     * Causes the ModelSelector to
     * retrieve a list of Models matching
     * your query.
     *
     * @return ModelSelector
     */
    public function select() {
        $this->isSelect = true;
        return $this;
    }

    /**
     * Causes the ModelSelector to fetch
     * the desired data from the database,
     * returning the result as a ModelList
     * of the Model type specified previously.
     *
     * @return ModelSelectorResults
     */
    public function execute() {
        // make sure we have something to do
        if(!$this->isSelect && !$this->isCount) {
            throw new ModelException('A call to count() or select() is required before a call to execute()');
        }

        // get the model driver we need
        $modelDatabaseContext = ModelDatabaseContextManager::getInstance()->getModelDatabaseContextForTable($this->tableName);

        // run the query
        $databaseResults = $modelDatabaseContext->selectModels($this->getSelectorProperties());

        // make sure there is something to instance
        if($databaseResults['data'] == null) {
            return new ModelSelectorResults($this->tableName, array(), $databaseResults['count']);
        }
        else {
            $modelBuilder = new ModelBuilder(String::underscoresToCamelCase($this->tableName, true), $databaseResults, $this->tableQueue);
            return new ModelSelectorResults($this->tableName, $modelBuilder->getModels(), $databaseResults['count']);
        }
    }

    /**
     * Get the sql generated by the current
     * state of the ModelSelector.
     *
     * @return string
     */
    public function toSql() {
        $modelDatabaseContext = ModelDatabaseContextManager::getInstance()->getModelDatabaseContextForTable($this->tableName);
        return $modelDatabaseContext->selectModels($this->getSelectorProperties(), true);
    }
    
    public function selectFirstModel() {
        return $this->select()->execute()->asModelList()->getFirst();
    }

    /**
     * Perpares the fields of the ModelSelector for a ModelDriver.
     *
     * This funciton is used instead of passing the object itself
     * because I did not want a bunch of stupid getter/setter methods
     * cluttering the api.
     *
     * @return array
     */
    protected function getSelectorProperties() {
        $array = parent::getSelectorProperties();
        unset($array['filterByArray']);
        $array['tableArray'] = &$this->tableQueue;
        $array['orderByAscending'] = &$this->orderByAscending;
        $array['orderByDescending'] = &$this->orderByDescending;
        $array['limit'] = &$this->limit;
        $array['isDistinct'] = &$this->isDistinct;
        $array['isCount'] = &$this->isCount;
        $array['isSelect'] = &$this->isSelect;        
        return $array;        
    }

    /**
     *
     * @param string $tableName
     * @param boolean $increment
     * @return string
     */
    private function getAlias($tableName, $increment = false) {
        if(!array_key_exists($tableName, $this->aliasTable)) {
            $this->aliasTable[$tableName] = 0;
            return $tableName;
        }
        else {
            if($increment) {
                return $tableName.'_'.++$this->aliasTable[$tableName];
            }
            else {
                if($this->aliasTable[$tableName] != 0) {
                    return $tableName.'_'.$this->aliasTable[$tableName];
                }
                else {
                    return $tableName;
                }
            }
        }
    }

    /**
     *
     * @param string $tableName
     * @return string
     */
    private function getPreviousAlias($tableName) {
        $suffix = String::subString($tableName, String::lastIndexOf('_', $tableName) + 1);
        $suffix = intval($suffix);
        $suffix--;
        if($suffix < 1) {
            return String::subString($tableName, 0, String::lastIndexOf('_', $tableName));
        }
        else {
            return $tableName.'_'.$suffix;
        }
    }
}

?>
