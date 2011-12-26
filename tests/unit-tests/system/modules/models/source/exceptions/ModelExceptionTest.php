<?php

/**
 *
 * @author Kam Sheffield
 * @version 08/20/2011
 */
class ModelExceptionTest extends PHPUnit_Framework_TestCase {

    /**
     * @var ModelException
     */
    protected $object;

    /**
     *
     * @var string
     */
    protected $message;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $this->message = 'this is my message';
        $this->object = new ModelException($this->message);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {

    }

    public function testTryCatch() {
        $exceptionCaught = false;
        try {
            throw $this->object;
        }
        catch(Exception $e) {
            $exceptionCaught = true;
        }
        $this->assertEquals($exceptionCaught, true);
        $this->assertEquals($this->message, $this->object->getMessage());
    }
}
?>
