<?php
// $Id$

// NOTE:
// Some of these tests are designed to fail! Do not be alarmed.
//                         ----------------

// The following tests are a bit hacky. Whilst Kent Beck tried to
// build a unit tester with a unit tester, I am not that brave.
// Instead I have just hacked together odd test scripts until
// I have enough of a tester to proceed more formally.
//
// The proper tests start in all_tests.php
require_once(dirname(__FILE__) . '/../unit_tester.php');
require_once(dirname(__FILE__) . '/../shell_tester.php');
require_once(dirname(__FILE__) . '/../mock_objects.php');
require_once(dirname(__FILE__) . '/../reporter.php');
require_once(dirname(__FILE__) . '/../xml.php');
require_once(dirname(__FILE__) . '/../autorun.php');


class TestDisplayClass {
    private $a;

    function __construct($a) {
        $this->a = $a;
    }
}

class PassingUnitTestCaseOutput extends UnitTestCase {

    function testOfResults() {
        $this->pass('Pass');
    }

    function testTrue() {
        $this->assertTrue(true);
    }

    function testFalse() {
        $this->assertFalse(false);
    }

    function testExpectation() {
        $expectation = new EqualExpectation(25, 'My expectation message: %s');
        $this->assert($expectation, 25, 'My assert message : %s');
    }

    function testNull() {
        $this->assertNull(null, "%s -> Pass");
        $this->assertNotNull(false, "%s -> Pass");
    }

    function testType() {
        $this->assertIsA("hello", "string", "%s -> Pass");
        $this->assertIsA($this, "PassingUnitTestCaseOutput", "%s -> Pass");
        $this->assertIsA($this, "UnitTestCase", "%s -> Pass");
    }

    function testTypeEquality() {
        $this->assertEqual("0", 0, "%s -> Pass");
    }

    function testNullEquality() {
        $this->assertNotEqual(null, 1, "%s -> Pass");
        $this->assertNotEqual(1, null, "%s -> Pass");
    }

    function testIntegerEquality() {
        $this->assertNotEqual(1, 2, "%s -> Pass");
    }

    function testStringEquality() {
        $this->assertEqual("a", "a", "%s -> Pass");
        $this->assertNotEqual("aa", "ab", "%s -> Pass");
    }

    function testHashEquality() {
        $this->assertEqual(array("a" => "A", "b" => "B"), array("b" => "B", "a" => "A"), "%s -> Pass");
    }

    function testWithin() {
        $this->assertWithinMargin(5, 5.4, 0.5, "%s -> Pass");
    }

    function testOutside() {
        $this->assertOutsideMargin(5, 5.6, 0.5, "%s -> Pass");
    }

    function testStringIdentity() {
        $a = "fred";
        $b = $a;
        $this->assertIdentical($a, $b, "%s -> Pass");
    }

    function testTypeIdentity() {
        $a = "0";
        $b = 0;
        $this->assertNotIdentical($a, $b, "%s -> Pass");
    }

    function testNullIdentity() {
        $this->assertNotIdentical(null, 1, "%s -> Pass");
        $this->assertNotIdentical(1, null, "%s -> Pass");
    }

    function testHashIdentity() {
    }

    function testObjectEquality() {
        $this->assertEqual(new TestDisplayClass(4), new TestDisplayClass(4), "%s -> Pass");
        $this->assertNotEqual(new TestDisplayClass(4), new TestDisplayClass(5), "%s -> Pass");
    }

    function testObjectIndentity() {
        $this->assertIdentical(new TestDisplayClass(false), new TestDisplayClass(false), "%s -> Pass");
        $this->assertNotIdentical(new TestDisplayClass(false), new TestDisplayClass(0), "%s -> Pass");
    }

    function testReference() {
        $a = "fred";
        $b = &$a;
        $this->assertReference($a, $b, "%s -> Pass");
    }

    function testReferenceFail1() {
        $a = "fred";
        $b = $a;
        $this->expectFail()->assertReference($a, $b, "%s -> Fail");
    }

    function testReferenceFail2() {
        $a = "fred";
        $b = "fred";
        $this->expectFail()->assertReference($a, $b, "%s -> Fail");
    }

