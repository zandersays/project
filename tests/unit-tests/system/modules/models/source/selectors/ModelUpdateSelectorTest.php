<?php

/**
 * Description of ModelSelectorUpdateTest
 *
 * @author Kam Sheffield
 * @version 09/13/2011
 */
class ModelUpdateSelectorTest extends PHPUnit_Framework_TestCase {

    /**
     *
     * @var ModelSelectorUpdate
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
        $this->object = new ModelUpdateSelector(AtlasInstance::Model);
    }

    protected function tearDown() {
        Database::removeDatabaseDriver($this->databaseDriver->getId());
        $this->databaseDriver->closeConnection();
    }

    public function testBasic() {
        $expected = 'UPDATE `atlas_instance` SET `atlas_instance`.`id` = \'6\';';
        $actual = $this->object
                    ->withValues(array(AtlasInstance::Id => 6))
                    ->toSql();
        $this->assertEquals($expected, $actual);
    }

    public function testWithColumnsAndFilterBy() {
        $expected = 'UPDATE `atlas_instance` SET `atlas_instance`.`id` = \'6\' WHERE `atlas_instance`.`id` = \'5\';';
        $actual = $this->object
                    ->withValues(array(AtlasInstance::Id => 6))
                    ->filterBy(AtlasInstance::Id, '5')
                    ->toSql();
        $this->assertEquals($expected, $actual);
    }

    public function testWithColumnsMultipleFilteryBy() {
        $expected = 'UPDATE `atlas_instance` SET `atlas_instance`.`id` = \'6\' WHERE `atlas_instance`.`id` = \'5\' OR `atlas_instance`.`status` != \'offline\';';
        $actual = $this->object
                    ->withValues(array(AtlasInstance::Id => 6))
                    ->filterBy(AtlasInstance::Id, '5')
                    ->orWith()
                    ->filterBy(AtlasInstance::Status, 'offline', Comparator::NotEqual)
                    ->toSql();
        $this->assertEquals($expected, $actual);
    }

    public function testMultipleColumnsMultipleValues() {
        $expected = 'UPDATE `atlas_instance` SET `atlas_instance`.`id` = \'6\', `atlas_instance`.`meta` = \'meeta\' WHERE (`atlas_instance`.`id` = \'5\' OR `atlas_instance`.`id` = \'6\');';
        $actual = $this->object
                    ->withValues(array(AtlasInstance::Id => 6, AtlasInstance::Meta => 'meeta'))
                    ->filterBy(AtlasInstance::Id, array(5, 6))
                    ->toSql();
        $this->assertEquals($expected, $actual);
    }

    public function testMultipleColumnsMultipleValuesMultipleFilterBy() {
        $expected = 'UPDATE `atlas_instance` SET `atlas_instance`.`id` = \'6\', `atlas_instance`.`meta` = \'meeta\' WHERE (`atlas_instance`.`id` = \'5\' OR `atlas_instance`.`id` = \'6\') AND `atlas_instance`.`status` = \'offline\';';
        $actual = $this->object
                    ->withValues(array(AtlasInstance::Id => 6, AtlasInstance::Meta => 'meeta'))
                    ->filterBy(AtlasInstance::Id, array(5, 6))
                    ->andWith()
                    ->filterBy(AtlasInstance::Status, 'offline')
                    ->toSql();
        $this->assertEquals($expected, $actual);
    }

    public function testUsingSql() {
        $expected = 'UPDATE `atlas_instance` SET `atlas_instance`.`id` = \'6\', `atlas_instance`.`meta` = \'meeta\' WHERE (`atlas_instance`.`id` = \'5\' OR `atlas_instance`.`id` = \'6\') AND `atlas_instance`.`status` = \'offline\';';
        $actual = $this->object
                    ->usingSql($expected)
                    ->toSql();
        $this->assertEquals($expected, $actual);
    }

    public function testBasicUpdate() {
        $count = Database::query('SELECT COUNT(*) FROM `post`');
        $count = $count[0]['COUNT(*)'];

        Database::query('INSERT INTO `post`(`user_id`, `category_id`, `content`, `time_added`) VALUES (1, 1, \'I like pizza!\', NOW());');
        $newCount = Database::query('SELECT COUNT(*) FROM `post`');
        $newCount = $newCount[0]['COUNT(*)'];

        $lastInsertId = Database::getLastInsertId();
        $this->assertEquals($newCount, $count + 1);

        $results = Post::update()
                ->filterBy(Post::Id, $lastInsertId)
                ->withValues(array(Post::Content => 'Dude I just totally changed what I posted about pizza!!'))
                ->execute();
        $this->assertEquals(1, $results->getCount());

        $post = Post::read()
                    ->filterBy(Post::Id, $lastInsertId)
                    ->select()
                    ->execute();

        $this->assertEquals(1, $post->getCount());
        $this->assertInstanceOf('Post', $post->getFirst());
        $this->assertEquals('Dude I just totally changed what I posted about pizza!!', $post->getFirst()->get(Post::Content));

        Database::query('DELETE FROM `post` WHERE `id` = \''.$lastInsertId.'\'');

        $newCount = Database::query('SELECT COUNT(*) FROM `post`');
        $newCount = $newCount[0]['COUNT(*)'];

        $this->assertEquals($count, $newCount);
    }
}

?>
