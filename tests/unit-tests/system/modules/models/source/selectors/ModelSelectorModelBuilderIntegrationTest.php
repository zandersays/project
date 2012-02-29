<?php

/**
 * This test looks at the output from the chain:
 * ModelSelector -> ModelDriver -> ModelBuilder
 * and programatically verifieds the correct behavior.
 *
 * @author Kam Sheffield
 * @version 09/13/2011
 */
class ModelSelectorModelBuilderIntegrationTest extends PHPUnit_Framework_TestCase {

    /**
     *
     * @var DatabaseDriver
     */
    protected $databaseDriver;

    protected function setUp() {
        // set up the database
        $this->databaseDriver = new DatabaseDriverMySql('project_unit_test', 'localhost', 'test', 'password');
        ModelContext::setGlobalContext($this->databaseDriver);
        Database::addDatabaseDriver($this->databaseDriver);
    }

    protected function tearDown() {
        $this->databaseDriver->closeConnection();
        Database::removeDatabaseDriver($this->databaseDriver->getId());
    }

    public function testBasic() {
        $model = User::read()
                    ->filterBy(User::Id, 1)
                    ->select()
                    ->execute()
                    ->getFirst();

        $this->assertInstanceOf('User', $model);
    }

    public function testWithRelation() {
        $model = User::read()
                    ->filterBy(User::Id, 1)
                    ->withRelation(Post::Model, Post::UserId)
                    ->select()
                    ->execute()
                    ->getFirst();

        $this->assertInstanceOf('User', $model);

        $count = Database::query('SELECT COUNT(*) FROM `post` WHERE `user_id` = 1;');
        $count = $count[0]['COUNT(*)'];
        $this->assertEquals($count, $model->getRelatedModelList(Post::UserId)->getSize());
    }

    public function testWithNullRelation() {
        $model = User::read()
                    ->filterBy(User::Id, 3)
                    ->withRelation(PostComment::Model, PostComment::UserId)
                    ->select()
                    ->execute()
                    ->getFirst();

        $this->assertInstanceOf('User', $model);

        $count = Database::query('SELECT COUNT(*) FROM `post_comment` WHERE `user_id` = 3');
        $count = $count[0]['COUNT(*)'];
        $this->assertEquals($count, $model->getRelatedModelList(PostComment::UserId)->getSize());
    }

    public function testWithNestedRelations() {
        $model = User::read()
                    ->filterBy(User::Id, 1)
                    ->withRelation(Post::Model, Post::UserId)
                        ->withRelation(PostComment::Model, PostComment::PostId)
                            ->close()
                        ->withRelation(PostRating::Model, PostRating::PostId)
                            ->close()
                        ->withRelation(PostFavorite::Model, PostFavorite::PostId)
                            ->close()
                    ->select()
                    ->execute()
                    ->getFirst();

        $this->assertInstanceOf('User', $model);

        $count = Database::query('SELECT COUNT(*) FROM `post` WHERE `user_id` = 1');
        $count = $count[0]['COUNT(*)'];
        $this->assertEquals($count, $model->getRelatedModelList(Post::UserId)->getSize());
        $posts = $model->getRelatedModelList(Post::UserId);

        foreach($posts->asArray() as $post) {
            $count = Database::query('SELECT COUNT(*) FROM `post_comment` WHERE `post_id` = '.$post->getId());
            $count = $count[0]['COUNT(*)'];
            $this->assertEquals($count, $post->getRelatedModelList(PostComment::PostId)->getSize());
            if($count > 0) {
                $this->assertInstanceOf('PostComment', $post->getRelatedModelList(PostComment::PostId)->getFirst());
            }

            $count = Database::query('SELECT COUNT(*) FROM `post_rating` WHERE `post_id` = '.$post->getId());
            $count = $count[0]['COUNT(*)'];
            $this->assertEquals($count, $post->getRelatedModelList(PostRating::PostId)->getSize());
            if($count > 0) {
                $this->assertInstanceOf('PostRating', $post->getRelatedModelList(PostRating::PostId)->getFirst());
            }

            $count = Database::query('SELECT COUNT(*) FROM `post_favorite` WHERE `post_id` = '.$post->getId());
            $count = $count[0]['COUNT(*)'];
            $this->assertEquals($count, $post->getRelatedModelList(PostFavorite::PostId)->getSize());
            if($count > 0) {
                $this->assertInstanceOf('PostFavorite', $post->getRelatedModelList(PostFavorite::PostId)->getFirst());
            }
        }
    }

