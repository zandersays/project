<?php

/**
 *
 * @author Kam Sheffield
 * @version 08/05/2011
 */
class ModelListTest extends PHPUnit_Framework_TestCase {

    /**
     * @var ModelList
     */
    protected $object;

    /**
     *
     * @var DatabaseDriver
     */
    protected $databaseDriver;

    protected function setUp() {
        $this->object = new ModelList(AtlasInstance::Model);
    }

    protected function tearDown() {

    }

    public function testGetSize() {
        $this->assertEquals(0, $this->object->getSize());
    }

    public function testPush() {
        $this->assertEquals(0, $this->object->getSize());
        $model = new AtlasInstance();
        $model->setId(1);
        $this->object->push($model);
        $model = new AtlasInstance();
        $model->setId(2);
        $this->object->push($model);
        $model = new AtlasInstance();
        $model->setId(3);
        $this->object->push($model);
        $this->assertEquals($this->object->get(0)->getId(), 1);
        $this->assertEquals($this->object->get(1)->getId(), 2);
        $this->assertEquals($this->object->get(2)->getId(), 3);
        $this->assertEquals(3, $this->object->getSize());
    }

    public function testPushFront() {
        $this->assertEquals(0, $this->object->getSize());
        $model = new AtlasInstance();
        $model->setId(1);
        $this->object->pushFront($model);
        $model = new AtlasInstance();
        $model->setId(2);
        $this->object->pushFront($model);
        $model = new AtlasInstance();
        $model->setId(3);
        $this->object->pushFront($model);
        $this->assertEquals($this->object->get(0)->getId(), 3);
        $this->assertEquals($this->object->get(1)->getId(), 2);
        $this->assertEquals($this->object->get(2)->getId(), 1);
        $this->assertEquals(3, $this->object->getSize());
    }


    public function testGet() {
        $model = new AtlasInstance();
        $model->setId(1);
        $this->object->push($model);

        $model = new AtlasInstance();
        $model->setId(2);
        $this->object->push($model);

        $model = new AtlasInstance();
        $model->setId(3);
        $this->object->push($model);

        $this->assertEquals($this->object->get(0)->getId(), 1);
        $this->assertEquals($this->object->get(1)->getId(), 2);
        $this->assertEquals($this->object->get(2)->getId(), 3);
    }

    public function testPop() {
        $model = new AtlasInstance();
        $model->setId(1);
        $this->object->push($model);

        $model = new AtlasInstance();
        $model->setId(2);
        $this->object->push($model);

        $model = new AtlasInstance();
        $model->setId(3);
        $this->object->push($model);

        $model = $this->object->pop();
        $this->assertEquals($model->getId(), 3);
        $model = $this->object->pop();
        $this->assertEquals($model->getId(), 2);
        $model = $this->object->pop();
        $this->assertEquals($model->getId(), 1);
    }

    public function testPopFront() {
        $model = new AtlasInstance();
        $model->setId(1);
        $this->object->push($model);

        $model = new AtlasInstance();
        $model->setId(2);
        $this->object->push($model);

        $model = new AtlasInstance();
        $model->setId(3);
        $this->object->push($model);

        $model = $this->object->popFront();
        $this->assertEquals($model->getId(), 1);
        $model = $this->object->popFront();
        $this->assertEquals($model->getId(), 2);
        $model = $this->object->popFront();
        $this->assertEquals($model->getId(), 3);
    }

    public function testRemove() {
        $model = new AtlasInstance();
        $model->setId(1);
        $this->object->push($model);

        $model = new AtlasInstance();
        $model->setId(2);
        $this->object->push($model);

        $model = new AtlasInstance();
        $model->setId(3);
        $this->object->push($model);

        $model = $this->object->remove(1);
        $this->assertEquals($model->getId(), 2);
        $this->assertEquals($this->object->getSize(), 2);
    }

    public function testGetType() {
        $this->assertEquals($this->object->getType(), 'AtlasInstance');
    }

    public function testAsArray() {
        $model = new AtlasInstance();
        $model->setId(1);
        $this->object->push($model);

        $model = new AtlasInstance();
        $model->setId(2);
        $this->object->push($model);

        $model = new AtlasInstance();
        $model->setId(3);
        $this->object->push($model);

        foreach($this->object->asArray() as $model) {
            $model->setId(4);
        }

        foreach($this->object->asArray() as $model) {
            $this->assertEquals(4, $model->getId());
        }
    }

    public function testGetFirst() {
        $model = new AtlasInstance();
        $model->setId(1);
        $this->object->push($model);

        $model = new AtlasInstance();
        $model->setId(2);
        $this->object->push($model);

        $model = new AtlasInstance();
        $model->setId(3);
        $this->object->push($model);

        $this->assertEquals(1, $this->object->getFirst()->getId());
    }

    public function testGetLast() {
        $model = new AtlasInstance();
        $model->setId(1);
        $this->object->push($model);

        $model = new AtlasInstance();
        $model->setId(2);
        $this->object->push($model);

        $model = new AtlasInstance();
        $model->setId(3);
        $this->object->push($model);

        $this->assertEquals(3, $this->object->getLast()->getId());
    }
}

?>
