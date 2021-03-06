<?php
/**
 *  base include file for SimpleTest
 *  @package    SimpleTest
 *  @subpackage UnitTester
 *  @version    $Id$
 */

/**#@+*/
require_once(dirname(__FILE__) . '/invoker.php');
/**#@-*/

/**
 *    Can receive test events and display them. Display
 *    is achieved by making display methods available
 *    and visiting the incoming event.
 *    @package SimpleTest
 *    @subpackage UnitTester
 *    @abstract
 */
class SimpleScorer {
    protected $passes;
    protected $fails;
    protected $errors;
    protected $exceptions;
    protected $is_dry_run;
    protected $list_tests;
    protected $show_breadcrumb;
    protected $show_stacktrace;

    /**
     *    Starts the test run with no results.
     *    @access public
     */
    function __construct() {
        $this->passes = 0;
        $this->fails = 0;
        $this->errors = 0;
        $this->exceptions = 0;
        $this->is_dry_run = false;
        $this->list_tests = false;
        $this->show_breadcrumb = true;
        $this->show_stacktrace = true;
    }

    /**
     *    Signals that the next evaluation will be a dry
     *    run. That is, the structure events will be
     *    recorded, but no tests will be run.
     *    @param boolean $is_dry        Dry run if true.
     *    @return boolean               The previous state.
     *    @access public
     */
    function makeDry($is_dry = true) {
        $rv = $this->is_dry_run;
        $this->is_dry_run = $is_dry;
        return $rv;
    }

    /**
     *    Signals that the next run will list the tests.
     *    @param boolean $do_list       The next run will show Tests' names if true.
     *    @return boolean               The previous state.
     *    @access public
     */
    function makeList($do_list = true) {
        $rv = $this->list_tests;
        $this->list_tests = $do_list;
        return $rv;
    }

    /**
     *    Output of the next run should include 'breadcrumb' invocation chains when available.
     *    @param boolean $state       True for breadcrumb inclusion, False for exclusion
     *                                and null if you only want to retrieve the current state.
     *    @return boolean             The previous state.
     */
    function includeBreadCrumb($state = null) {
        $rv = $this->show_breadcrumb;
        if (isset($state)) {
            $this->show_breadcrumb = (boolean)$state;
        }
        return $rv;
    }

    /**
     *    Output of the next run should include stack traces when available.
     *    @param boolean $state       True for stack trace inclusion, False for exclusion
     *                                and null if you only want to retrieve the current state.
     *    @return boolean             The previous state.
     */
    function includeStackTrace($state = null) {
        $rv = $this->show_stacktrace;
        if (isset($state)) {
            $this->show_stacktrace = (boolean)$state;
        }
        return $rv;
    }

    /**
     *    The reporter has a veto on what should be run.
     *    @param string $test_case_name  name of test case.
     *    @param string $method          Name of test method.
     *    @access public
     */
    function shouldInvoke($test_case_name, $method) {
        return ! $this->is_dry_run;
    }

    /**
     *    Can wrap the invoker in preperation for running
     *    a test.
     *    @param SimpleInvoker $invoker   Individual test runner.
     *    @return SimpleInvoker           Wrapped test runner.
     *    @access public
     */
    function createInvoker($invoker) {
        return $invoker;
    }

    /**
     *    Accessor for current status. Will be false
     *    if there have been any failures or exceptions.
     *    Used for command line tools.
     *    @return boolean        True if no failures.
     *    @access public
     */
    function getStatus() {
        if ($this->errors + $this->exceptions + $this->fails > 0) {
            return false;
        }
        return true;
    }

    /**
     *    Paints the start of a group test.
     *    @param string $test_name     Name of test or other label.
     *    @param integer $size         Number of test cases starting.
     *    @access public
     */
    function paintGroupStart($test_name, $size) {
    }

    /**
     *    Paints the end of a group test.
     *    @param string $test_name     Name of test or other label.
     *    @access public
     */
    function paintGroupEnd($test_name) {
    }