    public function testWithCount() {
        $modelSelectorResults = User::read()
                                    ->count()
                                    ->select()
                                    ->execute();
        $count = Database::query('SELECT COUNT(*) FROM `user`');
        $count = $count[0]['COUNT(*)'];
        $this->assertEquals($count, $modelSelectorResults->getCount());

        $this->assertInstanceOf('User', $modelSelectorResults->getFirst());
    }

    public function testUsingSql() {
        $modelSelectorResults = User::read()
                                    ->usingSql('SELECT * FROM `user`')
                                    ->count()
                                    ->select()
                                    ->execute();
        $count = Database::query('SELECT COUNT(*) FROM `user`');
        $count = $count[0]['COUNT(*)'];
        $this->assertEquals($count, $modelSelectorResults->getCount());

        $this->assertInstanceOf('User', $modelSelectorResults->getFirst());
    }

    public function testMultipleInboundReferencesFromDuplicateTable() {
        $modelSelectorResults = Entity::read()
                                    ->withRelation(Note::Model, Note::EntityId)
                                        ->close()
                                    ->withRelation(Note::Model, Note::AuthorEntityId)
                                        ->close()
                                    ->filterBy(Entity::Id, 1)
                                    ->select()
                                    ->execute();

        // check the base
        $this->assertEquals($modelSelectorResults->getCount(), 1);
        $this->assertInstanceOf('Entity', $modelSelectorResults->getFirst());
        $entity = $modelSelectorResults->getFirst();

        // check the first child
        $count = Database::query('SELECT COUNT(*) FROM `note` WHERE `entity_id` = 1;');
        $count = $count[0]['COUNT(*)'];
        $this->assertEquals($count, $entity->getRelatedModelList(Note::EntityId)->getSize());

        $notes = $entity->getRelatedModelList(Note::EntityId);
        $this->assertInstanceOf('Note', $notes->get(0));
        $this->assertInstanceOf('Note', $notes->get(1));

        // check the second child
        $count = Database::query('SELECT COUNT(*) FROM `note` WHERE `author_entity_id` = 1;');
        $count = $count[0]['COUNT(*)'];
        $this->assertEquals($count, $entity->getRelatedModelList(Note::AuthorEntityId)->getSize());

        $notes = $entity->getRelatedModelList(Note::AuthorEntityId);
        $this->assertInstanceOf('Note', $notes->get(0));
        $this->assertInstanceOf('Note', $notes->get(1));
    }

    public function testMultipleOutboudReferencesFromDuplicateTable() {
        $modelSelectorResults = Note::read()
                                    ->withRelation(Entity::Model, Note::EntityId)
                                        ->close()
                                    ->withRelation(Entity::Model, Note::AuthorEntityId)
                                        ->close()
                                    ->filterBy(Note::Id, 1)
                                    ->select()
                                    ->execute();

        // check the base
        $this->assertEquals($modelSelectorResults->getCount(), 1);
        $this->assertInstanceOf('Note', $modelSelectorResults->getFirst());
        $note = $modelSelectorResults->getFirst();

        // check the first child
        $this->assertEquals(1, $note->getRelatedModelList(Note::EntityId)->getSize());

        $entity = $note->getRelatedModelList(Note::EntityId);
        $this->assertInstanceOf('Entity', $entity->get(0));

        // check the second child
        $this->assertEquals(1, $note->getRelatedModelList(Note::AuthorEntityId)->getSize());

        $entity = $note->getRelatedModelList(Note::AuthorEntityId);
        $this->assertInstanceOf('Entity', $entity->get(0));
    }

