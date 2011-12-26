<?php

/**
 *
 * @author Kam Sheffield
 * @version 09/13/2011
 */
class ModelBuilderTest extends PHPUnit_Framework_TestCase {

    private static $databaseResultsArray = array(
        0 => array(
            'zeus_instance.id' => 1,
            'zeus_instance.status' => 'online',
            'zeus_log.id' => 1,
            'zeus_log.zeus_instance_id' => 1,
            'zeus_log.message' => 'hoooel',
            'atlas_instance.id' => 1,
            'atlas_instance.atlas_instance_ip_address_id' => 1,
            'atlas_instance.zeus_instance_id' => 1,
            'atlas_instance.identifier' => 'asdf',
            'atlas_instance.status' => 'push',
            'atlas_instance.meta' => '2011-07-27 15:43:37',
            'atlas_instance.time_updated' => '2011-07-27 15:43:37',
            'atlas_instance.time_added' => '2011-07-27 15:43:37',
            'atlas_log.id' => 1,
            'atlas_log.atlas_instance_id' => 1,
            'atlas_log.message' => 'asdf',
            'atlas_log.content' => 'ASDasddf',
            'atlas_log.ip_added_by' => '2011',
            'atlas_log.time_added' => '2011-07-28 13:59:18',
            'atlas_command.id' => 1,
            'atlas_command.status' => 'success',
            'atlas_command.command' => 'command_me',
            'atlas_command.atlas_instance_id' => '1'
        ),
        1 => array(
            'zeus_instance.id' => 1,
            'zeus_instance.status' => 'online',
            'zeus_log.id' => 2,
            'zeus_log.zeus_instance_id' => 1,
            'zeus_log.message' => 'hoooel',
            'atlas_instance.id' => 1,
            'atlas_instance.atlas_instance_ip_address_id' => 1,
            'atlas_instance.zeus_instance_id' => 1,
            'atlas_instance.identifier' => 'asdf',
            'atlas_instance.status' => 'push',
            'atlas_instance.meta' => '2011-07-27 15:43:37',
            'atlas_instance.time_updated' => '2011-07-27 15:43:37',
            'atlas_instance.time_added' => '2011-07-27 15:43:37',
            'atlas_log.id' => 2,
            'atlas_log.atlas_instance_id' => 1,
            'atlas_log.message' => 'beef',
            'atlas_log.content' => 'ncheese',
            'atlas_log.ip_added_by' => '2011',
            'atlas_log.time_added' => '2011-07-28 13:59:18',
            'atlas_command.id' => 2,
            'atlas_command.status' => 'success',
            'atlas_command.command' => 'command_me',
            'atlas_command.atlas_instance_id' => '1'
        ),
        2 => array(
            'zeus_instance.id' => 1,
            'zeus_instance.status' => 'online',
            'zeus_log.id' => 2,
            'zeus_log.zeus_instance_id' => 1,
            'zeus_log.message' => 'hoooel',
            'atlas_instance.id' => 2,
            'atlas_instance.atlas_instance_ip_address_id' => 1,
            'atlas_instance.zeus_instance_id' => 1,
            'atlas_instance.identifier' => 'asdf',
            'atlas_instance.status' => 'push',
            'atlas_instance.meta' => '2011-07-27 15:43:37',
            'atlas_instance.time_updated' => '2011-07-27 15:43:37',
            'atlas_instance.time_added' => '2011-07-27 15:43:37',
            'atlas_log.id' => 2,
            'atlas_log.atlas_instance_id' => 1,
            'atlas_log.message' => 'beef',
            'atlas_log.content' => 'ncheese',
            'atlas_log.ip_added_by' => '2011',
            'atlas_log.time_added' => '2011-07-28 13:59:18',
            'atlas_command.id' => 2,
            'atlas_command.status' => 'success',
            'atlas_command.command' => 'command_me',
            'atlas_command.atlas_instance_id' => '1'
        ),
        3 => array(
            'zeus_instance.id' => 2,
            'zeus_instance.status' => 'online',
            'zeus_log.id' => 2,
            'zeus_log.zeus_instance_id' => 1,
            'zeus_log.message' => 'hoooel',
            'atlas_instance.id' => 2,
            'atlas_instance.atlas_instance_ip_address_id' => 1,
            'atlas_instance.zeus_instance_id' => 1,
            'atlas_instance.identifier' => 'asdf',
            'atlas_instance.status' => 'push',
            'atlas_instance.meta' => '2011-07-27 15:43:37',
            'atlas_instance.time_updated' => '2011-07-27 15:43:37',
            'atlas_instance.time_added' => '2011-07-27 15:43:37',
            'atlas_log.id' => 2,
            'atlas_log.atlas_instance_id' => 1,
            'atlas_log.message' => 'beef',
            'atlas_log.content' => 'ncheese',
            'atlas_log.ip_added_by' => '2011',
            'atlas_log.time_added' => '2011-07-28 13:59:18',
            'atlas_command.id' => 2,
            'atlas_command.status' => 'success',
            'atlas_command.command' => 'command_me',
            'atlas_command.atlas_instance_id' => '1'
        )
    );