    function testReferenceFail3() {
        $a = "fred";
        $b = $a . '';
        $this->expectFail()->assertReference($a, $b, "%s -> Fail");
    }

    function testCloneOnSameObjects() {
        $a = "fred";
        $b = $a;
        $this->expectFail()->assertClone($a, $b, "%s -> Fail");
    }

    function testCloneOnSameObjects2() {
        $a = "fred";
        $b = "fred";
        $this->expectFail()->assertClone($a, $b, "%s -> Fail");
    }

    function testCloneOnSameObjects3() {
        $a = "fred";
        $b = $a . '';
        $this->expectFail()->assertClone($a, $b, "%s -> Fail");
    }

    function testCloneOnSameObjects4() {
        $a = "fred";
        $b = &$a;
        $this->expectFail()->assertClone($a, $b, "%s -> Fail");
    }

    function testCloneOnDifferentObjects() {
        $a = "fred";
        $b = substr(' ' . $a, 1);
        $this->expectFail()->assertClone($a, $b, "%s -> Fail");
    }

    function testCloneOnClonedUserObjects() {
        $a = new TestDisplayClass("fred");
        $b = clone $a;
        $this->assertClone($a, $b, "%s -> Pass");
    }

    function testCloneOnAssignedUserObjects() {
        $a = new TestDisplayClass("fred");
        $b = $a;
        $this->expectFail()->assertClone($a, $b, "%s -> Fail");
    }

    function testCloneOnReferencedUserObjects() {
        $a = new TestDisplayClass("fred");
        $b = &$a;
        $this->expectFail()->assertClone($a, $b, "%s -> Fail");
    }

    function testPatterns() {
        $this->assertPattern('/hello/i', "Hello there", "%s -> Pass");
        $this->assertNoPattern('/hello/', "Hello there", "%s -> Pass");
    }

    function testLongStrings() {
        $text = "";
        for ($i = 0; $i < 10; $i++) {
            $text .= "0123456789";
        }
        $this->assertEqual($text, $text);
    }
}

class FailingUnitTestCaseOutput extends UnitTestCase {

    function testOfResults() {
        $this->fail('Fail');        
    }

    function testTrue() {
        $this->expectFail()->assertTrue(false);        // Fail.
    }

    function testFalse() {
        $this->expectFail()->assertFalse(true);        // Fail.
    }

    function testExpectation() {
        $expectation = new EqualExpectation(25, 'My expectation message: %s');
        $this->expectFail()->assert($expectation, 24, 'My assert message : %s');        // Fail.
    }

    function testNull() {
        $this->expectFail()->assertNull(false, "%s -> Fail");        
        $this->expectFail()->assertNotNull(null, "%s -> Fail");        
    }

    function testType() {
        $this->expectFail()->assertIsA(14, "string", "%s -> Fail");        
        $this->expectFail()->assertIsA(14, "TestOfUnitTestCaseOutput", "%s -> Fail");        
        $this->expectFail()->assertIsA($this, "TestReporter", "%s -> Fail");        
    }

    function testTypeEquality() {
        $this->expectFail()->assertNotEqual("0", 0, "%s -> Fail");        
    }

    function testNullEquality() {
        $this->expectFail()->assertEqual(null, 1, "%s -> Fail");        
        $this->expectFail()->assertEqual(1, null, "%s -> Fail");        
    }

    function testIntegerEquality() {
        $this->expectFail()->assertEqual(1, 2, "%s -> Fail");        
    }

    function testStringEquality() {
        $this->expectFail()->assertNotEqual("a", "a", "%s -> Fail");    
        $this->expectFail()->assertEqual("aa", "ab", "%s -> Fail");        
    }

    function testHashEquality() {
        $this->expectFail()->assertEqual(array("a" => "A", "b" => "B"), array("b" => "B", "a" => "Z"), "%s -> Fail");
    }

    function testWithin() {
        $this->expectFail()->assertWithinMargin(5, 5.6, 0.5, "%s -> Fail");   
    }

    function testOutside() {
        $this->expectFail()->assertOutsideMargin(5, 5.4, 0.5, "%s -> Fail");   
    }

    function testStringIdentity() {
        $a = "fred";
        $b = $a;
        $this->expectFail()->assertNotIdentical($a, $b, "%s -> Fail");       
    }

