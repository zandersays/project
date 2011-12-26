<?php

/**
 * Description of SchemaGeneratorTest
 *
 * @author Kam Sheffield
 * @version 08/20/2011
 */
class SchemaGeneratorTest extends PHPUnit_Framework_TestCase {

    /**
     *
     * @var SchemaGenerator
     */
    private $object;

    /**
     *
     * @var DatabaseDriver
     */
    protected $databaseDriver;

    protected function setUp() {
        $this->databaseDriver = new DatabaseDriverMySql('atlasds', 'localhost', 'test', 'password');

        $modelDriver = ModelDriverFactory::create($this->databaseDriver);

        $this->object = new SchemaGenerator('AtlasInstance', $modelDriver);
    }

    protected function tearDown() {
        $this->databaseDriver->closeConnection();
    }

    public function testGetSchema() {
        $table = $this->object->getSchema();

        $this->assertEquals('atlas_instance', $table->getName());
        $this->assertEquals(8, count($table->getColumnArray()));
        $this->assertEquals(7, count($table->getIndexArray()));
        $this->assertEquals(2, count($table->getForeignKeyConstraintArray()));
        $this->assertEquals(4, count($table->getRelatedTableConstraintArray()));

        foreach($table->getColumnArray() as $tableColumn) {
            if($tableColumn->getName() == 'id') {
                $this->assertEquals('int', $tableColumn->getDataType());
                $this->assertEquals('11', $tableColumn->getLength());
                $this->assertTrue($tableColumn->getIsPrimaryKey());
                $this->assertFalse($tableColumn->getIsForeignKey());
                $this->assertTrue($tableColumn->getIsNonNull());
                $this->assertFalse($tableColumn->getIsBinary());
                $this->assertTrue($tableColumn->getIsUnsigned());
                $this->assertFalse($tableColumn->getIsZeroFill());
                $this->assertTrue($tableColumn->getIsAutoIncrementing());
                $this->assertNull($tableColumn->getDefaultValue());
            }
            else if($tableColumn->getName() == 'zeus_instance_id') {
                $this->assertEquals('int', $tableColumn->getDataType());
                $this->assertEquals('11', $tableColumn->getLength());
                $this->assertFalse($tableColumn->getIsPrimaryKey());
                $this->assertTrue($tableColumn->getIsForeignKey());
                $this->assertFalse($tableColumn->getIsNonNull());
                $this->assertFalse($tableColumn->getIsBinary());
                $this->assertTrue($tableColumn->getIsUnsigned());
                $this->assertFalse($tableColumn->getIsZeroFill());
                $this->assertFalse($tableColumn->getIsAutoIncrementing());
                $this->assertNull($tableColumn->getDefaultValue());
            }
            else if($tableColumn->getName() == 'atlas_instance_ip_address_id') {
                $this->assertEquals('int', $tableColumn->getDataType());
                $this->assertEquals('11', $tableColumn->getLength());
                $this->assertFalse($tableColumn->getIsPrimaryKey());
                $this->assertTrue($tableColumn->getIsForeignKey());
                $this->assertTrue($tableColumn->getIsNonNull());
                $this->assertFalse($tableColumn->getIsBinary());
                $this->assertTrue($tableColumn->getIsUnsigned());
                $this->assertFalse($tableColumn->getIsZeroFill());
                $this->assertFalse($tableColumn->getIsAutoIncrementing());
                $this->assertEquals(0, $tableColumn->getDefaultValue());
            }
        }
    }
}

?>
