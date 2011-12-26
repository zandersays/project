<?php

/**
 * Description of Table
 *
 * @author Kam Sheffield
 * @version 08/23/2011
 */
class Table {

    /**
     *
     * @param string $modelName
     * @param array $modelRequirements
     * @return Table
     */
    public static function createFromModelRequirements($modelName, $modelRequirements) {
        $table = new Table(String::camelCaseToUnderscores($modelName));

        foreach($modelRequirements as $modelRequirement) {
            $tableColumn = new TableColumn();
            $tableColumn->setName($modelRequirement['name']);

            if(String::contains('(', $modelRequirement['type'])) {
                $firstParen = String::indexOf('(', $modelRequirement['type']);
                $type = String::subString($modelRequirement['type'], 0, $firstParen);
                $tableColumn->setDataType($type);

                if(String::lower($type) != 'enum') {    // TODO this probably needs to be more robust
                    $secondParen = String::indexOf(')', $modelRequirement['type']);
                    $length = String::subString($modelRequirement['type'], $firstParen + 1, $secondParen - $firstParen - 1);
                    $tableColumn->setLength($length);
                }
            }
            else if(String::contains(' ', $modelRequirement['type'])) {
                $dataType = String::subString($modelRequirement['type'], 0, String::indexOf(' ', $modelRequirement['type']));
                $tableColumn->setDataType($dataType);
            }
            else {
                $tableColumn->setDataType($modelRequirement['type']);
            }

            if($modelRequirement['null'] == 'NO') {
                $tableColumn->setIsNonNull(true);
            }

            // different from schema gen
            if($modelRequirement['key'] != null) {
                if($modelRequirement['key']['type'] == 'primary') {
                    $tableColumn->setIsPrimaryKey(true);
                }
                else if($modelRequirement['key']['type'] == 'foreign') {
                    $tableColumn->setIsForeignKey(true);
                    $foreignKey = new ForeignKeyConstraint('fk_'.$tableColumn->getName());
                    $foreignKey->setColumnName($tableColumn->getName());
                    $foreignKey->setReferencedTableName($modelRequirement['key']['referenced_table']);
                    $foreignKey->setReferencedColumnName($modelRequirement['key']['referenced_column']);
                    $table->addForgeignKeyConstraint($foreignKey);
                }
            }

            if($modelRequirement['default'] != null) {
                $tableColumn->setDefaultValue($modelRequirement['default']);
            }

            if(String::contains('auto_increment', $modelRequirement['extra'])) {
                $tableColumn->setIsAutoIncrementing(true);
            }

            if(String::contains('unsigned', $modelRequirement['type'])) {
                $tableColumn->setIsUnsigned(true);
            }

            // TODO make this parse binary and zerofill
            $table->addColumn($tableColumn);
        }

        return $table;
    }

    /**
     *
     * @var string
     */
    private $name;

    /**
     *
     * @var string
     */
    private $characterSet;

    /**
     *
     * @var string
     */
    private $engine;

    /**
     *
     * @var array
     */
    private $columnArray;

    /**
     *
     * @var array
     */
    private $indexArray;

    /**
     *
     * @var array
     */
    private $foreignKeyConstraintArray;

    /**
     *
     * @var array
     */
    private $relatedTableConstraintArray;

