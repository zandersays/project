<?php

/**
 * Description of ModelManagerTest
 *
 * @author Kam Sheffield
 * @version 08/23/2011
 */
class ModelManagerTest extends PHPUnit_Framework_TestCase {

    /**
     *
     * @var ModelManager
     */
    private $object;

    /**
     *
     * @var DatabaseDriver
     */
    private $databaseDriver;

    protected function setUp() {
        $this->databaseDriver = new DatabaseDriverMySql('atlasds', 'localhost', 'test', 'password');

        $fileLocation = Project::getProjectPath().'tests/temp/';

        $this->object = new ModelManager($this->databaseDriver, $fileLocation, false);
    }

    protected function tearDown() {
        $this->databaseDriver->closeConnection();
    }

    public function testCheckModelRequirementsTableExists() {
        $tableName = 'AtlasInstance';
        $tableFields = array(
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

        $results = $this->object->checkModelRequirements($tableName, $tableFields);

        $this->assertEquals(5, count($results));

        $keys = array_keys($results);

        $this->assertTrue(Arr::contains('id', $keys));
        $this->assertTrue(Arr::contains('atlas_instance_ip_address_id', $keys));
        $this->assertTrue(Arr::contains('bacon_head', $keys));
        $this->assertTrue(Arr::contains('identifier', $keys));

        $this->assertTrue($results['id']['columnExistsInTable']);
        $this->assertTrue($results['id']['typeMatch']);

        $this->assertTrue($results['atlas_instance_ip_address_id']['columnExistsInTable']);
        $this->assertTrue($results['atlas_instance_ip_address_id']['typeMatch']);

        $this->assertFalse($results['bacon_head']['columnExistsInTable']);
        $this->assertFalse($results['bacon_head']['typeMatch']);

        $this->assertFalse($results['identifier']['typeMatch']);

        $this->assertTrue($results['tableExists']);
    }

    public function testCheckModelRequirementsTableDoesNotExist() {
        $tableName = 'BogusTable';
        $tableFields = array(
            0 => array(
                'name' => 'id',
                'type' => 'int(11) unsigned',
                'null' => 'no',
                'key' => array('type' => 'pri'),
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
                'key' => array('type' => 'foreign', 'referenced_table' => 'altas_instance_ip_address', 'referenced_column' => 'id'),
                'default' => null,
                'extra' => ''
            ),
            3 => array(
                'name' => 'identifier',
                'type' => 'bogus_type',
                'null' => 'no',
                'key' => array('type' => 'foreign', 'referenced_table' => 'altas_instance_ip_address', 'referenced_column' => 'id'),
                'default' => null,
                'extra' => ''
            )
        );

        $results = $this->object->checkModelRequirements($tableName, $tableFields);

        $this->assertEquals(1, count($results));
        $this->assertFalse($results['tableExists']);
    }

    public function testGetModelModelTable() {
        $results = $this->object->getModel('AtlasInstance');

        $this->assertTrue($results['existsInModels']);
        $this->assertFalse(String::isNullOrEmpty($results['timeCreated']));
        $this->assertTrue($results['existsInDatabase']);
        $this->assertFalse(String::isNullOrEmpty($results['tableName']));
        $this->assertEquals(8, count($results['fields']));
    }

    public function testGetModelNoModelNoTable() {
        $results = $this->object->getModel('some really dumb model');

        $this->assertFalse($results['existsInModels']);
        $this->assertEquals("", $results['timeCreated']);
        $this->assertFalse($results['existsInDatabase']);
        $this->assertEquals("", $results['tableName']);
        $this->assertEquals(0, count($results['fields']));
    }

    public function testGetModelNoModelTable() {

    }

    public function testGetModelModelNoTable() {

    }

    public function testGetModels() {

    }
}

?>
