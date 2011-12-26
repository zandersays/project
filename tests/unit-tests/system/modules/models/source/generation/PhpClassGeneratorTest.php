<?php

/**
 * Description of PhpClassGeneratorTest
 *
 * @author Kam Sheffield
 * @version 08/20/2011
 */
class PhpClassGeneratorTest extends PHPUnit_Framework_TestCase {

    /**
     *
     * @var PhpClassGenerator
     */
    protected $object;

    /**
     *
     * @var DatabaseDriver
     */
    protected $databaseDriver;

    protected function setUp() {
        $this->databaseDriver = new DatabaseDriverMySql('atlasds', 'localhost', 'test', 'password');

        $modelDriver = ModelDriverFactory::create($this->databaseDriver);

        $schemaGenerator = new SchemaGenerator('AtlasInstance', $modelDriver);
        $table = $schemaGenerator->getSchema();

        $this->object = new PhpClassGenerator('AtlasInstance', $table, $modelDriver);
    }

    protected function tearDown() {
        $this->databaseDriver->closeConnection();
    }

    public function generateTestModels() {

    }

    public function testGetClass() {
        $class = $this->object->getClass();
        // TODO do something better with this
        $this->assertTrue($class != null && is_string($class));
    }
}

?>
