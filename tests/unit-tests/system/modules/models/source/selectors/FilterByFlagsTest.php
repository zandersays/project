<?php

/**
 * Description of ModelSelectorFlagsTest
 *
 * @author Kam Sheffield
 * @version 08/20/2011
 */
class FilterByFlagsTest extends PHPUnit_Framework_TestCase {

    protected function setUp() {

    }

    protected function tearDown() {

    }

    public function testIsCaseInsensitive() {
        $this->assertTrue(FilterByFlags::isCaseInsensitive(FilterByFlags::CaseInsensitive));
        $this->assertFalse(FilterByFlags::isCaseInsensitive(0));
    }
}

?>
