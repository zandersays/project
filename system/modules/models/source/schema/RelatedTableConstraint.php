<?php

/**
 * Description of TableRelatedTable
 *
 * @author Kam Sheffield
 * @version 08/11/2011
 */
class RelatedTableConstraint {

    /**
     *
     * @var string
     */
    protected $name;

    /**
     *
     * @var string
     */
    protected $tableName;

    /**
     *
     * @var string
     */
    protected $columnName;

    /**
     *
     * @var string
     */
    protected $referencedColumnName;

    /**
     *
     * @param string $name
     * @param string $tableName
     * @param string $columName
     * @param string $referencedColumnName
     */
    public function __construct($name = '', $tableName = '', $columName = '', $referencedColumnName = '') {
        $this->name = $name;
        $this->tableName = $tableName;
        $this->columnName = $columName;
        $this->referencedColumnName = $referencedColumnName;
    }

    /**
     *
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     *
     * @param string $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     *
     * @return string
     */
    public function getTableName() {
        return $this->tableName;
    }

    /**
     *
     * @param string $tableName
     */
    public function setTableName($tableName) {
        $this->tableName = $tableName;
    }

    /**
     *
     * @return string
     */
    public function getColumnName() {
        return $this->columnName;
    }

    /**
     *
     * @param string $columnName
     */
    public function setColumnName($columnName) {
        $this->columnName = $columnName;
    }

    /**
     *
     * @return string
     */
    public function getReferencedColumnName() {
        return $this->referencedColumnName;
    }

    /**
     *
     * @param string $referencedColumnName
     */
    public function setReferencedColumnName($referencedColumnName) {
        $this->referencedColumnName = $referencedColumnName;
    }
}

?>
