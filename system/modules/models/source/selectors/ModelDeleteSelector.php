<?php

/**
 * Description of ModelDeleteSelector
 *
 * @author Kam Sheffield
 * @version 09/14/2011
 */
class ModelDeleteSelector extends Selector {

    /**
     *
     * @param string $tableName
     */
    public function __construct($tableName) {
        parent::__construct($tableName);
    }

    /**
     *
     * @param string $field
     * @param mixed $value
     * @param string $comparator
     * @param int $flags
     * @return ModelDeleteSelector
     */
    public function filterBy($field, $value, $comparator = '=', $flags = 0) {
        parent::filterBy($field, $value, $comparator, $flags);
        return $this;
    }

    /**
     *
     * @return ModelDeleteSelector
     */
    public function andWith() {
        parent::andWith();
        return $this;
    }

    /**
     *
     * @return ModelDeleteSelector
     */
    public function orWith() {
        parent::orWith();
        return $this;
    }

    /**
     *
     * @param string $sql
     * @return ModelDeleteSelector
     */
    public function usingSql($sql) {
        parent::usingSql($sql);
        return $this;
    }

    /**
     *
     * @return ModelSelectorResults
     */
    public function execute() {
        // get the model driver we need
        $modelDatabaseContext = ModelDatabaseContextManager::getInstance()->getModelDatabaseContextForTable($this->tableName);

        // run the query
        $rowCount = $modelDatabaseContext->deleteModelViaSelector($this->getSelectorProperties());

        // return results
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
        return $modelDatabaseContext->deleteModelViaSelector($this->getSelectorProperties(), true);
    }
}

?>