    /**
     *
     * @param string $name
     * @param string $databaseName
     * @param string $characterSet
     * @param string $engine
     */
    public function __construct($name = '', $characterSet = 'utf8', $engine = 'InnoDB') {
        $this->name = $name;
        $this->characterSet = $characterSet;
        $this->engine = $engine;
        $this->columnArray = array();
        $this->indexArray = array();
        $this->foreignKeyConstraintArray = array();
        $this->relatedTableConstraintArray = array();
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
    public function getCharacterSet() {
        return $this->characterSet;
    }

    /**
     *
     * @param string $characterSet
     */
    public function setCharacterSet($characterSet) {
        $this->characterSet = $characterSet;
    }

    /**
     *
     * @return string
     */
    public function getEngine() {
        return $this->engine;
    }

    /**
     *
     * @param string $engine
     */
    public function setEngine($engine) {
        $this->engine = $engine;
    }

    /**
     *
     * @param TableColumn $tableColumn
     * @return boolean
     */
    public function addColumn(TableColumn $tableColumn) {
        if(!$this->containsColumn($tableColumn->getName())) {
            $this->columnArray[] = $tableColumn;

            if($tableColumn->getIsPrimaryKey()) {
                $tableIndex = new TableIndex('pk_'.$tableColumn->getName(), TableIndexType::Primary);
                $tableIndex->addIndexedColumn($tableColumn->getName(), 1);
                if(!$this->addIndex($tableIndex)) {
                    //TODO log me
                }
            }

            return true;
        }
        return false;
    }

    /**
     *
     * @param string $tableColumnName
     * @return boolean
     */
    public function removeColumn($tableColumnName) {
        foreach($this->columnArray as $index => $tableColumn) {
            if($tableColumn->getName() == $tableColumnName) {
                if($tableColumn->getIsPrimaryKey()) {
                    if(!$this->removeIndex('pk_'.$tableColumn->getName())){
                        //TODO log me
                    }
                }
                unset($this->columnArray[$index]);
                return true;
            }
        }
        return false;
    }

    /**
     *
     * @param string $tableColumnName
     * @return boolean
     */
    public function containsColumn($tableColumnName) {
        foreach($this->columnArray as $index => $tableColumn) {
            if($tableColumn->getName() == $tableColumnName) {
                return true;
            }
        }
        return false;
    }

    /**
     *
     * @param string $tableColumnName
     * @return TableColumn
     */
    public function getColumn($tableColumnName) {
        foreach($this->columnArray as $index => $tableColumn) {
            if($tableColumn->getName() == $tableColumnName) {
                return $tableColumn;
            }
        }
        return null;
    }

    /**
     *
     * @param TableIndex $tableIndex
     * @return boolean
     */
    public function addIndex(TableIndex $tableIndex) {
        if(!$this->containsIndex($tableIndex->getName())) {
            $this->indexArray[] = $tableIndex;
            return true;
        }
        return false;
    }

    /**
     *
     * @param string $tableIndexName
     * @return boolean
     */
    public function removeIndex($tableIndexName) {
        foreach($this->indexArray as $index => $tableIndex) {
            if($tableIndex->getName() == $tableIndexName) {
                unset($this->indexArray[$index]);
                return true;
            }
        }
        return false;
    }

    /**
     *
     * @param string $tableIndexName
     * @return boolean
     */
    public function containsIndex($tableIndexName) {
        foreach($this->indexArray as $index => $tableIndex) {
            if($tableIndex->getName() == $tableIndexName) {
                return true;
            }
        }
        return false;
    }

    /**
     *
     * @param string $tableIndexName
     * @return TableIndex
     */
    public function getIndex($tableIndexName) {
        foreach($this->indexArray as $index => $tableIndex) {
            if($tableIndex->getName() == $tableIndexName) {
                return $tableIndex;
            }
        }
        return null;
    }

    /**
     *
     * @param ForeignKeyConstraint $foreignKeyConstraint
     * @return boolean
     */
    public function addForgeignKeyConstraint(ForeignKeyConstraint $foreignKeyConstraint) {
        if(!$this->containsForeignKeyConstraint($foreignKeyConstraint->getName())) {
            $this->foreignKeyConstraintArray[$foreignKeyConstraint->getName()] = $foreignKeyConstraint;

            $tableIndex = new TableIndex('fk_'.$foreignKeyConstraint->getColumnName(), TableIndexType::Index);
            $tableIndex->addIndexedColumn($foreignKeyConstraint->getColumnName(), 1);
            if(!$this->addIndex($tableIndex)) {
                //TODO log me!!
            }

            return true;
        }
        return false;
    }

    /**
     *
     * @param string $foreignKeyConstraintName
     * @return boolean
     */
    public function removeForeignKeyConstraint($foreignKeyConstraintName) {
        if($this->containsForeignKeyConstraint($foreignKeyConstraintName)) {
            if(!$this->removeIndex('fk_'.$this->foreignKeyConstraintArray[$foreignKeyConstraintName]->getColumnName())) {
                // TODO log me!!
            }

            unset($this->foreignKeyConstraintArray[$foreignKeyConstraintName]);
            return true;
        }
        return false;
    }

    /**
     *
     * @param string $foreignKeyConstraintName
     * @return boolean
     */
    public function containsForeignKeyConstraint($foreignKeyConstraintName) {
        return array_key_exists($foreignKeyConstraintName, $this->foreignKeyConstraintArray);
    }

    /**
     *
     * @param RelatedTableConstraint $relatedTableConstraint
     * @return boolean
     */
    public function addRelatedTableConstraint(RelatedTableConstraint $relatedTableConstraint) {
        if(!$this->containsRelatedTableConstraint($relatedTableConstraint->getName())) {
            $this->relatedTableConstraintArray[$relatedTableConstraint->getName()] = $relatedTableConstraint;
            return true;
        }
        return false;
    }

    /**
     *
     * @param string $relatedTableConstraintName
     * @return boolean
     */
    public function removeRelatedTableConstraint($relatedTableConstraintName) {
        if($this->containsRelatedTableConstraint($relatedTableConstraintName)) {
            unset($this->relatedTableConstraintArray[$relatedTableConstraintName]);
            return true;
        }
        return false;
    }

    /**
     *
     * @param string $relatedTableConstraintName
     * @return boolean
     */
    public function containsRelatedTableConstraint($relatedTableConstraintName) {
        return array_key_exists($relatedTableConstraintName, $this->relatedTableConstraintArray);
    }

    /**
     *
     * @param string $tableForeignKeyName
     * @return ForeignKeyConstraint
     */
    public function getForeignKeyConstraint($tableForeignKeyName) {
        foreach($this->foreignKeyConstraintArray as $index => $tableForeignKey) {
            if($tableForeignKey->getName() == $tableForeignKeyName) {
                return $tableForeignKey;
            }
        }
        return null;
    }

    /**
     *
     * @return array
     */
    public function getColumnArray() {
        return $this->columnArray;
    }

    /**
     *
     * @return array
     */
    public function getIndexArray() {
        return $this->indexArray;
    }

    /**
     *
     * @return array
     */
    public function getForeignKeyConstraintArray() {
        return $this->foreignKeyConstraintArray;
    }

    /**
     *
     * @param array $columnArray
     */
    public function setColumnArray(Array $columnArray) {
        $this->columnArray = $columnArray;
    }

    /**
     *
     * @param array $indexArray
     */
    public function setIndexArray(Array $indexArray) {
        $this->indexArray = $indexArray;
    }

    /**
     *
     * @param array $foreignKeyArray
     */
    public function setForeignKeyConstraintArray(Array $foreignKeyArray) {
        $this->foreignKeyConstraintArray = $foreignKeyArray;
    }

    /**
     *
     * @return array
     */
    public function getRelatedTableConstraintArray() {
        return $this->relatedTableConstraintArray;
    }

    /**
     *
     * @param array $relatedTableConstraintArray
     */
    public function setRelatedTableConstraintArray(Array $relatedTableConstraintArray) {
        $this->relatedTableConstraintArray = $relatedTableConstraintArray;
    }

    /**
     *
     * @return string
     */
    public function pickPrimaryKey() {
        $primaryKeys = array();
        foreach($this->columnArray as $column) {
            if($column->getIsPrimaryKey()) {
                $primaryKeys[] = $column->getName();
            }
        }

        $size = Arr::size($primaryKeys);
        if($size > 0) {
            if($size > 1) {
                if(Arr::contains('id', $primaryKeys)) {
                    return 'id';
                }
                else {
                    return $primaryKeys[0];
                }
            }
            else {
                return $primaryKeys[0];
            }
        }
        else {
            return null;
        }
    }
}

?>
