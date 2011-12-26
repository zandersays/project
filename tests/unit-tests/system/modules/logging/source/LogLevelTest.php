<?php

/**
 * Description of LogLevelTest
 *
 * @author Kam Sheffield
 * @version 08/30/2011
 */
class LogLevelTest extends PHPUnit_Framework_TestCase {

    protected function setUp() {

    }

    protected function tearDown() {

    }

    public function testIsError() {
        $this->assertTrue(LogLevel::isError(LogLevel::Error));
        $this->assertFalse(LogLevel::isError(-1));
        $this->assertFalse(LogLevel::isError(1));
    }

    public function testIsWarning() {
        $this->assertTrue(LogLevel::isWarning(LogLevel::Warning));
        $this->assertFalse(LogLevel::isWarning(-1));
        $this->assertFalse(LogLevel::isWarning(2));
    }

    public function testIsInformation() {
        $this->assertTrue(LogLevel::isInformation(LogLevel::Information));
        $this->assertFalse(LogLevel::isWarning(-1));
        $this->assertFalse(LogLevel::isWarning(3));
    }

    public function testIsVerbose() {
        $this->assertTrue(LogLevel::isVerbose(LogLevel::Verbose));
        $this->assertFalse(LogLevel::isWarning(-1));
        $this->assertFalse(LogLevel::isWarning(2));
    }
}

?>
