<?php

/**
 * Description of ModelDriverMySqlTest
 *
 * @author Kam Sheffield
 * @version 08/15/2011
 */
class ModelDriverMySqlTest extends PHPUnit_Framework_TestCase {

    /**
     *
     * @var ModelDriverMySql
     */
    protected $object;

    /**
     *
     * @var DatabaseDriver
     */
    protected $databaseDriver;

    protected function setUp() {
        $this->databaseDriver = new DatabaseDriverMySql('atlasds', 'localhost', 'test', 'password');
        $this->object = ModelDriverFactory::create($this->databaseDriver);
        $this->assertInstanceOf('ModelDriverMySql', $this->object);
    }

    protected function tearDown() {
        $this->databaseDriver->closeConnection();
    }

    public function testCreateTableColumnStringBasic() {
        $tableColumn = new TableColumn('id');
        $tableColumn->setDataType('INT');

        $actual = $this->object->createTableColumnString($tableColumn);
        $expected = '`id` INT NULL';
        $this->assertEquals($expected, $actual);
    }

    public function testCreateTableColumnStringWithLength() {
        $tableColumn = new TableColumn('id');
        $tableColumn->setDataType('INT');
        $tableColumn->setLength(11);

        $actual = $this->object->createTableColumnString($tableColumn);
        $expected = '`id` INT(11) NULL';
        $this->assertEquals($expected, $actual);
    }

    public function testCreateTableColumnStringNotNull() {
        $tableColumn = new TableColumn('id');
        $tableColumn->setDataType('INT');
        $tableColumn->setIsNonNull(true);

        $actual = $this->object->createTableColumnString($tableColumn);
        $expected = '`id` INT NOT NULL';
        $this->assertEquals($expected, $actual);
    }

    public function testCreateTableColumnStringDefault() {
        $tableColumn = new TableColumn('id');
        $tableColumn->setDataType('INT');
        $tableColumn->setDefaultValue(1);

        $actual = $this->object->createTableColumnString($tableColumn);
        $expected = '`id` INT NULL DEFAULT \'1\'';
        $this->assertEquals($expected, $actual);
    }

    public function testCreateTableColumnStringAutoIncrement() {
        $tableColumn = new TableColumn('id');
        $tableColumn->setDataType('INT');
        $tableColumn->setIsAutoIncrementing(true);

        $actual = $this->object->createTableColumnString($tableColumn);
        $expected = '`id` INT NULL AUTO_INCREMENT';
        $this->assertEquals($expected, $actual);
    }

    public function testCreateTableColumnStringUnsigned() {
        $tableColumn = new TableColumn('id');
        $tableColumn->setDataType('INT');
        $tableColumn->setIsUnsigned(true);

        $actual = $this->object->createTableColumnString($tableColumn);
        $expected = '`id` INT UNSIGNED NULL';
        $this->assertEquals($expected, $actual);
    }

    public function testCreateTableColumnStringZeroFill() {
        $tableColumn = new TableColumn('id');
        $tableColumn->setDataType('INT');
        $tableColumn->setIsZeroFill(true);

        $actual = $this->object->createTableColumnString($tableColumn);
        $expected = '`id` INT ZEROFILL NULL';
        $this->assertEquals($expected, $actual);
    }

    public function testCreateTableColumnStringBinary() {
        $tableColumn = new TableColumn('id');
        $tableColumn->setDataType('INT');
        $tableColumn->setIsBinary(true);

        $actual = $this->object->createTableColumnString($tableColumn);
        $expected = '`id` INT BINARY NULL';
        $this->assertEquals($expected, $actual);
    }

    public function testCreateTableColumnStringPK() {
        $tableColumn = new TableColumn('id');
        $tableColumn->setDataType('INT');
        $tableColumn->setIsAutoIncrementing(true);
        $tableColumn->setIsNonNull(true);
        $tableColumn->setIsUnsigned(true);
        $tableColumn->setLength(11);

        $actual = $this->object->createTableColumnString($tableColumn);
        $expected = '`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT';
        $this->assertEquals($expected, $actual);
    }

