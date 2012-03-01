<?php

/**
 *
 * @author Kam Sheffield
 * @version 09/14/2011
 */
class ModelTest extends PHPUnit_Framework_TestCase {

    /**
     * @var AtlasInstance
     */
    protected $object;

    /**
     *
     * @var DatabaseDriver
     */
    protected $databaseDriver;

    protected function setUp() {
         // set up the database
        $this->databaseDriver = new DatabaseDriverMySql('atlasds', 'localhost', 'test', 'password');
        Database::addDatabaseDriver($this->databaseDriver);
        ModelContext::setGlobalContext($this->databaseDriver);

        $this->object = new AtlasInstance();
    }

    protected function tearDown() {
        Database::removeDatabaseDriver($this->databaseDriver->getId());
        $this->databaseDriver->closeConnection();
    }

    public function testGetModelName() {
        $this->assertEquals('AtlasInstance', $this->object->getModelName());
    }

    public function testGetTableName() {
        $this->assertEquals('atlas_instance', $this->object->getTableName());
    }

    public function testGetPrimaryKeyName() {
        $this->assertEquals('id', $this->object->getPrimaryKeyName());
    }

    public function testToArray() {
        $this->object->setId(10);
        $this->object->setIdentifier("123456");
        $this->object->setMeta("meata");
        $this->object->setStatus("offline");

        $array = $this->object->toArray();

        $this->assertEquals(10, $array['id']);
        $this->assertEquals('123456', $array['identifier']);
        $this->assertEquals('meata', $array['meta']);
        $this->assertEquals('offline', $array['status']);
    }

    public function testToArrayRecursive() {
        $this->object->newAtlasCommand();
        $this->object->newAtlasCommand();
        $this->object->newAtlasLog();
        $this->object->newAtlasLog();
        $this->object->newAtlasLog();
        $this->object->newAtlasTask();

        $array = $this->object->toArray(true);

        $this->assertEquals(3, count($array['relatedModels']['atlasLog.atlasInstanceId']));
        $this->assertEquals(2, count($array['relatedModels']['atlasCommand.atlasInstanceId']));
        $this->assertEquals(1, count($array['relatedModels']['atlasTask.atlasInstanceId']));
        $this->assertEquals(0, count($array['relatedModels']['atlasTaskResults.atlasInstanceId']));
        $this->assertEquals(0, count($array['relatedModels']['atlasInstance.zeusInstanceId']));
        $this->assertEquals(0, count($array['relatedModels']['atlasInstance.atlasInstanceIpAddressId']));
    }

    public function testGetHashCode() {        
        $hash = $this->object->getHashCode();
        $this->object->setTimeAdded(Sql::now());
        $newHash = $this->object->getHashCode();
        $this->assertTrue($hash != $newHash);
        
        $this->object->setTimeAdded(null);
        $newHash = $this->object->getHashCode();
        $this->assertEquals($hash, $newHash);
        
        $this->object->setId(1);
        $hash = $newHash;
        $newHash = $this->object->getHashCode();
        $this->assertTrue($hash != $newHash);
        
        $this->object->setIdentifier('bajblakjlk;djasf');
        $hash = $newHash;
        $newHash = $this->object->getHashCode();
        $this->assertEquals($hash,$newHash);
    }

    public function testAddRelatedModel() {
        $this->assertEquals(0, $this->object->getAtlasLogs()->getSize());
        $this->object->addRelatedModel(new AtlasLog(), AtlasLog::AtlasInstanceId);
        $this->assertEquals(1, $this->object->getAtlasLogs()->getSize());
    }

    public function testGetRelatedModelList() {
        $this->assertEquals(0, $this->object->getRelatedModelList(AtlasLog::AtlasInstanceId)->getSize());
        $this->object->addRelatedModel(new AtlasLog(), AtlasLog::AtlasInstanceId);
        $this->assertEquals(1, $this->object->getRelatedModelList(AtlasLog::AtlasInstanceId)->getSize());
    }

