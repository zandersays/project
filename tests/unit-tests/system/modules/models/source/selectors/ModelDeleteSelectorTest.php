<?php

/**
 * Description of ModelSelectorDeleteTest
 *
 * @author Kam Sheffield
 * @version 09/13/2011
 */
class ModelDeleteSelectorTest extends PHPUnit_Framework_TestCase {

    /**
     *
     * @var ModelSelectorDelete
     */
    protected $object;

    /**
     *
     * @var DatabaseDriver
     */
    protected $databaseDriver;

    protected function setUp() {
        // set up the database
        $this->databaseDriver = new DatabaseDriverMySql('project_unit_test', 'localhost', 'test', 'password');
        Database::addDatabaseDriver($this->databaseDriver);
        ModelContext::setGlobalContext($this->databaseDriver);

        // create the ModelSelector
        $this->object = new ModelDeleteSelector(AtlasInstance::Model);
    }

    protected function tearDown() {
        Database::removeDatabaseDriver($this->databaseDriver->getId());
        $this->databaseDriver->closeConnection();
    }

    public function testBasic() {
        $expected = 'DELETE FROM `atlas_instance` WHERE `atlas_instance`.`id` = \'5\';';
        $actual = $this->object
                    ->filterBy(AtlasInstance::Id, '5')
                    ->toSql();
        $this->assertEquals($expected, $actual);
    }

    public function testMultipleFilteryBy() {
        $expected = 'DELETE FROM `atlas_instance` WHERE `atlas_instance`.`id` = \'5\' OR `atlas_instance`.`status` != \'offline\';';
        $actual = $this->object
                    ->filterBy(AtlasInstance::Id, '5')
                    ->orWith()
                    ->filterBy(AtlasInstance::Status, 'offline', Comparator::NotEqual)
                    ->toSql();
        $this->assertEquals($expected, $actual);
    }

    public function testMultipleValues() {
        $expected = 'DELETE FROM `atlas_instance` WHERE (`atlas_instance`.`id` = \'5\' OR `atlas_instance`.`id` = \'6\');';
        $actual = $this->object
                    ->filterBy(AtlasInstance::Id, array(5, 6))
                    ->toSql();
        $this->assertEquals($expected, $actual);
    }

    public function testMultipleValuesMultipleFilterBy() {
        $expected = 'DELETE FROM `atlas_instance` WHERE (`atlas_instance`.`id` = \'5\' OR `atlas_instance`.`id` = \'6\') AND `atlas_instance`.`status` = \'offline\';';
        $actual = $this->object
                    ->filterBy(AtlasInstance::Id, array(5, 6))
                    ->andWith()
                    ->filterBy(AtlasInstance::Status, 'offline')
                    ->toSql();
        $this->assertEquals($expected, $actual);
    }

    public function testUsingSql() {
        $expected = 'DELETE FROM `atlas_instance` WHERE (`atlas_instance`.`id` = \'5\' OR `atlas_instance`.`id` = \'6\') AND `atlas_instance`.`status` = \'offline\';';
        $actual = $this->object
                    ->usingSql($expected)
                    ->toSql();
        $this->assertEquals($expected, $actual);
    }

    public function testNoFilterBy() {
        $expected = 'DELETE FROM `atlas_instance`;';
        $actual = $this->object
                    ->toSql();
        $this->assertEquals($expected, $actual);
    }

    public function testBasicDelete() {
        $count = Database::query('SELECT COUNT(*) FROM `post`');
        $count = $count[0]['COUNT(*)'];

        Database::query('INSERT INTO `post`(`user_id`, `category_id`, `content`, `time_added`) VALUES (1, 1, \'Something Awesome!\', NOW());');
        $newCount = Database::query('SELECT COUNT(*) FROM `post`');
        $newCount = $newCount[0]['COUNT(*)'];

        $lastInsertId = Database::getLastInsertId();
        $this->assertEquals($newCount, $count + 1);

        $results = Post::remove()
                ->filterBy(Post::Id, $lastInsertId)
                ->execute();

        $this->assertEquals(1, $results->getCount());

        $newCount = Database::query('SELECT COUNT(*) FROM `post`');
        $newCount = $newCount[0]['COUNT(*)'];

        $this->assertEquals($count, $newCount);
    }
}

?>
