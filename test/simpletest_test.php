<?php
    // $Id$
    require_once(dirname(__FILE__) . '/../simpletest.php');
    
    class TestOfOptions extends UnitTestCase {

        function testMockBase() {
            $old_class = SimpleTest::getMockBaseClass();
            SimpleTest::setMockBaseClass('Fred');
            $this->assertEqual(SimpleTest::getMockBaseClass(), 'Fred');
            SimpleTest::setMockBaseClass($old_class);
        }
        
        function testStubBase() {
            $old_class = SimpleTest::getStubBaseClass();
            SimpleTest::setStubBaseClass('Fred');
            $this->assertEqual(SimpleTest::getStubBaseClass(), 'Fred');
            SimpleTest::setStubBaseClass($old_class);
        }
        
        function testIgnoreList() {
            $this->assertFalse(SimpleTest::isIgnored('ImaginaryTestCase'));
            SimpleTest::ignore('ImaginaryTestCase');
            $this->assertTrue(SimpleTest::isIgnored('ImaginaryTestCase'));
        }
    }
?>