    // get the parent
    public function testSelfReferentialJoinAsChild() {
        $modelSelectorResults = PostComment::read()
                                    ->filterBy(PostComment::Id, 3)
                                    ->withRelation(PostComment::Model, PostComment::ParentPostCommentId)
                                    ->select()
                                    ->execute();

        $this->assertEquals(1, $modelSelectorResults->getCount());
        $this->assertInstanceOf('PostComment', $modelSelectorResults->getFirst());
        $postComment = $modelSelectorResults->getFirst();

        $this->assertEquals(1, $postComment->getRelatedModelList(PostComment::ParentPostCommentId)->getSize());
        $this->assertInstanceOf('PostComment', $postComment->getRelatedModelList(PostComment::ParentPostCommentId)->getFirst());
    }

    // get the child
    public function testSelfReferentialJoinAsParent() {
        $modelSelectorResults = PostComment::read()
                                    ->filterBy(PostComment::Id, 1)
                                    ->withRelation(PostComment::Model, PostComment::Id)
                                    ->select()
                                    ->execute();

        $this->assertEquals(1, $modelSelectorResults->getCount());
        $this->assertInstanceOf('PostComment', $modelSelectorResults->getFirst());
        $postComment = $modelSelectorResults->getFirst();

        $this->assertEquals(2, $postComment->getRelatedModelList(PostComment::Id)->getSize());
        $this->assertInstanceOf('PostComment', $postComment->getRelatedModelList(PostComment::Id)->getFirst());
    }

    // gnarles barkley
    public function testMultipleChildJoinsBackToDifferentParentsOfTheSameType() {
        $modelSelectorResults = Post::read()
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
                                    ->filterBy(Post::Id, 1)
                                    ->select()
                                    ->execute();

        $this->assertEquals($modelSelectorResults->getCount(), 1);
        $this->assertInstanceOf('Post', $modelSelectorResults->getFirst());
        $post = $modelSelectorResults->getFirst();

        $this->assertEquals($post->getRelatedModelList(Post::UserId)->getSize(), 1);
        $this->assertInstanceOf('User', $post->getRelatedModelList(Post::UserId)->getFirst());

        $count = Database::query('SELECT COUNT(*) FROM `post_favorite` WHERE `post_id` = 1');
        $count = $count[0]['COUNT(*)'];
        $this->assertEquals($count, $post->getRelatedModelList(PostFavorite::PostId)->getSize());
        $this->assertInstanceOf('PostFavorite', $post->getRelatedModelList(PostFavorite::PostId)->getFirst());

        $postFavorites = $post->getRelatedModelList(PostFavorite::PostId);
        foreach($postFavorites->asArray() as $postFavorite) {
            $this->assertEquals(1, $postFavorite->getRelatedModelList(PostFavorite::UserId)->getSize());
            $this->assertInstanceOf('User', $postFavorite->getRelatedModelList(PostFavorite::UserId)->getFirst());
        }

        $count = Database::query('SELECT COUNT(*) FROM `post_rating` WHERE `post_id` = 1');
        $count = $count[0]['COUNT(*)'];
        $this->assertEquals($count, $post->getRelatedModelList(PostRating::PostId)->getSize());
        $this->assertInstanceOf('PostRating', $post->getRelatedModelList(PostRating::PostId)->getFirst());

        $postRatings = $post->getRelatedModelList(PostRating::PostId);
        foreach($postRatings->asArray() as $postRating) {
            $this->assertEquals(1, $postRating->getRelatedModelList(PostRating::UserId)->getSize());
            $this->assertInstanceOf('User', $postRating->getRelatedModelList(PostRating::UserId)->getFirst());
        }
    }
}

?>