    /**
     *    Paints the start of a test case.
     *    @param string $test_name     Name of test or other label.
     *    @access public
     */
    function paintCaseStart($test_name) {
    }

    /**
     *    Paints the end of a test case.
     *    @param string $test_name     Name of test or other label.
     *    @access public
     */
    function paintCaseEnd($test_name) {
    }

    /**
     *    Paints the start of a test method.
     *    @param string $test_name     Name of test or other label.
     *    @access public
     */
    function paintMethodStart($test_name) {
    }

    /**
     *    Paints the end of a test method.
     *    @param string $test_name     Name of test or other label.
     *    @access public
     */
    function paintMethodEnd($test_name) {
    }

    /**
     *    Increments the pass count.
     *
     *    Note: Derive from or invoke this method directly when you are
     *          only interested in the test run bookkeeping. Use the paint
     *          methods when you are interested in the visula feedback
     *          of a test run.
     *
     *    @access public
     */
    function incrementPassCount() {
        $this->passes++;
    }

    /**
     *    Increments the fail count.
     *
     *    Note: Derive from or invoke this method directly when you are
     *          only interested in the test run bookkeeping. Use the paint
     *          methods when you are interested in the visula feedback
     *          of a test run.
     *
     *    @access public
     */
    function incrementFailCount() {
        $this->fails++;
    }

    /**
     *    Increments the error count.
     *
     *    Note: Derive from or invoke this method directly when you are
     *          only interested in the test run bookkeeping. Use the paint
     *          methods when you are interested in the visula feedback
     *          of a test run.
     *
     *    @access public
     */
    function incrementErrorCount() {
        $this->errors++;
    }

    /**
     *    Increments the exception count.
     *
     *    Note: Derive from or invoke this method directly when you are
     *          only interested in the test run bookkeeping. Use the paint
     *          methods when you are interested in the visula feedback
     *          of a test run.
     *
     *    @access public
     */
    function incrementExceptionCount() {
        $this->exceptions++;
    }

    /**
     *    Potentially show the pass message.
     *    @param string $message        Message is ignored.
     *    @access public
     */
    function paintPass($message) {
        //$this->incrementPassCount();
    }

    /**
     *    Potentially show the fail message.
     *    @param string $message        Message is ignored.
     *    @access public
     */
    function paintFail($message) {
        //$this->incrementFailCount();
    }

    /**
     *    Deals with PHP 4 throwing an error.
     *    @param string $message    Text of error formatted by
     *                              the test case.
     *    @access public
     */
    function paintError($message) {
        //$this->incrementExceptionCounts();
    }

    /**
     *    Deals with PHP 5 throwing an exception.
     *    @param Exception $exception    The actual exception thrown.
     *    @access public
     */
    function paintException($exception) {
        //$this->incrementExceptionCounts();
    }

    /**
     *    Prints the message for skipping tests.
     *    @param string $message    Text of skip condition.
     *    @access public
     */
    function paintSkip($message) {
    }

    /**
     *    Accessor for the number of passes so far.
     *    @return integer       Number of passes.
     *    @access public
     */
    function getPassCount() {
        return $this->passes;
    }

    /**
     *    Accessor for the number of fails so far.
     *    @return integer       Number of fails.
     *    @access public
     */
    function getFailCount() {
        return $this->fails;
    }

    /**
     *    Accessor for the number of untrapped errors
     *    so far.
     *    @return integer       Number of exceptions.
     *    @access public
     */
    function getExceptionCount() {
        return $this->errors + $this->exceptions;
    }

    /**
     *    Paints a simple supplementary message.
     *    @param string $message        Text to display.
     *    @access public
     */
    function paintMessage($message) {
    }

    /**
     *    Paints a formatted ASCII message such as a
     *    variable dump.
     *    @param string $message        Text to display.
     *    @access public
     */
    function paintFormattedMessage($message) {
    }