    private static $selectorDataArray = array(
        0 => array(
            'modelName' => 'AtlasInstance',
            'tableName' => 'atlas_instance',
            'relationKey' => 'atlas_instance.zeus_instance_id',
            'parent' => 'zeus_instance'
        ),
        1 => array(
            'modelName' => 'AtlasLog',
            'tableName' => 'atlas_log',
            'relationKey' => 'atlas_log.atlas_instance_id',
            'parent' => 'atlas_instance'
        ),
        2 => array(
            'modelName' => 'AtlasCommand',
            'tableName' => 'atlas_command',
            'relationKey' => 'atlas_command.atlas_instance_id',
            'parent' => 'atlas_instance'
        ),
        3 => array(
            'modelName' => 'ZeusInstance',
            'tableName' => 'zeus_instance',
            'relationKey' => null,
            'parent' => null
        ),
        4 => array(
            'modelName' => 'ZeusLog',
            'tableName' => 'zeus_log',
            'relationKey' => 'zeus_log.zeus_instance_id',
            'parent' => 'zeus_instance'
        )
    );

    /**
     * @var ModelBuilder
     */
    protected $object;

    protected function setUp() {
        $this->object = new ModelBuilder('zeus_instance', array('data' => self::$databaseResultsArray, 'count' => 0), self::$selectorDataArray);
    }

    protected function tearDown() {

    }

    public function testGetModels() {
        $modelArray = $this->object->getModels();
        $this->assertEquals(2, count($modelArray));
        $zeusInstance = $modelArray[0];
        $this->assertEquals(2, $zeusInstance->getRelatedModelList(ZeusLog::ZeusInstanceId)->getSize());

        $atlasInstanceModelList = $zeusInstance->getRelatedModelList(AtlasInstance::ZeusInstanceId);
        $this->assertEquals(2, $atlasInstanceModelList->getSize());

        $atlasInstance = $atlasInstanceModelList->getFirst();
        $this->assertEquals(2, $atlasInstance->getRelatedModelList(AtlasLog::AtlasInstanceId)->getSize());
        $this->assertEquals(2, $atlasInstance->getRelatedModelList(AtlasCommand::AtlasInstanceId)->getSize());

        $atlasInstance = $atlasInstanceModelList->getLast();
        $this->assertEquals(1, $atlasInstance->getRelatedModelList(AtlasLog::AtlasInstanceId)->getSize());
        $this->assertEquals(1, $atlasInstance->getRelatedModelList(AtlasCommand::AtlasInstanceId)->getSize());

        $zeusInstance = $modelArray[1];
        $this->assertEquals(1, $zeusInstance->getRelatedModelList(ZeusLog::ZeusInstanceId)->getSize());

        $this->assertEquals(1, $zeusInstance->getRelatedModelList(AtlasInstance::ZeusInstanceId)->getSize());
        $this->assertEquals(1, $zeusInstance->getRelatedModelList(AtlasInstance::ZeusInstanceId)->getFirst()->getRelatedModelList(AtlasLog::AtlasInstanceId)->getSize());
        $this->assertEquals(1, $zeusInstance->getRelatedModelList(AtlasInstance::ZeusInstanceId)->getFirst()->getRelatedModelList(AtlasCommand::AtlasInstanceId)->getSize());
    }
}
?>
