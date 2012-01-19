<?php
require_once(dirname(__FILE__) . '/../autorun.php');
require_once(dirname(__FILE__) . '/../errors.php');
require_once(dirname(__FILE__) . '/../expectation.php');
require_once(dirname(__FILE__) . '/../test_case.php');
Mock::generate('SimpleTestCase');
Mock::generate('SimpleExpectation');
SimpleTest::ignore('MockSimpleTestCase');

class TestOfErrorQueue extends UnitTestCase {

    function setUp() {
        $context = SimpleTest::getContext();
        $queue = $context->get('SimpleErrorQueue');
        $queue->clear();
    }

    function tearDown() {
        $context = SimpleTest::getContext();
        $queue = $context->get('SimpleErrorQueue');
        $queue->clear();
    }

    function testExpectationMatchCancelsIncomingError() {
        $test = new MockSimpleTestCase();
        $test->expectOnce('assert', array(
                new IdenticalExpectation(new AnythingExpectation()),
                'B',
                'a message'));
        $test->setReturnValue('assert', true);
        $test->expectNever('error');
        $queue = new SimpleErrorQueue();
        $queue->setTestCase($test);
        $queue->expectError(new AnythingExpectation(), 'a message');
        $queue->add(1024, 'B', 'b.php', 100);
        $queue->tally();
    }
}

SimpleTest::ignore('TestOfErrorTrapQueueBug');
class TestOfErrorTrapQueueBug extends UnitTestCase 
{
    function testAnyFalseyErrorCanBeSwallowed() 
	{
		/*
			offical release & CVS: 
			
			it would barf on this, because SimpleTest created a TrueExpectation() instance 
			instead of an AnythingExpectation() for an empty expect argument, but then 
			an AnythingExpectation() would have broken the broken assert logic in the tally() 
			as well (an AnythingExpectation() being TRUEish all the time), so it's
			twice bitten for the price of one there, I guess...
			
			Correct code must catch this falsey trigger_error() bugger and hence 'pass'.
		*/
        $this->expectError();
		trigger_error(false);
    }
}

class TestOfErrorTrap extends UnitTestCase {
    private $old;

    function setUp() {
        $this->old = error_reporting(E_ALL);
        set_error_handler('SimpleTestErrorHandler');
    }

    function tearDown() {
        restore_error_handler();
        error_reporting($this->old);
    }

    function testQueueStartsEmpty() {
        $context = SimpleTest::getContext();
        $queue = $context->get('SimpleErrorQueue');
        $this->assertFalse($queue->extractExpectation());
    }

    function testErrorsAreSwallowedByMatchingExpectation() {
        $this->expectError('Ouch!');
        trigger_error('Ouch!');
    }

    function testErrorsAreSwallowedInOrder() {
        $this->expectError('a');
        $this->expectError('b');
        trigger_error('a');
        trigger_error('b');
    }

    function testAnyErrorCanBeSwallowed() {
        $this->expectError();
        trigger_error('Ouch!');
    }

    function testAnyFalseyErrorCanBeSwallowed() {
		$test = new TestOfErrorTrapQueueBug();
		$this->assertTrue($test->run(new SimpleReporter()));
		$reporter = $test->getReporter(); // get the inner test's reporter, as /this/ test level should be using another one again already!
		$this->assertEqual($reporter->getTestCaseCount(), 1, "%s -> Fail TestCaseCount");
		$this->assertEqual($reporter->getTestCaseProgress(), 1, "%s -> Fail TestCaseProgress");
		$this->assertEqual($reporter->getPassCount(), 1, "%s -> Fail getPassCount");
		$this->assertEqual($reporter->getFailCount(), 0, "%s -> Fail getFailCount");
		$this->assertEqual($reporter->getExceptionCount(), 0, "%s -> Fail getExceptionCount");
    }

    function testErrorCanBeSwallowedByPatternMatching() {
        $this->expectError(new PatternExpectation('/ouch/i'));
        trigger_error('Ouch!');
    }

    function testErrorWithPercentsPassesWithNoSprintfError() {
        $this->expectError("%");
        trigger_error('%');
    }
}

class TestOfErrors extends UnitTestCase {
    private $old;