    /**
     *    By default just ignores user generated events.
     *    @param string $type        Event type as text.
     *    @param mixed $payload      Message or object.
     *    @access public
     */
    function paintSignal($type, $payload) {
    }
}

/**
 *    Recipient of generated test messages that can display
 *    page footers and headers. Also keeps track of the
 *    test nesting. This is the main base class on which
 *    to build the finished test (page based) displays.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class SimpleReporter extends SimpleScorer {
    protected $test_stack;
    protected $size;
    protected $progress;

    /**
     *    Starts the display with no results in.
     *    @access public
     */
    function __construct() {
        parent::__construct();
        $this->test_stack = array();
        $this->size = null;
        $this->progress = 0;
    }

    /**
     *    Gets the formatter for small generic data items.
     *    @return SimpleDumper          Formatter.
     *    @access public
     */
    function getDumper() {
        return new SimpleDumper();
    }

    /**
     *    Paints the start of a group test. Will also paint
     *    the page header and footer if this is the
     *    first test. Will stash the size if the first
     *    start.
     *    @param string $test_name   Name of test that is starting.
     *    @param integer $size       Number of test cases starting.
     *    @access public
     */
    function paintGroupStart($test_name, $size) {
        if (! isset($this->size)) {
            $this->size = $size;
        }
        if (count($this->test_stack) == 0) {
            $this->paintHeader($test_name);
        }
        $this->test_stack[] = $test_name;
    }

    /**
     *    Paints the end of a group test. Will paint the page
     *    footer if the stack of tests has unwound.
     *    @param string $test_name   Name of test that is ending.
     *    @param integer $progress   Number of test cases ending.
     *    @access public
     */
    function paintGroupEnd($test_name) {
        array_pop($this->test_stack);
        if (count($this->test_stack) == 0) {
            $this->paintFooter($test_name);
        }
    }

    /**
     *    Paints the start of a test case. Will also paint
     *    the page header and footer if this is the
     *    first test. Will stash the size if the first
     *    start.
     *    @param string $test_name   Name of test that is starting.
     *    @access public
     */
    function paintCaseStart($test_name) {
        if (! isset($this->size)) {
            $this->size = 1;
        }
        if (count($this->test_stack) == 0) {
            $this->paintHeader($test_name);
        }
        $this->test_stack[] = $test_name;
    }

    /**
     *    Paints the end of a test case. Will paint the page
     *    footer if the stack of tests has unwound.
     *    @param string $test_name   Name of test that is ending.
     *    @access public
     */
    function paintCaseEnd($test_name) {
        $this->progress++;
        array_pop($this->test_stack);
        if (count($this->test_stack) == 0) {
            $this->paintFooter($test_name);
        }
    }

    /**
     *    Paints the start of a test method.
     *    @param string $test_name   Name of test that is starting.
     *    @access public
     */
    function paintMethodStart($test_name) {
        $this->test_stack[] = $test_name;
    }

    /**
     *    Paints the end of a test method. Will paint the page
     *    footer if the stack of tests has unwound.
     *    @param string $test_name   Name of test that is ending.
     *    @access public
     */
    function paintMethodEnd($test_name) {
        array_pop($this->test_stack);
    }

    /**
     *    Paints the test document header.
     *    @param string $test_name     First test top level
     *                                 to start.
     *    @access public
     *    @abstract
     */
    function paintHeader($test_name) {
    }

    /**
     *    Paints the test document footer.
     *    @param string $test_name        The top level test.
     *    @access public
     *    @abstract
     */
    function paintFooter($test_name) {
    }

    /**
     *    Accessor for internal test stack. For
     *    subclasses that need to see the whole test
     *    history for display purposes.
     *    @return array     List of methods in nesting order.
     *    @access public
     */
    function getTestList() {
        return $this->test_stack;
    }

    /**
     *    Accessor for total test size in number
     *    of test cases. Null until the first
     *    test is started.
     *    @return integer   Total number of cases at start.
     *    @access public
     */
    function getTestCaseCount() {
        return $this->size;
    }

    /**
     *    Accessor for the number of test cases
     *    completed so far.
     *    @return integer   Number of completed cases.
     *    @access public
     */
    function getTestCaseProgress() {
        return $this->progress;
    }

    /**
     *    Serialize/stringify any object to string through serialization.
     *    Keep strings intact, i.e. do not serialize these.
     *    @param mixed $object      The item to stringify/serialize.
     *    @return string            The readable form of the object.
     *    @access protected
     */
    protected function serializePayload($object) {
        if (is_string($object)) {
            return $object;
        }
        else {
            return serialize($object);
        }
    }

    /**
     *    Static check for running in the comand line.
     *    @return boolean        True if CLI.
     *    @access public
     */
    static function inCli() {
        return php_sapi_name() == 'cli';
    }
}