    public function testSaveInsertInDebugMode() {
        ModelContext::setDebug(null, true);

        $this->object->setAtlasInstanceIpAddressId(1);
        $this->object->setIdentifier("123456789");
        $this->object->setMeta('meata');
        $this->object->setStatus('push');
        $this->object->setTimeAdded(Sql::now());
        $this->object->setTimeUpdated(Sql::now());
        $this->object->setZeusInstanceId(1);
        $actual = $this->object->save();

        $expected = 'INSERT INTO `atlas_instance` (`atlas_instance_ip_address_id`, `identifier`, `meta`, `status`, `time_added`, `time_updated`, `zeus_instance_id`) VALUES (\'1\', \'123456789\', \'meata\', \'push\', NOW(), NOW(), \'1\');';
        $this->assertEquals($expected, $actual);
    }

    public function testSaveUpdateInDebugMode() {
        $atlasInstance = AtlasInstance::cast(AtlasInstance::read()->filterBy(AtlasInstance::Id, 1)->select()->execute()->getFirst());
        $atlasInstance->setStatus('offline');
        $atlasInstance->setMeta('bacon and cheese');

        ModelContext::setDebug(null, true);
        $actual = $atlasInstance->save();

        $expected = 'UPDATE `atlas_instance` SET `status` = \'offline\', `meta` = \'bacon and cheese\' WHERE `id` = \'1\';';
        $this->assertEquals($expected, $actual);
    }

    public function testDeleteInDebugMode() {
        $atlasInstance = AtlasInstance::cast(AtlasInstance::read()->filterBy(AtlasInstance::Id, 1)->select()->execute()->getFirst());

        ModelContext::setDebug(null, true);
        $actual = $atlasInstance->delete();

        $expected = 'DELETE FROM `atlas_instance` WHERE `id` IN (\'1\');';
        $this->assertEquals($expected, $actual);
    }

    public function testSaveInsert() {
        $count = Database::query('SELECT COUNT(*) FROM atlas_instance');
        $count = $count[0]['COUNT(*)'];

        $this->object->setAtlasInstanceIpAddressId(1);
        $this->object->setIdentifier("123456789");
        $this->object->setMeta('meata');
        $this->object->setStatus('push');
        $this->object->setTimeAdded(Sql::now());
        $this->object->setTimeUpdated(Sql::now());
        $this->object->setZeusInstanceId(1);
        $this->object->save();

        $newCount = Database::query('SELECT COUNT(*) FROM atlas_instance');
        $newCount = $newCount[0]['COUNT(*)'];

        $this->assertEquals(intval($count) + 1, intval($newCount));
    }

    public function testSaveUpdate() {
        $count = Database::query('SELECT COUNT(*) FROM atlas_instance');
        $count = $count[0]['COUNT(*)'];

        $results = Database::query('UPDATE `atlas_instance` SET `status` = \'push\' WHERE id = 1;');
        $results = Database::query('SELECT `status` FROM `atlas_instance` WHERE id = 1;');
        $status = $results[0]['status'];

        $atlasInstance = AtlasInstance::cast(AtlasInstance::read()->filterBy(AtlasInstance::Id, 1)->select()->execute()->getFirst());
        $atlasInstance->setStatus('offline');
        $atlasInstance->save();

        $newCount = Database::query('SELECT COUNT(*) FROM atlas_instance');
        $newCount = $newCount[0]['COUNT(*)'];

        $results = Database::query('SELECT `status` FROM `atlas_instance` WHERE id = 1;');
        $newStatus = $results[0]['status'];

        $this->assertEquals('push', $status);
        $this->assertEquals($count, $newCount);
        $this->assertFalse($status == $newStatus);
        $this->assertEquals('offline', $newStatus);
    }

    public function testDelete() {
        $count = Database::query('SELECT COUNT(*) FROM atlas_instance');
        $count = $count[0]['COUNT(*)'];

        $maxId = Database::query('SELECT MAX(id) FROM atlas_instance');
        $maxId= $maxId[0]['MAX(id)'];

        $atlasInstance = AtlasInstance::cast(AtlasInstance::read()->filterBy(AtlasInstance::Id, $maxId)->select()->execute()->getFirst());
        $atlasInstance->delete();

        $newCount = Database::query('SELECT COUNT(*) FROM atlas_instance');
        $newCount = $newCount[0]['COUNT(*)'];

        $this->assertEquals(intval($count) - 1, intval($newCount));
    }