    public function testCreateTableIndexStringBasic() {
        $indexName = 'table_index';
        $tableIndex = new TableIndex($indexName, TableIndexType::Index);
        $columnName = 'table_column';
        $tableIndex->addIndexedColumn($columnName, 1);

        $actual = $this->object->createTableIndexString($tableIndex);
        $expected = 'INDEX `'.$indexName.'` (`'.$columnName.'` ASC)';
        $this->assertEquals($expected, $actual);
    }

    public function testCreateTableIndexStringSpatial() {
        $indexName = 'table_index';
        $tableIndex = new TableIndex($indexName, TableIndexType::Index);
        $columnName = 'table_column';
        $tableIndex->addIndexedColumn($columnName, 1);
        $tableIndex->setType(TableIndexType::Spatial);

        $actual = $this->object->createTableIndexString($tableIndex);
        $expected = 'SPATIAL INDEX `'.$indexName.'` (`'.$columnName.'` ASC)';
        $this->assertEquals($expected, $actual);
    }

    public function testCreateTableIndexStringFullTextWithLength() {
        $indexName = 'table_index';
        $tableIndex = new TableIndex($indexName, TableIndexType::Index);
        $columnName = 'table_column';
        $tableIndex->addIndexedColumn($columnName, 1, false, 128);
        $tableIndex->setType(TableIndexType::FullText);

        $actual = $this->object->createTableIndexString($tableIndex);
        $expected = 'FULLTEXT INDEX `'.$indexName.'` (`'.$columnName.'`(128) DESC)';
        $this->assertEquals($expected, $actual);
    }

    public function testCreateTableIndexStringUnique() {
        $indexName = 'table_index';
        $tableIndex = new TableIndex($indexName, TableIndexType::Index);
        $columnName = 'table_column';
        $tableIndex->addIndexedColumn($columnName, 1);
        $tableIndex->setType(TableIndexType::Unique);

        $actual = $this->object->createTableIndexString($tableIndex);
        $expected = 'UNIQUE INDEX `'.$indexName.'` (`'.$columnName.'` ASC)';
        $this->assertEquals($expected, $actual);
    }

    public function testCreateTableIndexStringPrimary() {
        $indexName = 'table_index';
        $tableIndex = new TableIndex($indexName, TableIndexType::Index);
        $columnName = 'table_column';
        $tableIndex->addIndexedColumn($columnName, 1, false, 128);
        $tableIndex->setType(TableIndexType::Primary);

        $actual = $this->object->createTableIndexString($tableIndex);
        $expected = 'PRIMARY KEY (`'.$columnName.'`)';
        $this->assertEquals($expected, $actual);
    }

    public function testCreateForeignKeyConstraintStringBasic() {
        $name = 'fk_table_column';
        $columnName = 'table_column';
        $referencedTableName = 'referenced_table_name';
        $referencedColumnName = 'refereced_column_name';
        $foreignKeyConstraint = new ForeignKeyConstraint($name, $columnName, $referencedTableName, $referencedColumnName);

        $actual = $this->object->createForeignKeyConstraintString($foreignKeyConstraint);
        $expected = 'CONSTRAINT `'.$name.'`'."\n    ".'FOREIGN KEY (`'.$columnName.'`)'."\n    ".'REFERENCES `'.$this->databaseDriver->getDatabaseName().'`.`'.$referencedTableName.'` (`'.$referencedColumnName.'`)'."\n    ".'ON DELETE CASCADE'."\n    ".'ON UPDATE CASCADE';
        $this->assertEquals($expected, $actual);
    }