/**
 *    For modifying the behaviour of the visual reporters.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class SimpleReporterDecorator {
    protected $reporter;

    /**
     *    Mediates between the reporter and the test case.
     *    @param SimpleScorer $reporter       Reporter to receive events.
     */
    function __construct($reporter) {
        $this->reporter = $reporter;
    }

    /**
     *    Signals that the next evaluation will be a dry
     *    run. That is, the structure events will be
     *    recorded, but no tests will be run.
     *    @param boolean $is_dry              Dry run if true.
     *    @return boolean                     The previous state.
     *    @access public
     */
    function makeDry($is_dry = true) {
        return $this->reporter->makeDry($is_dry);
    }

    /**
     *    Signals that the next run will list the tests.
     *    @param boolean $do_list             The next run will show Tests' names if true.
     *    @return boolean                     The previous state.
     *    @access public
     */
    function makeList($do_list = true) {
        return $this->reporter->makeList($do_list);
    }

    /**
     *    Output of the next run should include 'breadcrumb' invocation chains when available.
     *    @param boolean $state       True for breadcrumb inclusion, False for exclusion
     *                                and null if you only want to retrieve the current state.
     *    @return boolean             The previous state.
     */
    function includeBreadCrumb($state = null) {
        return $this->reporter->includeBreadCrumb($state);
    }

    /**
     *    Output of the next run should include stack traces when available.
     *    @param boolean $state       True for stack trace inclusion, False for exclusion
     *                                and null if you only want to retrieve the current state.
     *    @return boolean             The previous state.
     */
    function includeStackTrace($state = null) {
        return $this->reporter->includeStackTrace($state);
    }

    /**
     *    Accessor for current status. Will be false
     *    if there have been any failures or exceptions.
     *    Used for command line tools.
     *    @return boolean        True if no failures.
     *    @access public
     */
    function getStatus() {
        return $this->reporter->getStatus();
    }

    /**
     *    The nesting of the test cases so far.
     *    @return array        Test list if accessible.
     *    @access public
     */
    function getTestList() {
        return $this->reporter->getTestList();
    }

    /**
     *    Accessor for total test size in number
     *    of test cases. Null until the first
     *    test is started.
     *    @return integer   Total number of cases at start.
     *    @access public
     */
    function getTestCaseCount() {
        return $this->reporter->getTestCaseCount();
    }

    /**
     *    Accessor for the number of test cases
     *    completed so far.
     *    @return integer   Number of completed cases.
     *    @access public
     */
    function getTestCaseProgress() {
        return $this->reporter->getTestCaseProgress();
    }

    /**
     *    The reporter has a veto on what should be run.
     *    @param string $test_case_name  Name of test case.
     *    @param string $method          Name of test method.
     *    @return boolean                True if test should be run.
     *    @access public
     */
    function shouldInvoke($test_case_name, $method) {
        return $this->reporter->shouldInvoke($test_case_name, $method);
    }

    /**
     *    Can wrap the invoker in preparation for running
     *    a test.
     *    @param SimpleInvoker $invoker   Individual test runner.
     *    @return SimpleInvoker           Wrapped test runner.
     *    @access public
     */
    function createInvoker($invoker) {
        return $this->reporter->createInvoker($invoker);
    }

    /**
     *    Gets the formatter for variables and other small
     *    generic data items.
     *    @return SimpleDumper          Formatter.
     *    @access public
     */
    function getDumper() {
        return $this->reporter->getDumper();
    }

    /**
     *    Paints the start of a group test.
     *    @param string $test_name     Name of test or other label.
     *    @param integer $size         Number of test cases starting.
     *    @access public
     */
    function paintGroupStart($test_name, $size) {
        $this->reporter->paintGroupStart($test_name, $size);
    }

    /**
     *    Paints the end of a group test.
     *    @param string $test_name     Name of test or other label.
     *    @access public
     */
    function paintGroupEnd($test_name) {
        $this->reporter->paintGroupEnd($test_name);
    }

    /**
     *    Paints the start of a test case.
     *    @param string $test_name     Name of test or other label.
     *    @access public
     */
    function paintCaseStart($test_name) {
        $this->reporter->paintCaseStart($test_name);
    }

    /**
     *    Paints the end of a test case.
     *    @param string $test_name     Name of test or other label.
     *    @access public
     */
    function paintCaseEnd($test_name) {
        $this->reporter->paintCaseEnd($test_name);
    }

    /**
     *    Paints the start of a test method.
     *    @param string $test_name     Name of test or other label.
     *    @access public
     */
    function paintMethodStart($test_name) {
        $this->reporter->paintMethodStart($test_name);
    }

    /**
     *    Paints the end of a test method.
     *    @param string $test_name     Name of test or other label.
     *    @access public
     */
    function paintMethodEnd($test_name) {
        $this->reporter->paintMethodEnd($test_name);
    }

    /**
     *    Chains to the wrapped reporter.
     *    @access public
     */
    function incrementPassCount() {
        $this->reporter->incrementPassCount();
    }

    /**
     *    Chains to the wrapped reporter.
     *    @access public
     */
    function incrementFailCount() {
        $this->reporter->incrementFailCount();
    }

    /**
     *    Chains to the wrapped reporter.
     *    @access public
     */
    function incrementErrorCount() {
        $this->reporter->incrementErrorCount();
    }

    /**
     *    Chains to the wrapped reporter.
     *    @access public
     */
    function incrementExceptionCount() {
        $this->reporter->incrementExceptionCount();
    }

    /**
     *    Chains to the wrapped reporter.
     *    @param string $message        Message is ignored.
     *    @access public
     */
    function paintPass($message) {
        $this->reporter->paintPass($message);
    }

    /**
     *    Chains to the wrapped reporter.
     *    @param string $message        Message is ignored.
     *    @access public
     */
    function paintFail($message) {
        $this->reporter->paintFail($message);
    }

    /**
     *    Chains to the wrapped reporter.
     *    @param string $message    Text of error formatted by
     *                              the test case.
     *    @access public
     */
    function paintError($message) {
        $this->reporter->paintError($message);
    }

    /**
     *    Chains to the wrapped reporter.
     *    @param Exception $exception        Exception to show.
     *    @access public
     */
    function paintException($exception) {
        $this->reporter->paintException($exception);
    }

    /**
     *    Prints the message for skipping tests.
     *    @param string $message    Text of skip condition.
     *    @access public
     */
    function paintSkip($message) {
        $this->reporter->paintSkip($message);
    }

    /**
     *    Chains to the wrapped reporter.
     *    @param string $message        Text to display.
     *    @access public
     */
    function paintMessage($message) {
        $this->reporter->paintMessage($message);
    }

    /**
     *    Chains to the wrapped reporter.
     *    @param string $message        Text to display.
     *    @access public
     */
    function paintFormattedMessage($message) {
        $this->reporter->paintFormattedMessage($message);
    }

    /**
     *    Chains to the wrapped reporter.
     *    @param string $type        Event type as text.
     *    @param mixed $payload      Message or object.
     *    @return boolean            Should return false if this
     *                               type of signal should fail the
     *                               test suite.
     *    @access public
     */
    function paintSignal($type, $payload) {
        $this->reporter->paintSignal($type, $payload);
    }
}