    function setUp() {
        $this->old = error_reporting(E_ALL);
    }

    function tearDown() {
        error_reporting($this->old);
    }

    function testDefaultWhenAllReported() {
        error_reporting(E_ALL);
        $this->expectError('Ouch!');
        trigger_error('Ouch!');
    }

    function testNoticeWhenReported() {
        error_reporting(E_ALL);
        $this->expectError('Ouch!');
        trigger_error('Ouch!', E_USER_NOTICE);
    }

    function testWarningWhenReported() {
        error_reporting(E_ALL);
        $this->expectError('Ouch!');
        trigger_error('Ouch!', E_USER_WARNING);
    }

    function testErrorWhenReported() {
        error_reporting(E_ALL);
        $this->expectError('Ouch!');
        trigger_error('Ouch!', E_USER_ERROR);
    }

    function testNoNoticeWhenNotReported() {
        error_reporting(0);
        trigger_error('Ouch!', E_USER_NOTICE);
    }

    function testNoWarningWhenNotReported() {
        error_reporting(0);
        trigger_error('Ouch!', E_USER_WARNING);
    }

    function testNoticeSuppressedWhenReported() {
        error_reporting(E_ALL);
        @trigger_error('Ouch!', E_USER_NOTICE);
    }

    function testWarningSuppressedWhenReported() {
        error_reporting(E_ALL);
        @trigger_error('Ouch!', E_USER_WARNING);
    }

    function testErrorWithPercentsReportedWithNoSprintfError() {
        $this->expectError('%');
        trigger_error('%');
    }
}

class TestOfPHP52RecoverableErrors extends UnitTestCase {
    function skip() {
        $this->skipIf(
                version_compare(phpversion(), '5.2', '<'),
                'E_RECOVERABLE_ERROR not tested for PHP below 5.2');
    }

    function testError() {
        eval('
            class RecoverableErrorTestingStub {
                function ouch(RecoverableErrorTestingStub $obj) {
                }
            }
        ');

        $stub = new RecoverableErrorTestingStub();
        $this->expectError(new PatternExpectation('/must be an instance of RecoverableErrorTestingStub/i'));
        $stub->ouch(new stdClass());
    }
}

class TestOfErrorsExcludingPHP52AndAbove extends UnitTestCase {
    function skip() {
        $this->skipIf(0 &&
                version_compare(phpversion(), '5.2', '>='),
                'E_USER_ERROR not tested for PHP 5.2 and above');
    }

    function testNoErrorWhenNotReported() {
        error_reporting(0);
        trigger_error('Ouch!', E_USER_ERROR);
    }

    function testErrorSuppressedWhenReported() {
        error_reporting(E_ALL);
        @trigger_error('Ouch!', E_USER_ERROR);
    }
}

SimpleTest::ignore('TestOfNotEnoughErrors');
/**
 * This test is ignored as it is used by {@link TestRunnerForLeftOverAndNotEnoughErrors}
 * to verify that it fails as expected.
 *
 * @ignore
 */
class TestOfNotEnoughErrors extends UnitTestCase {
    function testExpectTwoErrorsThrowOne() {
        $this->expectError('Error 1');
        trigger_error('Error 1');
        $this->expectError('Error 2');
    }
}

SimpleTest::ignore('TestOfLeftOverErrors');
/**
 * This test is ignored as it is used by {@link TestRunnerForLeftOverAndNotEnoughErrors}
 * to verify that it fails as expected.
 *
 * @ignore
 */
class TestOfLeftOverErrors extends UnitTestCase {
    function testExpectOneErrorGetTwo() {
        $this->expectError('Error 1');
        trigger_error('Error 1');
        trigger_error('Error 2');
    }
}

SimpleTest::ignore('TestOfAnythingErrors');
SimpleTest::ignore('TestOfLeftOverAnythingErrors');
/**
 * This test is ignored as it is used by {@link TestRunnerForLeftOverAndNotEnoughErrors}
 * to verify that it fails as expected.
 *
 * @ignore
 */
class TestOfAnythingErrors extends UnitTestCase {
    function testExpectOneErrorGetTwo() {
        $this->expectError(new AnythingExpectation());
        trigger_error('Error 1');
        trigger_error('Error 2');
    }
}
/**
 * This test is ignored as it is used by {@link TestRunnerForLeftOverAndNotEnoughErrors}
 * to verify that it fails as expected.
 *
 * @ignore
 */
class TestOfLeftOverAnythingErrors extends UnitTestCase {
    function testExpectOneErrorGetZero() {
        $this->expectError(new AnythingExpectation());
        $this->assertTrue(true);
		// no error, so we'll have an AnythingExpectation dangling in the tally: it should pop up there!
    }
}


class TestRunnerForLeftOverAndNotEnoughErrors extends UnitTestCase {
    function testRunLeftOverErrorsTestCase() {
        $test = new TestOfLeftOverErrors();
        $this->assertFalse($test->run(new SimpleReporter()));
		// also make sure that the test run did produce exactly 1 failure, nothing else:
		$reporter = $test->getReporter(); // get the inner test's reporter, as /this/ test level should be using another one again already!
		$this->assertEqual($reporter->getTestCaseCount(), 1, "%s -> Fail TestCaseCount");
		$this->assertEqual($reporter->getTestCaseProgress(), 1, "%s -> Fail TestCaseProgress");
		$this->assertEqual($reporter->getPassCount(), 1, "%s -> Fail getPassCount");
		$this->assertEqual($reporter->getFailCount(), 0, "%s -> Fail getFailCount");
		$this->assertEqual($reporter->getExceptionCount(), 1, "%s -> Fail getExceptionCount");
    }

