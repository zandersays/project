<?php

/**
 *
 * @author Kam Sheffield
 * @version 09/13/2011
 */
class ModelSelectorTest extends PHPUnit_Framework_TestCase {

    /**
     * @var ModelSelector
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
        $this->object = new ModelSelector(AtlasInstance::Table);
    }

    protected function tearDown() {
        Database::removeDatabaseDriver($this->databaseDriver->getId());
        $this->databaseDriver->closeConnection();
    }

    public function testBasic() {
        $expected = 'SELECT `atlas_instance`.* FROM `atlas_instance`;';
        $actual = $this->object
                    ->toSql();
        $this->assertEquals($expected, $actual['selectSql']);
    }

    public function testNoColumns() {
        $expected = 'SELECT `atlas_instance`.`id` FROM `atlas_instance`;';
        $actual = $this->object
                    ->withColumns()
                    ->toSql();
        $this->assertEquals($expected, $actual['selectSql']);
    }

    public function testWithColumns() {
        $expected = 'SELECT `atlas_instance`.`id`, `atlas_instance`.`identifier`, `atlas_instance`.`status`, `atlas_instance`.`time_added` FROM `atlas_instance`;';
        $actual = $this->object
                    ->withColumns(AtlasInstance::Id, AtlasInstance::Identifier, AtlasInstance::Status, AtlasInstance::TimeAdded)
                    ->toSql();
        $this->assertEquals($expected, $actual['selectSql']);
    }

    public function testDistinctBasic() {
        $expected = 'SELECT DISTINCT `atlas_instance`.* FROM `atlas_instance`;';
        $actual = $this->object
                    ->asDistinct()
                    ->toSql();
        $this->assertEquals($expected, $actual['selectSql']);
    }

    public function testFilterByBasic() {
        $expected = 'SELECT `atlas_instance`.* FROM `atlas_instance` WHERE `atlas_instance`.`id` = \'1\';';
        $actual = $this->object
                    ->filterBy(AtlasInstance::Id, 1)
                    ->toSql();
        $this->assertEquals($expected, $actual['selectSql']);
    }

    public function testWhereBasic() {
        $expected = 'SELECT `atlas_instance`.* FROM `atlas_instance` WHERE atlas_instance.id = 1;';
        $actual = $this->object
                    ->where('atlas_instance.id = 1')
                    ->toSql();
        $this->assertEquals($expected, $actual['selectSql']);
    }

    public function testFilterByComplex() {
        $expected = 'SELECT `atlas_instance`.* FROM `atlas_instance` WHERE `atlas_instance`.`id` = \'1\' AND `atlas_instance`.`status` = \'online\' OR `atlas_instance`.`time_added` = NOW();';
        $actual = $this->object
                    ->filterBy(AtlasInstance::Id, 1)
                    ->andWith()
                    ->filterBy(AtlasInstance::Status, 'online')
                    ->orWith()
                    ->filterBy(AtlasInstance::TimeAdded, Sql::now())
                    ->toSql();
        $this->assertEquals($expected, $actual['selectSql']);
    }

    public function testWithRelationBasic() {
        $expected = 'SELECT `atlas_instance`.*, `atlas_log`.* FROM `atlas_instance` LEFT OUTER JOIN `atlas_log` AS `atlas_log` ON `atlas_instance`.`id` = `atlas_log`.`atlas_instance_id`;';
        $actual = $this->object
                    ->withRelation(AtlasLog::Table, AtlasLog::AtlasInstanceId)
                    ->toSql();
        $this->assertEquals($expected, $actual['selectSql']);
    }

    public function testWithRelationMultiples() {
        $expected = 'SELECT `atlas_instance`.*, `atlas_log`.*, `atlas_command`.*, `atlas_task`.* FROM `atlas_instance` LEFT OUTER JOIN `atlas_log` AS `atlas_log` ON `atlas_instance`.`id` = `atlas_log`.`atlas_instance_id` LEFT OUTER JOIN `atlas_command` AS `atlas_command` ON `atlas_instance`.`id` = `atlas_command`.`atlas_instance_id` LEFT OUTER JOIN `atlas_task` AS `atlas_task` ON `atlas_instance`.`id` = `atlas_task`.`atlas_instance_id`;';
        $actual = $this->object
                    ->withRelation(AtlasLog::Model, AtlasLog::AtlasInstanceId)
                        ->close()
                    ->withRelation(AtlasCommand::Model, AtlasCommand::AtlasInstanceId)
                        ->close()
                    ->withRelation(AtlasTask::Model, AtlasTask::AtlasInstanceId)
                    ->toSql();
        $this->assertEquals($expected, $actual['selectSql']);
    }

    public function testWithRelationsNested() {
        $expected = 'SELECT `zeus_instance`.*, `atlas_instance`.*, `atlas_log`.*, `atlas_command`.* FROM `zeus_instance` LEFT OUTER JOIN `atlas_instance` AS `atlas_instance` ON `zeus_instance`.`id` = `atlas_instance`.`zeus_instance_id` LEFT OUTER JOIN `atlas_log` AS `atlas_log` ON `atlas_instance`.`id` = `atlas_log`.`atlas_instance_id` LEFT OUTER JOIN `atlas_command` AS `atlas_command` ON `atlas_instance`.`id` = `atlas_command`.`atlas_instance_id`;';
        $actual = ZeusInstance::read()
                    ->withRelation(AtlasInstance::Model, AtlasInstance::ZeusInstanceId)
                        ->withRelation(AtlasLog::Model, AtlasLog::AtlasInstanceId)
                            ->close()
                        ->withRelation(AtlasCommand::Model, AtlasCommand::AtlasInstanceId)
                            ->close()
                    ->toSql();
        $this->assertEquals($expected, $actual['selectSql']);
    }

    public function testFilterByWithRelation() {
        $expected = 'SELECT `zeus_instance`.*, `atlas_instance`.* FROM `zeus_instance` LEFT OUTER JOIN `atlas_instance` AS `atlas_instance` ON `zeus_instance`.`id` = `atlas_instance`.`zeus_instance_id` WHERE `atlas_instance`.`id` = \'1\';';
        $actual = ZeusInstance::read()
                    ->withRelation(AtlasInstance::Model, AtlasInstance::ZeusInstanceId)
                        ->filterBy(AtlasInstance::Id, 1)
                    ->toSql();
        $this->assertEquals($expected, $actual['selectSql']);
    }

    public function testOrderByAscending() {
        $expected = 'SELECT `atlas_instance`.*, `atlas_log`.* FROM `atlas_instance` LEFT OUTER JOIN `atlas_log` AS `atlas_log` ON `atlas_instance`.`id` = `atlas_log`.`atlas_instance_id` ORDER BY `atlas_instance`.`id`, `atlas_instance`.`status` ASC;';
        $actual = $this->object
                    ->withRelation(AtlasLog::Model, AtlasLog::AtlasInstanceId)
                    ->orderByAscending(AtlasInstance::Id, AtlasInstance::Status)
                    ->toSql();
        $this->assertEquals($expected, $actual['selectSql']);
    }

    public function testOrderByDescending() {
        $expected = 'SELECT `atlas_instance`.*, `atlas_log`.* FROM `atlas_instance` LEFT OUTER JOIN `atlas_log` AS `atlas_log` ON `atlas_instance`.`id` = `atlas_log`.`atlas_instance_id` ORDER BY `atlas_instance`.`id`, `atlas_instance`.`status` DESC;';
        $actual = $this->object
                    ->withRelation(AtlasLog::Model, AtlasLog::AtlasInstanceId)
                    ->orderByDescending(AtlasInstance::Id, AtlasInstance::Status)
                    ->toSql();
        $this->assertEquals($expected, $actual['selectSql']);
    }

    public function testOrderByBoth() {
        $expected = 'SELECT `atlas_instance`.*, `atlas_log`.* FROM `atlas_instance` LEFT OUTER JOIN `atlas_log` AS `atlas_log` ON `atlas_instance`.`id` = `atlas_log`.`atlas_instance_id` ORDER BY `atlas_instance`.`id` ASC, `atlas_instance`.`status` DESC;';
        $actual = $this->object
                    ->withRelation(AtlasLog::Model, AtlasLog::AtlasInstanceId)
                    ->orderByAscending(AtlasInstance::Id)
                    ->orderByDescending(AtlasInstance::Status)
                    ->toSql();
        $this->assertEquals($expected, $actual['selectSql']);
    }

    public function testCount() {
        $expected = 'SELECT COUNT(*) FROM `atlas_instance` LEFT OUTER JOIN `atlas_log` AS `atlas_log` ON `atlas_instance`.`id` = `atlas_log`.`atlas_instance_id`;';
        $actual = $this->object
                    ->withRelation(AtlasLog::Model, AtlasLog::AtlasInstanceId)
                    ->orderByAscending(AtlasInstance::Id)
                    ->orderByDescending(AtlasInstance::Status)
                    ->count()
                    ->toSql();
        $this->assertEquals($expected, $actual['countSql']);
    }

    public function testLimit() {
        $expected = 'SELECT `atlas_instance`.*, `atlas_log`.* FROM `atlas_instance` LEFT OUTER JOIN `atlas_log` AS `atlas_log` ON `atlas_instance`.`id` = `atlas_log`.`atlas_instance_id` ORDER BY `atlas_instance`.`id` ASC, `atlas_instance`.`status` DESC LIMIT 0, 100;';
        $actual = $this->object
                    ->withRelation(AtlasLog::Model, AtlasLog::AtlasInstanceId)
                    ->orderByAscending(AtlasInstance::Id)
                    ->orderByDescending(AtlasInstance::Status)
                    ->limit(0, 100)
                    ->toSql();
        $this->assertEquals($expected, $actual['selectSql']);
    }

    public function testSql() {
        $expected = 'SELECT * FROM `zeus_instance` WHERE `id` = 1;';
        $actual = ZeusInstance::read()
                    ->usingSql($expected)
                    ->toSql();
        $this->assertEquals($expected, $actual['selectSql']);
    }

    public function testSqlWithCount() {
        $expected = 'SELECT COUNT(*) FROM `zeus_instance` WHERE `id` = 1;';
        $actual = ZeusInstance::read()
                    ->usingSql('SELECT * FROM `zeus_instance` WHERE `id` = 1;')
                    ->count()
                    ->toSql(true);
        $this->assertEquals($expected, $actual['countSql']);
    }

    public function testLike() {
        $expected = 'SELECT `zeus_instance`.* FROM `zeus_instance` WHERE `zeus_instance`.`identifier` LIKE \'k%\';';
        $actual = ZeusInstance::read()
                    ->filterBy(ZeusInstance::Identifier, 'k%', Comparator::Like)
                    ->select()
                    ->toSql();
        $this->assertEquals($expected, $actual['selectSql']);
    }

    public function testCasInsensitive() {
        $expected = 'SELECT `zeus_instance`.* FROM `zeus_instance` WHERE LOWER(`zeus_instance`.`identifier`) != \'bacon\';';
        $actual = ZeusInstance::read()
                    ->filterBy(ZeusInstance::Identifier, 'bacon', Comparator::NotEqual, FilterByFlags::CaseInsensitive)
                    ->select()
                    ->toSql();
        $this->assertEquals($expected, $actual['selectSql']);
    }

    public function testMultipleInboundReferencesFromDuplicateTable() {
        $expected = 'SELECT `entity`.*, `note`.*, `note_1`.* FROM `entity` LEFT OUTER JOIN `note` AS `note` ON `entity`.`id` = `note`.`entity_id` LEFT OUTER JOIN `note` AS `note_1` ON `entity`.`id` = `note_1`.`author_entity_id` WHERE `entity`.`id` = \'1\';';
        $actual = Entity::read()
                    ->withRelation(Note::Model, Note::EntityId)
                        ->close()
                    ->withRelation(Note::Model, Note::AuthorEntityId)
                        ->close()
                    ->filterBy(Entity::Id, 1)
                    ->select()
                    ->toSql();
        $this->assertEquals($expected, $actual['selectSql']);
    }

    public function testMultipleOutboudReferencesFromDuplicateTable() {
        $expected = 'SELECT `note`.*, `entity`.*, `entity_1`.* FROM `note` LEFT OUTER JOIN `entity` AS `entity` ON `note`.`entity_id` = `entity`.`id` LEFT OUTER JOIN `entity` AS `entity_1` ON `note`.`author_entity_id` = `entity_1`.`id` WHERE `note`.`id` = \'1\';';
        $actual = Note::read()
                    ->withRelation(Entity::Model, Note::EntityId)
                        ->close()
                    ->withRelation(Entity::Model, Note::AuthorEntityId)
                        ->close()
                    ->filterBy(Note::Id, 1)
                    ->select()
                    ->toSql();
        $this->assertEquals($expected, $actual['selectSql']);
    }

    // get the parent
    public function testSelfReferentialJoinAsChild() {
        $expected = 'SELECT `post_comment`.*, `post_comment_1`.* FROM `post_comment` LEFT OUTER JOIN `post_comment` AS `post_comment_1` ON `post_comment`.`parent_post_comment_id` = `post_comment_1`.`id`;';
        $actual = PostComment::read()
                    ->withRelation(PostComment::Model, PostComment::ParentPostCommentId)
                    ->select()
                    ->toSql();
        $this->assertEquals($expected, $actual['selectSql']);
    }

    // get the child
    public function testSelfReferentialJoinAsParent() {
        $expected = 'SELECT `post_comment`.*, `post_comment_1`.* FROM `post_comment` LEFT OUTER JOIN `post_comment` AS `post_comment_1` ON `post_comment`.`id` = `post_comment_1`.`parent_post_comment_id`;';
        $actual = PostComment::read()
                    ->withRelation(PostComment::Model, PostComment::Id)
                    ->select()
                    ->toSql();
        $this->assertEquals($expected, $actual['selectSql']);
    }

    // gnarles barkley
    public function testMultipleChildJoinsBackToDifferentParentsOfTheSameType() {
        $expected = 'SELECT `post`.*, `user`.*, `post_favorite`.*, `user_1`.*, `post_rating`.*, `user_2`.* FROM `post` LEFT OUTER JOIN `user` AS `user` ON `post`.`user_id` = `user`.`id` LEFT OUTER JOIN `post_favorite` AS `post_favorite` ON `post`.`id` = `post_favorite`.`post_id` LEFT OUTER JOIN `post_rating` AS `post_rating` ON `post`.`id` = `post_rating`.`post_id` LEFT OUTER JOIN `user` AS `user_1` ON `post_favorite`.`user_id` = `user_1`.`id` LEFT OUTER JOIN `user` AS `user_2` ON `post_rating`.`user_id` = `user_2`.`id`;';
        $actual = Post::read()
                    ->withRelation(User::Model, Post::UserId)
                        ->close()
                    ->withRelation(PostFavorite::Model, PostFavorite::PostId)
                        ->withRelation(User::Model, PostFavorite::UserId)
                            ->close()
                        ->close()
                    ->withRelation(PostRating::Model, PostRating::PostId)
                        ->withRelation(User::Model, PostRating::UserId)
                            ->close()
                        ->close()
                    ->select()
                    ->toSql();
        $this->assertEquals($expected, $actual['selectSql']);
    }

    /*
    public function testMultipleChildJoinsBackToDifferentParentsOfTheSameTypeWithSelfReferentialJoinAsParent() {

    }
    */
}
?>
