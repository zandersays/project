<?php

/**
 * Description of SchemaGenerator
 *
 * @author Kam Sheffield
 * @version 08/11/2011
 */
class SchemaGenerator {

    /**
     *
     * @var string
     */
    private $modelName;

    /**
     *
     * @var string
     */
    private $tableName;

    /**
     *
     * @var ModelDriver
     */
    private $modelDriver;

    /**
     *
     * @param string $modelName
     * @param ModelDriver $modelDriver
     */
    public function __construct($modelName, ModelDriver $modelDriver) {
        $this->modelName = $modelName;
        $this->tableName = String::camelCaseToUnderscores($modelName);
        $this->modelDriver = $modelDriver;
    }

    /**
     *
     * @return Table
     */
    public function getSchema() {
        $table = new Table($this->tableName);

        // get the columns for this table
        $tableDescription = $this->modelDriver->getDescriptionForTable($this->tableName);
        $this->buildTableColumns($table, $tableDescription);

        // get the foreign keys for this table
        $tableForeignKeys = $this->modelDriver->getForeignKeysForTable($this->tableName);
        $this->buildForeignKeys($table, $tableForeignKeys);

        // get the indexes for this table
        $tableIndexes = $this->modelDriver->getIndexesForTable($this->tableName);
        $this->buildIndexes($table, $tableIndexes);

        // get all of the tables related to this table
        $relatedTables = $this->modelDriver->getTablesRelatedToTable($this->tableName);
        $this->buildRelatedTableConstraints($table, $relatedTables);

        return $table;
    }

    private function buildTableColumns(Table $table, $tableDescription) {
        foreach($tableDescription as $columnDescription) {
            $tableColumn = new TableColumn();
            $tableColumn->setName($columnDescription['Field']);

            if(String::contains('(', $columnDescription['Type'])) {
                $firstParen = String::indexOf('(', $columnDescription['Type']);
                $type = String::subString($columnDescription['Type'], 0, $firstParen);
                $tableColumn->setDataType($type);

                if(String::lower($type) != 'enum') {    // TODO this probably needs to be more robust
                    $secondParen = String::indexOf(')', $columnDescription['Type']);
                    $length = String::subString($columnDescription['Type'], $firstParen + 1, $secondParen - $firstParen - 1);
                    $tableColumn->setLength($length);
                }
            }
            else if(String::contains(' ', $columnDescription['Type'])) {
                $dataType = String::subString($columnDescription['Type'], 0, String::indexOf(' ', $columnDescription['Type']));
                $tableColumn->setDataType($dataType);
            }
            else {
                $tableColumn->setDataType($columnDescription['Type']);
            }

            if($columnDescription['Null'] == 'NO') {
                $tableColumn->setIsNonNull(true);
            }

            if($columnDescription['Key'] == 'PRI') {
                $tableColumn->setIsPrimaryKey(true);
            }
            else if($columnDescription['Key'] == 'UNI' || $columnDescription['Key'] == 'MUL') {
                $tableColumn->setIsForeignKey(true);
            }

            if($columnDescription['Default'] != null) {
                $tableColumn->setDefaultValue($columnDescription['Default']);
            }

            if(String::contains('auto_increment', $columnDescription['Extra'])) {
                $tableColumn->setIsAutoIncrementing(true);
            }

            if(String::contains('unsigned', $columnDescription['Type'])) {
                $tableColumn->setIsUnsigned(true);
            }

            // TODO make this parse binary and zerofill
            $table->addColumn($tableColumn);
        }
    }

    private function buildForeignKeys(Table $table, $tableForeignKeys) {
        // TODO we need to figure out if there is some way to get the update type from the database...
        foreach($tableForeignKeys as $foreignKey) {
            if($foreignKey['CONSTRAINT_NAME'] != 'PRIMARY') {
                $foreignKeyConstraint = new ForeignKeyConstraint();
                $foreignKeyConstraint->setName($foreignKey['CONSTRAINT_NAME']);
                $foreignKeyConstraint->setReferencedTableName($foreignKey['REFERENCED_TABLE_NAME']);
                $foreignKeyConstraint->setColumnName($foreignKey['COLUMN_NAME']);
                $foreignKeyConstraint->setReferencedColumnName($foreignKey['REFERENCED_COLUMN_NAME']);
                $table->addForgeignKeyConstraint($foreignKeyConstraint);
            }
        }
    }

    private function buildIndexes(Table $table, $tableIndexes) {
        foreach($tableIndexes as $index) {
            if(!$table->containsIndex($index['Key_name'])) {
                $tableIndex = new TableIndex();
                $tableIndex->setName($index['Key_name']);

                // TODO this is a guess, we have no way of getting this information from the database...
                if($index['Index_type'] == 'FULLTEXT') {
                    $tableIndex->setType(TableIndexType::FullText);
                }
                else if($index['Key_name'] == 'PRIMARY') {
                    $tableIndex->setType(TableIndexType::Primary);
                }
                else {
                    $tableIndex->setType(TableIndexType::Index);    // TODO should we do this?
                }

                $table->addIndex($tableIndex);
            }

            $tableIndex = $table->getIndex($index['Key_name']);

            $isOrderAscending = false;
            if($index['Collation'] == 'A') {
                $isOrderAscending = true;
            }

            $length = null;
            if($index['Sub_part'] != null) {
                $length = $index['Sub_part'];
            }

            $tableIndex->addIndexedColumn($index['Column_name'], $index['Seq_in_index'], $isOrderAscending, $length);
        }
    }

    private function buildRelatedTableConstraints(Table $table, $relatedTables) {
        foreach($relatedTables as $relatedTable) {
            if($relatedTable['CONSTRAINT_NAME'] != 'PRIMARY') {
                $relatedTableConstraint = new RelatedTableConstraint();
                $relatedTableConstraint->setName($relatedTable['CONSTRAINT_NAME']);
                $relatedTableConstraint->setTableName($relatedTable['TABLE_NAME']);
                $relatedTableConstraint->setColumnName($relatedTable['COLUMN_NAME']);
                $relatedTableConstraint->setReferencedColumnName($relatedTable['REFERENCED_COLUMN_NAME']);
                $table->addRelatedTableConstraint($relatedTableConstraint);
            }
        }
    }
}

?>