    function testRunNotEnoughErrors() {
        $test = new TestOfNotEnoughErrors();
        $this->assertFalse($test->run(new SimpleReporter()));
		// also make sure that the test run did produce exactly 1 failure, nothing else:
		$reporter = $test->getReporter(); // get the inner test's reporter, as /this/ test level should be using another one again already!
		//$context = SimpleTest::getContext();
        //$reporter = $context->getReporter();
		$this->assertEqual($reporter->getTestCaseCount(), 1, "%s -> Fail TestCaseCount");
		$this->assertEqual($reporter->getTestCaseProgress(), 1, "%s -> Fail TestCaseProgress");
		$this->assertEqual($reporter->getPassCount(), 1, "%s -> Fail getPassCount");
		$this->assertEqual($reporter->getFailCount(), 1, "%s -> Fail getFailCount");
		$this->assertEqual($reporter->getExceptionCount(), 0, "%s -> Fail getExceptionCount");
    }

    function testRunAnythingError() {
        $test = new TestOfAnythingErrors();
        $this->assertFalse($test->run(new SimpleReporter()));
		$reporter = $test->getReporter(); // get the inner test's reporter, as /this/ test level should be using another one again already!
		$this->assertEqual($reporter->getTestCaseCount(), 1, "%s -> Fail TestCaseCount");
		$this->assertEqual($reporter->getTestCaseProgress(), 1, "%s -> Fail TestCaseProgress");
		$this->assertEqual($reporter->getPassCount(), 1, "%s -> Fail getPassCount");
		$this->assertEqual($reporter->getFailCount(), 0, "%s -> Fail getFailCount");
		$this->assertEqual($reporter->getExceptionCount(), 1, "%s -> Fail getExceptionCount");
    }

    function testRunLeftOverAnythingError() {
        $test = new TestOfLeftOverAnythingErrors();
		// this is done this way to test nesting of expects: the expectFail() here should NOT be munched by the inner TestOfLeftOverAnythingErrors() test:
        $this->expectFail()->assertTrue($test->run(new SimpleReporter()));
		$reporter = $test->getReporter(); // get the inner test's reporter, as /this/ test level should be using another one again already!
		$this->assertEqual($reporter->getTestCaseCount(), 1, "%s -> Fail TestCaseCount");
		$this->assertEqual($reporter->getTestCaseProgress(), 1, "%s -> Fail TestCaseProgress");
		$this->assertEqual($reporter->getPassCount(), 1, "%s -> Fail getPassCount");
		$this->assertEqual($reporter->getFailCount(), 1, "%s -> Fail getFailCount");
		$this->assertEqual($reporter->getExceptionCount(), 0, "%s -> Fail getExceptionCount");
    }
}

// TODO: Add stacked error handler test
