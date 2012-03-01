<?php

/**
 * Description of AlterTable
 *
 * @author Kam Sheffield
 * @version 08/15/2011
 */
class TableAlterer {

    /**
     *
     * @var string
     */
    protected $table;

    /**
     *
     * @var string
     */
    protected $newTableName;

    /**
     *
     * @var string
     */
    protected $newCharacterSet;

    /**
     *
     * @var string
     */
    protected $newEngine;

    /**
     *
     * @var array
     */
    protected $columnsToAdd;

    /**
     *
     * @var array
     */
    protected $columnsToRemove;

    /**
     *
     * @var array
     */
    protected $columnsToAlter;

    /**
     *
     * @var array
     */
    protected $indexesToAdd;

    /**
     *
     * @var array
     */
    protected $indexesToRemove;

    /**
     *
     * @var array
     */
    protected $foreignKeysToAdd;

    /**
     *
     * @var array
     */
    protected $foreignKeysToRemove;

    /**
     *
     * @param Table $table
     */
    public function __construct(Table $table) {
        $this->table = $table;
        $this->newTableName = '';
        $this->newCharacterSet = '';
        $this->newEngine = '';
        $this->columnsToAdd = array();
        $this->columnsToRemove = array();
        $this->columnsToAlter = array();
        $this->indexesToAdd = array();
        $this->indexesToRemove = array();
        $this->foreignKeysToAdd = array();
        $this->foreignKeysToRemove = array();
    }

    public function rename($newTableName) {
        $this->newTableName = $newTableName;
    }

    public function changeCharacterSet($characterSet) {
        $this->newCharacterSet = $characterSet;
    }

    public function changeEngine($engine) {
        $this->newEngine = $engine;
    }

    public function addColumn(TableColumn $tableColumn) {
        if(!$this->table->containsColumn($tableColumn->getName()) && !Arr::contains($tableColumn->getName(), $this->columnsToAdd)) {
            $this->columnsToAdd[] = $tableColumn;

            if($tableColumn->getIsPrimaryKey()) {
                $tableIndex = new TableIndex('pk_'.$tableColumn->getName(), TableIndexType::Primary);
                if(!$this->addIndex($tableIndex)) {
                    // TODO log me
                }
            }

            return true;
        }
        else {
            return false;
        }
    }

    public function removeColumn($columnName) {
        if($this->table->containsColumn($columnName) && !Arr::contains($columnName, $this->columnsToRemove)) {
            $this->columnsToRemove[] = $columnName;

            if($this->table->getColumn($columnName)->getIsPrimaryKey()) {
                if(!$this->removeIndex('pk_'.$columnName)){
                    // TODO log a warning
                }
            }

            if($this->table->getColumn($columnName)->getIsForeignKey()) {
                if(!$this->removeForeignKey('fk_'.$columnName)) {
                    // TODO log a warning
                }
            }

            return true;
        }
        else {
            return false;
        }
    }

    public function alterColumn($columnName, TableColumn $alteredColumn) {
        if($this->table->containsColumn($columnName) && !array_key_exists($columnName, $this->columnsToAlter)) {
            $this->columnsToAlter[$columnName] = $alteredColumn;

            if($alteredColumn->getIsPrimaryKey()) {
                $tableIndex = new TableIndex('pk_'.$alteredColumn->getName(), TableIndexType::Primary);
                if(!$this->addIndex($tableIndex)) {
                    // TODO log me
                }
            }

            return true;
        }
        else {
            return false;
        }
    }

    public function addIndex(TableIndex $tableIndex) {
        if(!$this->table->containsIndex($tableIndex->getName()) && !Arr::contains($tableIndex->getName(), $this->indexesToAdd)) {
            $this->indexesToAdd[] = $tableIndex;
            return true;
        }
        else {
            return false;
        }
    }

    public function removeIndex($indexName) {
        if($this->table->containsIndex($indexName) && !Arr::contains($indexName, $this->indexesToRemove)) {
            $this->indexesToRemove[] = $indexName;
            return true;
        }
        else {
            return false;
        }
    }

    public function addForeignKey(ForeignKeyConstraint $foreignKeyConstraint) {
        if(!$this->table->containsForeignKeyConstraint($foreignKeyConstraint->getName()) && !Arr::contains($foreignKeyConstraint->getName(), $this->foreignKeysToAdd)) {
            $this->foreignKeysToAdd[] = $foreignKeyConstraint;
            return true;
        }
        else {
            return false;
        }
    }

    public function removeForeignKey($foreignKeyName) {
        if($this->table->containsForeignKeyConstraint($foreignKeyName) && !Arr::contains($foreignKeyName, $this->foreignKeysToRemove)) {
            $this->foreignKeysToRemove[] = $foreignKeyName;
            return true;
        }
        else {
            return false;
        }
    }

    /**
     *
     * @return Table
     */
    public function getTable() {
        return $this->table;
    }

    public function getNewCharacterSet() {
        return $this->newCharacterSet;
    }

    public function getNewEngine() {
        return $this->newEngine;
    }

    public function getNewTableName() {
        return $this->newTableName;
    }

    public function getColumnsToAdd() {
        return $this->columnsToAdd;
    }

    public function getColumnsToRemove() {
        return $this->columnsToRemove;
    }

    public function getColumnsToAlter() {
        return $this->columnsToAlter;
    }

    public function getIndexesToAdd() {
        return $this->indexesToAdd;
    }

    public function getIndexesToRemove() {
        return $this->indexesToRemove;
    }

    public function getForeignKeysToAdd() {
        return $this->foreignKeysToAdd;
    }

    public function getForeignKeysToRemove() {
        return $this->foreignKeysToRemove;
    }
}

?>