/**
 *    For sending messages to multiple reporters at
 *    the same time.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class MultipleReporter {
    protected $reporters;

    /**
     *    Set up an MultipleReporter instance which allows you to send
     *    (paint) messages to multiple reporters in parallel.
     *
     *    @param Array $reporters       An array of instances of class SimpleReporter or
     *                                  SimpleReporterDecorator or their derivates.
     */
    function __construct($reporters = array()) {
        $this->reporters = $reporters;
    }

    /**
     *    Adds a reporter to the subscriber list.
     *    @param SimpleScorer $reporter     Reporter to receive events.
     *    @access public
     */
    function attachReporter($reporter) {
        $this->reporters[] = $reporter;
    }

    /**
     *    Signals that the next evaluation will be a dry
     *    run. That is, the structure events will be
     *    recorded, but no tests will be run.
     *    @param boolean $is_dry        Dry run if true.
     *    @return boolean               The previous state.
     *    @access public
     */
    function makeDry($is_dry = true) {
        $rv = false;
        for ($i = 0; $i < count($this->reporters); $i++) {
            $rv |= $this->reporters[$i]->makeDry($is_dry);
        }
        return $rv;
    }

    /**
     *    Signals that the next run will list the tests.
     *    @param boolean $do_list       The next run will show Tests' names if true.
     *    @return boolean               The previous state.
     *    @access public
     */
    function makeList($do_list = true) {
        $rv = false;
        for ($i = 0; $i < count($this->reporters); $i++) {
            $rv |= $this->reporters[$i]->makeList($do_list);
        }
        return $rv;
    }

    /**
     *    Output of the next run should include 'breadcrumb' invocation chains when available.
     *    @param boolean $state       True for breadcrumb inclusion, False for exclusion
     *                                and null if you only want to retrieve the current state.
     *    @return boolean             The previous state.
     */
    function includeBreadCrumb($state = null) {
        $rv = false;
        for ($i = 0; $i < count($this->reporters); $i++) {
            $rv |= $this->reporters[$i]->includeBreadCrumb($state);
        }
        return $rv;
    }

    /**
     *    Output of the next run should include stack traces when available.
     *    @param boolean $state       True for stack trace inclusion, False for exclusion
     *                                and null if you only want to retrieve the current state.
     *    @return boolean             The previous state.
     */
    function includeStackTrace($state = null) {
        $rv = false;
        for ($i = 0; $i < count($this->reporters); $i++) {
            $rv |= $this->reporters[$i]->includeStackTrace($state);
        }
        return $rv;
    }

    /**
     *    Accessor for current status. Will be false
     *    if there have been any failures or exceptions.
     *    If any reporter reports a failure, the whole
     *    suite fails.
     *    @return boolean        True if no failures.
     *    @access public
     */
    function getStatus() {
        for ($i = 0; $i < count($this->reporters); $i++) {
            if (! $this->reporters[$i]->getStatus()) {
                return false;
            }
        }
        return true;
    }

    /**
     *    Accessor for internal test stack. For
     *    subclasses that need to see the whole test
     *    history for display purposes.
     *    @return array     List of methods in nesting order.
     *    @access public
     */
    function getTestList() {
        $ret = array();
        for ($i = 0; $i < count($this->reporters); $i++) {
            $ret = array_merge($ret, $this->reporters[$i]->getTestList());
        }
        return $ret;
    }

    /**
     *    The reporter has a veto on what should be run.
     *    It requires all reporters to want to run the method.
     *    @param string $test_case_name  name of test case.
     *    @param string $method          Name of test method.
     *    @access public
     */
    function shouldInvoke($test_case_name, $method) {
        for ($i = 0; $i < count($this->reporters); $i++) {
            if (! $this->reporters[$i]->shouldInvoke($test_case_name, $method)) {
                return false;
            }
        }
        return true;
    }

    /**
     *    Every reporter gets a chance to wrap the invoker.
     *    @param SimpleInvoker $invoker   Individual test runner.
     *    @return SimpleInvoker           Wrapped test runner.
     *    @access public
     */
    function createInvoker($invoker) {
        for ($i = 0; $i < count($this->reporters); $i++) {
            $invoker = $this->reporters[$i]->createInvoker($invoker);
        }
        return $invoker;
    }

    /**
     *    Gets the formatter for variables and other small
     *    generic data items.
     *    @return SimpleDumper          Formatter.
     *    @access public
     */
    function getDumper() {
        return new SimpleDumper();
    }

    /**
     *    Paints the start of a group test.
     *    @param string $test_name     Name of test or other label.
     *    @param integer $size         Number of test cases starting.
     *    @access public
     */
    function paintGroupStart($test_name, $size) {
        for ($i = 0; $i < count($this->reporters); $i++) {
            $this->reporters[$i]->paintGroupStart($test_name, $size);
        }
    }

    /**
     *    Paints the end of a group test.
     *    @param string $test_name     Name of test or other label.
     *    @access public
     */
    function paintGroupEnd($test_name) {
        for ($i = 0; $i < count($this->reporters); $i++) {
            $this->reporters[$i]->paintGroupEnd($test_name);
        }
    }

    /**
     *    Paints the start of a test case.
     *    @param string $test_name     Name of test or other label.
     *    @access public
     */
    function paintCaseStart($test_name) {
        for ($i = 0; $i < count($this->reporters); $i++) {
            $this->reporters[$i]->paintCaseStart($test_name);
        }
    }

    /**
     *    Paints the end of a test case.
     *    @param string $test_name     Name of test or other label.
     *    @access public
     */
    function paintCaseEnd($test_name) {
        for ($i = 0; $i < count($this->reporters); $i++) {
            $this->reporters[$i]->paintCaseEnd($test_name);
        }
    }

    /**
     *    Paints the start of a test method.
     *    @param string $test_name     Name of test or other label.
     *    @access public
     */
    function paintMethodStart($test_name) {
        for ($i = 0; $i < count($this->reporters); $i++) {
            $this->reporters[$i]->paintMethodStart($test_name);
        }
    }

    /**
     *    Paints the end of a test method.
     *    @param string $test_name     Name of test or other label.
     *    @access public
     */
    function paintMethodEnd($test_name) {
        for ($i = 0; $i < count($this->reporters); $i++) {
            $this->reporters[$i]->paintMethodEnd($test_name);
        }
    }

    /**
     *    Chains to the wrapped reporter.
     *    @access public
     */
    function incrementPassCount() {
        for ($i = 0; $i < count($this->reporters); $i++) {
            $this->reporters[$i]->incrementPassCount();
        }
    }

    /**
     *    Chains to the wrapped reporter.
     *    @access public
     */
    function incrementFailCount() {
        for ($i = 0; $i < count($this->reporters); $i++) {
            $this->reporters[$i]->incrementFailCount();
        }
    }

    /**
     *    Chains to the wrapped reporter.
     *    @access public
     */
    function incrementErrorCount() {
        for ($i = 0; $i < count($this->reporters); $i++) {
            $this->reporters[$i]->incrementErrorCount();
        }
    }

    /**
     *    Chains to the wrapped reporter.
     *    @access public
     */
    function incrementExceptionCount() {
        for ($i = 0; $i < count($this->reporters); $i++) {
            $this->reporters[$i]->incrementExceptionCount();
        }
    }

    /**
     *    Chains to the wrapped reporter.
     *    @param string $message        Message is ignored.
     *    @access public
     */
    function paintPass($message) {
        for ($i = 0; $i < count($this->reporters); $i++) {
            $this->reporters[$i]->paintPass($message);
        }
    }

    /**
     *    Chains to the wrapped reporter.
     *    @param string $message        Message is ignored.
     *    @access public
     */
    function paintFail($message) {
        for ($i = 0; $i < count($this->reporters); $i++) {
            $this->reporters[$i]->paintFail($message);
        }
    }

    /**
     *    Chains to the wrapped reporter.
     *    @param string $message    Text of error formatted by
     *                              the test case.
     *    @access public
     */
    function paintError($message) {
        for ($i = 0; $i < count($this->reporters); $i++) {
            $this->reporters[$i]->paintError($message);
        }
    }

    /**
     *    Chains to the wrapped reporter.
     *    @param Exception $exception    Exception to display.
     *    @access public
     */
    function paintException($exception) {
        for ($i = 0; $i < count($this->reporters); $i++) {
            $this->reporters[$i]->paintException($exception);
        }
    }

    /**
     *    Prints the message for skipping tests.
     *    @param string $message    Text of skip condition.
     *    @access public
     */
    function paintSkip($message) {
        for ($i = 0; $i < count($this->reporters); $i++) {
            $this->reporters[$i]->paintSkip($message);
        }
    }

    /**
     *    Chains to the wrapped reporter.
     *    @param string $message        Text to display.
     *    @access public
     */
    function paintMessage($message) {
        for ($i = 0; $i < count($this->reporters); $i++) {
            $this->reporters[$i]->paintMessage($message);
        }
    }

    /**
     *    Chains to the wrapped reporter.
     *    @param string $message        Text to display.
     *    @access public
     */
    function paintFormattedMessage($message) {
        for ($i = 0; $i < count($this->reporters); $i++) {
            $this->reporters[$i]->paintFormattedMessage($message);
        }
    }

    /**
     *    Chains to the wrapped reporter.
     *    @param string $type        Event type as text.
     *    @param mixed $payload      Message or object.
     *    @return boolean            Should return false if this
     *                               type of signal should fail the
     *                               test suite.
     *    @access public
     */
    function paintSignal($type, $payload) {
        for ($i = 0; $i < count($this->reporters); $i++) {
            $this->reporters[$i]->paintSignal($type, $payload);
        }
    }

    /**
     *    Accessor for total test size in number
     *    of test cases. Null until the first
     *    test is started.
     *
     *    Note: This is not an ideal implementation (and no ideal exists)
     *          as multiple reporters MAY count the same test cases.
     *          Despite this cause for 'overcounting', you may expect
     *          @see getTestCaseProgress() to suffer from the same, so
     *          the progress to total count ratio should still range between
     *          0% and 100%.
     *
     *    @return integer   Total number of cases at start.
     *    @access public
     */
    function getTestCaseCount() {
        $rv = 0;
        for ($i = 0; $i < count($this->reporters); $i++) {
            $rv += $this->reporters[$i]->getTestCaseCount();
        }
        return $rv;
    }

    /**
     *    Accessor for the number of test cases
     *    completed so far.
     *
     *    Note: This is not an ideal implementation (and no ideal exists)
     *          as multiple reporters MAY count the same test cases.
     *          Despite this cause for 'overcounting', you may expect
     *          @see getTestCaseCount() to suffer from the same, so
     *          the progress to total count ratio should still range between
     *          0% and 100%.
     *
     *    @return integer   Number of completed cases.
     *    @access public
     */
    function getTestCaseProgress() {
        $rv = 0;
        for ($i = 0; $i < count($this->reporters); $i++) {
            $rv += $this->reporters[$i]->getTestCaseProgress();
        }
        return $rv;
    }
}