    function testTypeIdentity() {
        $a = "0";
        $b = 0;
        $this->expectFail()->assertIdentical($a, $b, "%s -> Fail");        
    }

    function testNullIdentity() {
        $this->expectFail()->assertIdentical(null, 1, "%s -> Fail");        
        $this->expectFail()->assertIdentical(1, null, "%s -> Fail");        
    }

    function testHashIdentity() {
        $this->expectFail()->assertIdentical(array("a" => "A", "b" => "B"), array("b" => "B", "a" => "A"), "%s -> Fail");        
    }

    function testObjectEquality() {
        $this->expectFail()->assertNotEqual(new TestDisplayClass(4), new TestDisplayClass(4), "%s -> Fail");    
        $this->expectFail()->assertEqual(new TestDisplayClass(4), new TestDisplayClass(5), "%s -> Fail");        
    }

    function testObjectIndentity() {
        $this->expectFail()->assertNotIdentical(new TestDisplayClass(false), new TestDisplayClass(false), "%s -> Fail");    
        $this->expectFail()->assertIdentical(new TestDisplayClass(false), new TestDisplayClass(0), "%s -> Fail");        
    }

    function testReference() {
        $a = "fred";
        $b = &$a;
        $this->expectFail()->assertClone($a, $b, "%s -> Fail");        
    }

    function testCloneOnDifferentObjects() {
        $a = "fred";
        $b = $a;
        $c = "Hello";
        $this->expectFail()->assertClone($a, $c, "%s -> Fail");        
    }

    function testPatterns() {
        $this->expectFail()->assertPattern('/hello/', "Hello there", "%s -> Fail");            
        $this->expectFail()->assertNoPattern('/hello/i', "Hello there", "%s -> Fail");      
    }

    function testLongStrings() {
        $text = "";
        for ($i = 0; $i < 10; $i++) {
            $text .= "0123456789";
        }
        $this->expectFail()->assertEqual($text . $text, $text . "a" . $text);        // Fail.
    }
}

class Dummy4VisualTest {
    function __construct() {
    }

    function a() {
    }
}
Mock::generate('Dummy4VisualTest');

class TestOfMockObjectsOutput extends UnitTestCase {

    function testCallCounts() {
        $dummy = new MockDummy4VisualTest();
        $dummy->expectCallCount('a', 2, 'My message: %s');
        $dummy->a();
        $dummy->a();
    }

    function testMinimumCallCounts() {
        $dummy = new MockDummy4VisualTest();
        $dummy->expectMinimumCallCount('a', 1, 'My message: %s');
        $dummy->a();
        $dummy->a();
    }

    function testMaximumCallCounts() {
        $dummy = new MockDummy4VisualTest();
        $dummy->expectMaximumCallCount('a', 2, 'My message: %s');
        $dummy->a();
        $dummy->a();
    }

    function testMaximumCallCounts2() {
        $dummy = new MockDummy4VisualTest();
        $dummy->expectMaximumCallCount('a', 3, 'My message: %s');
        $dummy->a();
        $dummy->a();
    }

    function testEmptyMatching() {
        $dummy = new MockDummy4VisualTest();
        $dummy->expect('a', array());
        $dummy->a();
        $dummy->a(null);        // Fail.
    }

    function testEmptyMatchingWithCustomMessage() {
        $dummy = new MockDummy4VisualTest();
        $dummy->expect('a', array(), 'My expectation message: %s');
        $dummy->a();
        $dummy->a(null);        // Fail.
    }

    function testNullMatching() {
        $dummy = new MockDummy4VisualTest();
        $dummy->expect('a', array(null));
        $dummy->a(null);
        $dummy->a();        // Fail.
    }

    function testBooleanMatching() {
        $dummy = new MockDummy4VisualTest();
        $dummy->expect('a', array(true, false));
        $dummy->a(true, false);
        $dummy->a(true, true);        // Fail.
    }

    function testIntegerMatching() {
        $dummy = new MockDummy4VisualTest();
        $dummy->expect('a', array(32, 33));
        $dummy->a(32, 33);
        $dummy->a(32, 34);        // Fail.
    }