    public function testCreateForeignKeyConstraintStringSetNull() {
        $name = 'fk_table_column';
        $columnName = 'table_column';
        $referencedTableName = 'referenced_table_name';
        $referencedColumnName = 'refereced_column_name';
        $foreignKeyConstraint = new ForeignKeyConstraint($name, $columnName, $referencedTableName, $referencedColumnName);
        $foreignKeyConstraint->setDeleteAction(ForeignKeyConstraintUpdateType::SetNull);
        $foreignKeyConstraint->setUpdateAction(ForeignKeyConstraintUpdateType::SetNull);

        $actual = $this->object->createForeignKeyConstraintString($foreignKeyConstraint);
        $expected = 'CONSTRAINT `'.$name.'`'."\n    ".'FOREIGN KEY (`'.$columnName.'`)'."\n    ".'REFERENCES `'.$this->databaseDriver->getDatabaseName().'`.`'.$referencedTableName.'` (`'.$referencedColumnName.'`)'."\n    ".'ON DELETE SET NULL'."\n    ".'ON UPDATE SET NULL';
        $this->assertEquals($expected, $actual);
    }

    public function testCreateTableBasic() {
        $tableName = 'table_name';
        $table = new Table($tableName);
        $table->addColumn(new TableColumn('column', 'int'));
        $table->addColumn(new TableColumn('column1', 'int'));
        $primaryKeyIndex = new TableIndex('column_pk', TableIndexType::Primary);
        $primaryKeyIndex->addIndexedColumn('column', 1);
        $table->addIndex($primaryKeyIndex);
        $tableForeignKey = new ForeignKeyConstraint('column1_fk', 'column1', 'table1', 'reference_column');
        $table->addForgeignKeyConstraint($tableForeignKey);

        $expected = 'CREATE TABLE IF NOT EXISTS `atlasds`.`table_name` (
  `column` int NULL,
  `column1` int NULL,
  PRIMARY KEY (`column`),
  INDEX `fk_column1` (`column1` ASC),
  CONSTRAINT `column1_fk`
    FOREIGN KEY (`column1`)
    REFERENCES `atlasds`.`table1` (`reference_column`)
    ON DELETE CASCADE
    ON UPDATE CASCADE
  )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;';

        $actual = $this->object->createTable($table, true);
        $this->assertEquals($expected, $actual);
    }

    public function testAlterTableBasic() {
        $tableName = 'table_name';
        $table = new Table($tableName);
        $table->addColumn(new TableColumn('column', 'int'));
        $table->addColumn(new TableColumn('column1', 'int'));
        $primaryKeyIndex = new TableIndex('column_pk', TableIndexType::Primary);
        $primaryKeyIndex->addIndexedColumn('column', 1);
        $table->addIndex($primaryKeyIndex);
        $tableForeignKey = new ForeignKeyConstraint('column1_fk', 'column1', 'table1', 'reference_column');
        $table->addForgeignKeyConstraint($tableForeignKey);

        $tableAlterer = new TableAlterer($table);
        $tableAlterer->rename('new_table');
        $tableAlterer->removeColumn('column1');
        $tableAlterer->addColumn(new TableColumn('column2', 'VARCHAR', 'WHAT?', '255'));
        $tableAlterer->addForeignKey(new ForeignKeyConstraint('fk1', 'column', 'ref_table', 'ref_col'));
        $primaryKeyIndex = new TableIndex('pk1', TableIndexType::Index);
        $primaryKeyIndex->addIndexedColumn('column2', 1);
        $tableAlterer->addIndex($primaryKeyIndex);

        $actual = $this->object->alterTable($tableAlterer, true);

        $expected = 'ALTER TABLE `table_name`
 RENAME `new_table`,
 ADD `column2` VARCHAR(255) NULL DEFAULT \'WHAT?\',
 DROP COLUMN `column1`,
 ADD INDEX `pk1` (`column2` ASC);
ALTER TABLE `table_name`
 ADD CONSTRAINT `fk1`
    FOREIGN KEY (`column`)
    REFERENCES `atlasds`.`ref_table` (`ref_col`)
    ON DELETE CASCADE
    ON UPDATE CASCADE;
';

        $this->assertEquals($expected, $actual);
    }
}

?>
