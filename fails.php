<?php
/**
 *  base include file for SimpleTest
 *  @package    SimpleTest
 *  @subpackage UnitTester
 *  @version    $Id$
 */

/**#@+
 * Includes SimpleTest files.
 */
require_once dirname(__FILE__) . '/invoker.php';
require_once dirname(__FILE__) . '/test_case.php';
require_once dirname(__FILE__) . '/expectation.php';
/**#@-*/

/**
 *    Extension that traps failing assertions into an fail queue.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class SimpleFailTrappingInvoker extends SimpleInvokerDecorator {

    /**
     *    Stores the invoker to wrap.
     *    @param SimpleInvoker $invoker  Test method runner.
     */
    function __construct($invoker) {
        parent::__construct($invoker);
    }

    /**
     *    Invokes a test method and dispatches any
     *    untrapped assertion failures. Called back from
     *    the visiting runner.
     *    @param string $method    Test method to call.
     *    @access public
     */
    function invoke($method) {
        $queue = $this->createFailQueue();
        parent::invoke($method);
        $queue->tally();
    }

    /**
     *    Wires up the failure queue for a single test.
     *    @return SimpleFailQueue    Queue connected to the test.
     *    @access protected
     */
    protected function createFailQueue() {
        $context = SimpleTest::getContext();
        $test = $this->getTestCase();
        $queue = $context->get('SimpleFailQueue');
        $queue->setTestCase($test);
        return $queue;
    }
}

/**
 *    Failure queue used to record trapped
 *    assertion failures.
 *    @package  SimpleTest
 *    @subpackage   UnitTester
 */
class SimpleFailQueue {
    protected $expectation_queue;
    protected $test;

    /**
     *    Starts with an empty queue.
     */
    function __construct() {
        $this->clear();
    }

    /**
     *    Discards the contents of the fail queue.
     *    @access public
     */
    function clear() {
        $this->expectation_queue = array();
    }

    /**
     *    Sets the currently running test case.
     *    @param SimpleTestCase $test    Test case to send messages to.
     *    @access public
     */
    function setTestCase($test) {
        $this->test = $test;
    }

    /**
     *    Sets up an expectation of an assertion failure. If this is
     *    not fulfilled at the end of the test, a failure reporting this
     *    will occur. If the assertion failure does happen, then this
     *    will cancel it out and send a pass message.
     *    @param SimpleExpectation $expected    Expected fail match.
     *    @param string $message                Message to display.
     *    @access public
     */
    function expectFail($expected, $message) {
        array_push($this->expectation_queue, array($expected, $message));
    }

    /**
     *    Adds an assertion failure to the front of the queue.
     *    @param integer $severity       PHP error code.
     *    @param string $content         Text of error.
     *    @param string $file_and_line   Formatted file and line number where assertion failure occoured.
     *    @access public
     */
    function add($content, $file_and_line, $mode) {
        $content = str_replace('%', '%%', $content);
        return $this->testLatestFail($content, $file_and_line, $mode);
    }

    /**
     *    Any assertion failures still in the queue are sent to the test
     *    case. Any unfulfilled expectations trigger failures.
     *    @access public
     */
    function tally() {
        while (list($expected, $message) = $this->extractExpectation()) {
            $this->test->fail($this->test->constructFailMessage($expected, 'tally dangling', "%s -> Expected fail not caught"));
        }
    }

    /**
     *    Tests the fail against the most recent expected
     *    assertion failure.
     *    @param string $content         Text of error.
     *    @param string $file_and_line   Formatted file and line where assertion failure occoured.
     *    @access protected
     */
    protected function testLatestFail($content, $file_and_line, $mode) {
        if ($expectation = $this->extractExpectation()) {
            list($expected, $message) = $expectation;
            $this->test->assert($expected, $content, sprintf(
                    $message,
                    "%s -> Unexpected pass: [$content] $file_and_line"));
            return -1 * $mode;
        }
        return $mode;
    }

    /**
     *    Pulls the earliest expectation from the queue.
     *    @return     SimpleExpectation    False if none.
     *    @access protected
     */
    protected function extractExpectation() {
        if (count($this->expectation_queue)) {
            return array_shift($this->expectation_queue);
        }
        return false;
    }
}