    function testFloatMatching() {
        $dummy = new MockDummy4VisualTest();
        $dummy->expect('a', array(3.2, 3.3));
        $dummy->a(3.2, 3.3);
        $dummy->a(3.2, 3.4);        // Fail.
    }

    function testStringMatching() {
        $dummy = new MockDummy4VisualTest();
        $dummy->expect('a', array('32', '33'));
        $dummy->a('32', '33');
        $dummy->a('32', '34');        // Fail.
    }

    function testEmptyMatchingWithCustomExpectationMessage() {
        $dummy = new MockDummy4VisualTest();
        $dummy->expect(
                'a',
                array(new EqualExpectation('A', 'My part expectation message: %s')),
                'My expectation message: %s');
        $dummy->a('A');
        $dummy->a('B');        // Fail.
    }

    function testArrayMatching() {
        $dummy = new MockDummy4VisualTest();
        $dummy->expect('a', array(array(32), array(33)));
        $dummy->a(array(32), array(33));
        $dummy->a(array(32), array('33'));        // Fail.
    }

    function testObjectMatching() {
        $a = new Dummy4VisualTest();
        $a->a = 'a';
        $b = new Dummy4VisualTest();
        $b->b = 'b';
        $dummy = new MockDummy4VisualTest();
        $dummy->expect('a', array($a, $b));
        $dummy->a($a, $b);
        $dummy->a($a, $a);        // Fail.
    }

    function testBigList() {
        $dummy = new MockDummy4VisualTest();
        $dummy->expect('a', array(false, 0, 1, 1.0));
        $dummy->a(false, 0, 1, 1.0);
        $dummy->a(true, false, 2, 2.0);        // Fail.
    }
}

class TestOfPastBugs extends UnitTestCase {

    function testMixedTypes() {
        $this->assertEqual(array(), null, "%s -> Pass");
        $this->expectFail()->assertIdentical(array(), null, "%s -> Fail");    
    }

    function testMockWildcards() {
        $dummy = new MockDummy4VisualTest();
        $dummy->expect('a', array('*', array(33)));
        $dummy->a(array(32), array(33));
        $dummy->a(array(32), array('33'));        // Fail.
    }
}

class TestOfVisualShell extends ShellTestCase {

    function testDump() {
        $this->execute('ls');
        $this->dumpOutput();
        $this->execute('dir');
        $this->dumpOutput();
    }

    function testDumpOfList() {
        $this->execute('ls');
        $this->dump($this->getOutputAsList());
    }
}

class TestOfSkippingNoMatterWhat extends UnitTestCase {
    function skip() {
        $this->skipIf(true, 'Always skipped -> %s');
    }

    function testFail() {
        $this->fail('This really shouldn\'t have happened');
    }
}

class TestOfSkippingOrElse extends UnitTestCase {
    function skip() {
        $this->skipUnless(false, 'Always skipped -> %s');
    }

    function testFail() {
        $this->fail('This really shouldn\'t have happened');
    }
}

class TestOfSkippingTwiceOver extends UnitTestCase {
    function skip() {
        $this->skipIf(true, 'First reason -> %s');
        $this->skipIf(true, 'Second reason -> %s');
    }

    function testFail() {
        $this->fail('This really shouldn\'t have happened');
    }
}

class TestThatShouldNotBeSkipped extends UnitTestCase {
    function skip() {
        $this->skipIf(false);
        $this->skipUnless(true);
    }

    function testFail() {
        $this->fail('We should see this message');
    }

    function testPass() {
        $this->pass('We should see this message');
    }
}




class AllVisualTests extends TestSuite {
    function __construct() {
		parent::__construct('Visual test with 46 passes, 47 fails and 0 exceptions');
		$this->add(new PassingUnitTestCaseOutput());
		$this->add(new FailingUnitTestCaseOutput());
		$this->add(new TestOfMockObjectsOutput());
		$this->add(new TestOfPastBugs());
		$this->add(new TestOfVisualShell());
		$this->add(new TestOfSkippingNoMatterWhat());
		$this->add(new TestOfSkippingOrElse());
		$this->add(new TestOfSkippingTwiceOver());
		$this->add(new TestThatShouldNotBeSkipped());
	}
}

