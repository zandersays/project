<?php

/**
 * Description of Selector
 *
 * @author Kam Sheffield
 * @version 09/14/2011
 */
abstract class Selector {
    
    /**
     *
     * @var string
     */
    protected $tableName;   

    /**
     *
     * @var array
     */
    protected $filterByArray;

    /**
     *
     * @var string
     */
    protected $nextConcatenator;

    /**
     *
     * @var string
     */
    protected $sql;

    /**
     *
     * @param string $tableName
     */
    public function __construct($tableName) {
        // maybe a model name was passed in ?
        if(!String::hasUnderscores($tableName)) {
            $tableName = String::camelCaseToUnderscores($tableName);
        }
        
        $this->tableName = $tableName;
        $this->filterByArray = array();
        $this->nextConcatenator = null;
        $this->sql = null;        
    }
    
    /**
     *
     * @return ModelSelectorResults
     */
    public abstract function execute();

    /**
     *
     * @return string
     */
    public abstract function toSql();
    
    /**
     *
     * @param string $field
     * @param mixed $value
     * @param string $comparator
     * @param int $flags     
     */
    public function filterBy($field, $value, $comparator, $flags) {
        if(count($this->filterByArray) > 0 && $this->nextConcatenator == null) {
            throw new ModelException('You must call andWith or orWith() to join adjacent filterBy() calls');
        }

        $field = $this->stripTableFromColumnName($field);

        $this->filterByArray[] = array (
            'type' => 'filterBy',
            'column' => $field,
            'value' => $value,
            'comparator' => $comparator,
            'flags' => $flags,
            'concatenator' => $this->nextConcatenator
        );

        if($this->nextConcatenator != null) {
            $this->nextConcatenator = null;
        }        
    }

    /**
     *     
     */
    public function andWith() {
        $this->nextConcatenator = 'AND';        
    }

    /**
     *     
     */
    public function orWith() {
        $this->nextConcatenator = 'OR';        
    }

    /**
     *
     * @param string $sql     
     */
    public function usingSql($sql) {
        $this->sql = $sql;        
    }

    /**
     *
     * @return array
     */
    protected function getSelectorProperties() {
        return array(
            'tableName' => $this->tableName,            
            'filterByArray' => $this->filterByArray,
            'sql' => $this->sql,            
        );
    }

    /**
     *
     * @param string $columnName
     * @return string
     */
    protected function stripTableFromColumnName($columnName) {
        if(String::contains('.', $columnName)) {
            $columnName = String::subString($columnName, String::lastIndexOf('.', $columnName) + 1);
        }
        return $columnName;
    }
}

?>