    public function testSaveInsertRefresh() {
        $count = Database::query('SELECT COUNT(*) FROM atlas_instance');
        $count = $count[0]['COUNT(*)'];

        $this->object->setAtlasInstanceIpAddressId(1);
        $this->object->setIdentifier("123456789");
        $this->object->setMeta('meata');
        $this->object->setStatus('push');
        $this->object->setTimeAdded(Sql::now());
        $this->object->setTimeUpdated(Sql::now());
        $this->object->setZeusInstanceId(1);
        $this->object->save(true);

        $maxId = Database::query('SELECT MAX(id) FROM atlas_instance');
        $maxId= $maxId[0]['MAX(id)'];

        $newCount = Database::query('SELECT COUNT(*) FROM atlas_instance');
        $newCount = $newCount[0]['COUNT(*)'];

        $this->assertEquals(intval($count) + 1, intval($newCount));
        $this->assertEquals($maxId, $this->object->getId());
    }

    public function testSaveUpdateRefresh() {
        $count = Database::query('SELECT COUNT(*) FROM atlas_instance');
        $count = $count[0]['COUNT(*)'];

        $results = Database::query('UPDATE `atlas_instance` SET `status` = \'push\' WHERE id = 1;');
        $results = Database::query('SELECT `status` FROM `atlas_instance` WHERE id = 1;');
        $status = $results[0]['status'];

        $atlasInstance = AtlasInstance::cast(AtlasInstance::read()->filterBy(AtlasInstance::Id, 1)->select()->execute()->getFirst());
        $oldStatusFromModel = $atlasInstance->getStatus();
        $atlasInstance->setStatus('offline');
        $atlasInstance->save(true);
        $newStatusFromModel = $atlasInstance->getStatus();

        $newCount = Database::query('SELECT COUNT(*) FROM atlas_instance');
        $newCount = $newCount[0]['COUNT(*)'];

        $results = Database::query('SELECT `status` FROM `atlas_instance` WHERE id = 1;');
        $newStatus = $results[0]['status'];

        $this->assertEquals('push', $status);
        $this->assertEquals($count, $newCount);
        $this->assertFalse($status == $newStatus);
        $this->assertEquals('offline', $newStatus);

        $this->assertEquals($status, $oldStatusFromModel);
        $this->assertEquals($newStatus, $newStatusFromModel);
        $this->assertFalse($oldStatusFromModel == $newStatusFromModel);
    }

    public function testUpdate() {
        $count = Database::query('SELECT COUNT(*) FROM atlas_instance');
        $count = $count[0]['COUNT(*)'];

        $results = Database::query('UPDATE `atlas_instance` SET `status` = \'push\' WHERE id = 1;');
        $results = Database::query('SELECT `status` FROM `atlas_instance` WHERE id = 1;');
        $status = $results[0]['status'];

        $modelSelectorResults = AtlasInstance::update()->filterBy(AtlasInstance::Id, 1)->withValues(array(AtlasInstance::Status => 'offline'))->execute();
        $this->assertTrue($modelSelectorResults->getCount() > 0);

        $newCount = Database::query('SELECT COUNT(*) FROM atlas_instance');
        $newCount = $newCount[0]['COUNT(*)'];

        $results = Database::query('SELECT `status` FROM `atlas_instance` WHERE id = 1;');
        $newStatus = $results[0]['status'];

        $this->assertEquals('push', $status);
        $this->assertEquals($count, $newCount);
        $this->assertFalse($status == $newStatus);
        $this->assertEquals('offline', $newStatus);
    }

    public function testRemove() {
        $count = Database::query('SELECT COUNT(*) FROM atlas_instance');
        $count = $count[0]['COUNT(*)'];

        $maxId = Database::query('SELECT MAX(id) FROM atlas_instance');
        $maxId= $maxId[0]['MAX(id)'];

        $modelSelectorResults = AtlasInstance::remove()->filterBy(AtlasInstance::Id, $maxId)->execute();
        $this->assertTrue($modelSelectorResults->getCount() > 0);

        $newCount = Database::query('SELECT COUNT(*) FROM atlas_instance');
        $newCount = $newCount[0]['COUNT(*)'];

        $this->assertEquals(intval($count) - 1, intval($newCount));
    }
}

?>
