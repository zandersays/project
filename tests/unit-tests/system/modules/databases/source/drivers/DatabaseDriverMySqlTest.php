<?php

class DatabaseDriverMySqlTest extends PHPUnit_Framework_TestCase {

    /**
     * @var DatabaseDriverMySql
     */
    protected $object;

    protected function setUp() {
        $this->object = new DatabaseDriverMySql('atlasds', 'localhost', 'test', 'password');
    }

    protected function tearDown() {
        if($this->object->isConnected()) {
            $this->object->closeConnection();
        }
    }

    public function testGetSocket() {
        $this->assertNull($this->object->getSocket());
    }

    public function testInstanceWithSocket() {
        // instance the object with a socket connection instead
        $this->object = new DatabaseDriverMySql('atlasds', 'localhost', 'test', 'password', '3306', '/var/run/mysqld/mysqld.sock');

        // run all of the tests
        $this->assertEquals('/var/run/mysqld/mysqld.sock', $this->object->getSocket());
    }

    // TODO make this test better
}

?>
