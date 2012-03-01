<?php

/**
 * Description of ModelUpdateSelector
 *
 * @author Kam Sheffield
 * @version 09/14/2011
 */
class ModelUpdateSelector extends Selector {   

    /**
     *
     * @var array
     */
    protected $fieldArray;

    /**
     *
     * @param string $tableName
     */
    public function __construct($tableName) {
        parent::__construct($tableName);           
        $this->fieldArray = array();
    }

    /**
     *
     * @param string $field
     * @param mixed $value
     * @param string $comparator
     * @param int $flags
     * @return ModelUpdateSelector
     */
    public function filterBy($field, $value, $comparator = '=', $flags = 0) {
        parent::filterBy($field, $value, $comparator, $flags);
        return $this;
    }

    /**
     *
     * @return ModelUpdateSelector
     */
    public function andWith() {
        parent::andWith();
        return $this;
    }

    /**
     *
     * @return ModelUpdateSelector
     */
    public function orWith() {
        parent::orWith();
        return $this;
    }

    /**
     *
     * @param string $sql
     * @return ModelUpdateSelector
     */
    public function usingSql($sql) {
        parent::usingSql($sql);
        return $this;
    }

    /**
     *
     * @param array $values
     * @return ModelUpdateSelector
     */
    public function withValues(Array $values) {
        $array = array();
        foreach($values as $columnName => $columnValue) {
            $columnName = $this->stripTableFromColumnName($columnName);
            $array[$columnName] = $columnValue;
        }
        $this->fieldArray = $array;
        return $this;
    }

    /**
     *
     * @return ModelSelectorResults
     */
    public function execute() {
        if(count($this->fieldArray) == 0) {
            throw new ModelException('A call to withValues() is required before a call to execute().');
        }

        // get the model driver we need
        $modelDatabaseContext = ModelDatabaseContextManager::getInstance()->getModelDatabaseContextForTable($this->tableName);

        // run the query
        $rowCount = $modelDatabaseContext->updateModelViaSelector($this->getSelectorProperties());

        // return something cool
        return new ModelSelectorResults($this->tableName, array(), $rowCount);
    }

    /**
     *
     * @return string
     */
    public function toSql() {
        // get the model driver we need
        $modelDatabaseContext = ModelDatabaseContextManager::getInstance()->getModelDatabaseContextForTable($this->tableName);
        
        // run the query
        return $modelDatabaseContext->updateModelViaSelector($this->getSelectorProperties(), true);
    }

    protected function getSelectorProperties() {
        $array = parent::getSelectorProperties();
        $array['fieldArray'] = $this->fieldArray;
        return $array;        
    }
}

?>
