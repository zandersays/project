<?php

/**
 * Description of TableColumnForeignKey
 *
 * @author Kam Sheffield
 * @version 08/11/2011
 */
class ForeignKeyConstraint {

    /**
     *
     * @var string
     */
    protected $name;

    /**
     *
     * @var string
     */
    protected $columnName;

    /**
     *
     * @var string
     */
    protected $referencedTableName;

    /**
     *
     * @var string
     */
    protected $referencedColumnName;

    /**
     *
     * @var string
     */
    protected $updateAction;

    /**
     *
     * @var string
     */
    protected $deleteAction;

    /**
     *
     * @param string $name
     * @param string $columnName
     * @param string $referencedTableName
     * @param string $referencedColumnName
     */
    public function __construct($name = '', $columnName = '', $referencedTableName = '', $referencedColumnName = '') {
        $this->name = $name;
        $this->columnName = $columnName;
        $this->referencedTableName = $referencedTableName;
        $this->referencedColumnName = $referencedColumnName;
        $this->updateAction = ForeignKeyConstraintUpdateType::Cascade;
        $this->deleteAction = ForeignKeyConstraintUpdateType::Cascade;
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
    public function getReferencedTableName() {
        return $this->referencedTableName;
    }

    /**
     *
     * @param string $referencedTable
     */
    public function setReferencedTableName($referencedTableName) {
        $this->referencedTableName = $referencedTableName;
    }

    /**
     *
     * @return ForeignConstraintKeyUpdateType
     */
    public function getUpdateAction() {
        return $this->updateAction;
    }

    /**
     *
     * @param ForeignKeyConstraintUpdateType $updateAction
     */
    public function setUpdateAction($updateAction) {
        $this->updateAction = $updateAction;
    }

    /**
     *
     * @return ForeignKeyConstraintUpdateType
     */
    public function getDeleteAction() {
        return $this->deleteAction;
    }

    /**
     *
     * @param ForeignKeyConstraintUpdateType $deleteAction
     */
    public function setDeleteAction($deleteAction) {
        $this->deleteAction = $deleteAction;
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

    /**
     *
     * @param ForeignKeyConstraint $foreignKeyConstraint
     * @return array
     */
    public function compare(ForeignKeyConstraint $foreignKeyConstraint) {
        $comparisonArray = array();

        if($this->name != $foreignKeyConstraint->name) {
            $comparisonArray['name'] = $foreignKeyConstraint->name;
        }

        if($this->columnName != $foreignKeyConstraint->columnName) {
            $comparisonArray['columnName'] = $foreignKeyConstraint->columnName;
        }

        if($this->referencedTableName != $foreignKeyConstraint->referencedTableName) {
            $comparisonArray['referencedTableName'] = $foreignKeyConstraint->referencedTableName;
        }

        if($this->referencedColumnName != $foreignKeyConstraint->referencedColumnName) {
            $comparisonArray['referencedColumnName'] = $foreignKeyConstraint->referencedColumnName;
        }

        if($this->updateAction != $foreignKeyConstraint->updateAction) {
            $comparisonArray['updateAction'] = $foreignKeyConstraint->updateAction;
        }

        if($this->deleteAction != $foreignKeyConstraint->deleteAction) {
            $comparisonArray['deleteAction'] = $foreignKeyConstraint->deleteAction;
        }

        return $comparisonArray;
    }
}

?>
