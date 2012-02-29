<?php

/**
 * Description of ObjectTest
 *
 * @author Kam Sheffield
 * @version 08/21/2011
 */
class ObjectTest extends PHPUnit_Framework_TestCase {

    /**
     *
     * @var Table
     */
    protected $object;

    protected function setUp() {
        $table = new Table('table_name');
        $table->addColumn(new TableColumn('column', 'int'));
        $table->addColumn(new TableColumn('column1', 'int'));
        $primaryKeyIndex = new TableIndex('column_pk', TableIndexType::Primary);
        $primaryKeyIndex->addIndexedColumn('column', 1);
        $table->addIndex($primaryKeyIndex);
        $tableForeignKey = new ForeignKeyConstraint('column1_fk', 'column1', 'table1', 'referenced_column');
        $table->addForgeignKeyConstraint($tableForeignKey);

        $this->object = $table;
    }

    protected function tearDown() {

    }

    /*
    public function testToJson() {
        echo "toJson: \n";
        echo Object::toJson($this->object);
    }

    public function testToJsonRecursive() {
        echo "toJsonRecursive: \n";
        echo Object::toJson($this->object, true);
    }

    public function testToJsonRecursiveWithTyping() {
        echo "toJsonRecursive: \n";
        echo Object::toJson($this->object, true, true);
    }
    */

    public function testFromJson() {
        // setup
        $json = Object::toJson($this->object, false, true);

        // test
        $object = Object::fromJson($json);

        // verify
        $this->assertTrue($object instanceof Table);
        $this->assertEquals('table_name', $object->getName());
        $this->assertEquals('InnoDB', $object->getEngine());
        $this->assertEquals('utf8', $object->getCharacterSet());
        $this->assertEquals(0, sizeof($object->getColumnArray()));
        $this->assertEquals(0, sizeof($object->getForeignKeyConstraintArray()));
        $this->assertEquals(0, sizeof($object->getIndexArray()));
    }

    public function testFromJsonRecursive() {
        // setup
        $json = Object::toJson($this->object, true, true);

        // test
        $object = Object::fromJson($json);

        // verify
        $this->assertInstanceOf("Table", $object);
        $this->assertEquals('table_name', $object->getName());
        $this->assertEquals('InnoDB', $object->getEngine());
        $this->assertEquals('utf8', $object->getCharacterSet());
        $this->assertEquals(2, sizeof($object->getColumnArray()));
        $this->assertEquals(1, sizeof($object->getForeignKeyConstraintArray()));
        $this->assertEquals(2, sizeof($object->getIndexArray()));

        $column = $object->getColumn('column');
        $this->assertInstanceOf('TableColumn', $column);
        $this->assertEquals('column', $column->getName());
        $this->assertEquals('int', $column->getDataType());

        $column = $object->getColumn('column1');
        $this->assertInstanceOf('TableColumn', $column);
        $this->assertEquals('column1', $column->getName());
        $this->assertEquals('int', $column->getDataType());

        $foreignKey = $object->getForeignKeyConstraint('column1_fk');
        $this->assertInstanceOf('ForeignKeyConstraint', $foreignKey);
        $this->assertEquals('column1', $foreignKey->getColumnName());
        $this->assertEquals('referenced_column', $foreignKey->getReferencedColumnName());
        $this->assertEquals('table1', $foreignKey->getReferencedTableName());

        $tableIndex = $object->getIndex('column_pk');
        $this->assertInstanceOf('TableIndex', $tableIndex);
        $this->assertEquals(TableIndexType::Primary, $tableIndex->getType());
        $this->assertTrue($tableIndex->containsIndexedColumn('column'));
    }


    public function testToArray() {
        $array = Object::toArray($this->object);
        $this->assertEquals('table_name', $array['name']);
        $this->assertEquals('utf8', $array['characterSet']);
        $this->assertEquals('InnoDB', $array['engine']);
        $this->assertNull($array['columnArray']);
        $this->assertNull($array['indexArray']);
        $this->assertNull($array['foreignKeyConstraintArray']);
        $this->assertNull($array['relatedTableConstraintArray']);
    }

    public function testToArrayRecursive() {
        $array = Object::toArray($this->object, true);
        $this->assertEquals('table_name', $array['name']);
        $this->assertEquals('utf8', $array['characterSet']);
        $this->assertEquals('InnoDB', $array['engine']);
        $this->assertEquals(2, sizeof($array['columnArray']));
        $this->assertEquals(2, sizeof($array['indexArray']));
        $this->assertEquals(1, sizeof($array['foreignKeyConstraintArray']));
        $this->assertEquals(0, sizeof($array['relatedTableConstraintArray']));
    }

    public function testToArrayRecursiveWithTyping() {
        $array = Object::toArray($this->object, true, true);
        $this->assertEquals('Table', $array['___php__type___']);
        $this->assertEquals('table_name', $array['name']);
        $this->assertEquals('utf8', $array['characterSet']);
        $this->assertEquals('InnoDB', $array['engine']);
        $this->assertEquals(3, sizeof($array['columnArray']));
        $this->assertEquals(3, sizeof($array['indexArray']));
        $this->assertEquals(2, sizeof($array['foreignKeyConstraintArray']));
        $this->assertEquals(1, sizeof($array['relatedTableConstraintArray']));
    }

    public function testToArrayWithStdClass() {
        $stdClass = new stdClass();
        $stdClass->bacon = 'cheese';
        $stdClass->cheese = 'bacon';

        $array = Object::toArray($stdClass);
        $this->assertEquals(2, sizeof($array));
        $this->assertEquals('cheese', $array['bacon']);
        $this->assertEquals('bacon', $array['cheese']);
    }

    public function testToArrayWithStdClassRecursive() {
        $stdClass = new stdClass();
        $stdClass->bacon = 'cheese';
        $stdClass->cheese = array('zesty' => 'breeze', 'breeze' => 'zesty');

        $array = Object::toArray($stdClass, true);
        $this->assertEquals(2, sizeof($array));
        $this->assertEquals('cheese', $array['bacon']);
        $this->assertEquals(2, sizeof($array['cheese']));
    }

    public function testToArrayWithAssociativeArray() {

    }
}

?>
