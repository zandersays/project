<?php

/**
 * Description of TableTest
 *
 * @author Kam Sheffield
 * @version 08/23/2011
 */
class TableTest extends PHPUnit_Framework_TestCase {

    /**
     *
     * @var Table
     */
    private $object;

    protected function setUp() {
        $this->object = new Table();
    }

    protected function tearDown() {

    }

    public function testCreateFromModelRequirements() {
        $modelRequirements = array(
            0 => array(
                'name' => 'id',
                'type' => 'int(11) unsigned',
                'null' => 'no',
                'key' => array('type' => 'primary'),
                'default' => null,
                'extra' => 'auto_increment'
            ),
            1 => array(
                'name' => 'atlas_instance_ip_address_id',
                'type' => 'int(11) unsigned',
                'null' => 'no',
                'key' => array('type' => 'foreign', 'referenced_table' => 'altas_instance_ip_address', 'referenced_column' => 'id'),
                'default' => null,
                'extra' => ''
            ),
            2 => array(
                'name' => 'bacon_head',
                'type' => 'int(11) unsigned',
                'null' => 'no',
                'key' => null,
                'default' => null,
                'extra' => ''
            ),
            3 => array(
                'name' => 'identifier',
                'type' => 'bogus_type',
                'null' => 'no',
                'key' => null,
                'default' => null,
                'extra' => ''
            )
        );

        $table = Table::createFromModelRequirements('AtlasInstance', $modelRequirements);

        $this->assertEquals('atlas_instance', $table->getName());
        $this->assertEquals(4, count($table->getColumnArray()));
        $this->assertEquals(2, count($table->getIndexArray()));
        $this->assertEquals(1, count($table->getForeignKeyConstraintArray()));
    }

    public function testBasic() {
        $expected = 'utf8';
        $actual = $this->object->getCharacterSet();
        $this->assertEquals($expected, $actual);

        $this->object->setCharacterSet('ascii');
        $expected = 'ascii';
        $actual = $this->object->getCharacterSet();
        $this->assertEquals($expected, $actual);

        $expected = 'InnoDB';
        $actual = $this->object->getEngine();
        $this->assertEquals($expected, $actual);

        $this->object->setEngine('MYISAM');
        $expected = 'MYISAM';
        $actual = $this->object->getEngine();
        $this->assertEquals($expected, $actual);

        $this->object->setName('test_table');
        $expected = 'test_table';
        $actual = $this->object->getName();
        $this->assertEquals($expected, $actual);
    }

    public function testAddColumn() {
        $column = new TableColumn('test_column', 'int');
        $column->setIsPrimaryKey(true);

        $this->assertEquals(0, sizeof($this->object->getColumnArray()));
        $this->assertEquals(0, sizeof($this->object->getIndexArray()));
        $this->assertFalse($this->object->containsColumn('test_column'));
        $this->object->addColumn($column);
        $this->assertEquals(1, sizeof($this->object->getColumnArray()));
        $this->assertEquals(1, sizeof($this->object->getIndexArray()));
        $this->assertTrue($this->object->containsColumn('test_column'));
    }

    public function testRemoveColumn() {
        $column = new TableColumn('test_column', 'int');
        $column->setIsPrimaryKey(true);

        $this->assertEquals(0, sizeof($this->object->getColumnArray()));
        $this->assertEquals(0, sizeof($this->object->getIndexArray()));
        $this->assertFalse($this->object->containsColumn('test_column'));
        $this->object->addColumn($column);
        $this->assertEquals(1, sizeof($this->object->getColumnArray()));
        $this->assertEquals(1, sizeof($this->object->getIndexArray()));
        $this->assertTrue($this->object->containsColumn('test_column'));
        $this->object->removeColumn('test_column');
        $this->assertEquals(0, sizeof($this->object->getColumnArray()));
        $this->assertEquals(0, sizeof($this->object->getIndexArray()));
        $this->assertFalse($this->object->containsColumn('test_column'));
    }

    public function testAddForeignKey() {
        $foreignKey = new ForeignKeyConstraint('test', 'test_column', 'test_table', 'test_table_column');

        $this->assertEquals(0, sizeof($this->object->getForeignKeyConstraintArray()));
        $this->assertEquals(0, sizeof($this->object->getIndexArray()));
        $this->assertFalse($this->object->containsForeignKeyConstraint('test'));
        $this->object->addForgeignKeyConstraint($foreignKey);
        $this->assertEquals(1, sizeof($this->object->getForeignKeyConstraintArray()));
        $this->assertEquals(1, sizeof($this->object->getIndexArray()));
        $this->assertTrue($this->object->containsForeignKeyConstraint('test'));
        $this->object->removeForeignKeyConstraint('test');
        $this->assertEquals(0, sizeof($this->object->getForeignKeyConstraintArray()));
        $this->assertEquals(0, sizeof($this->object->getIndexArray()));
        $this->assertFalse($this->object->containsForeignKeyConstraint('test'));
    }
}

?>
