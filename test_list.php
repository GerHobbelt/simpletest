<?php
/**
 *  base include file for 'list' listing of tests feature for SimpleTest
 *  @package    SimpleTest
 *  @subpackage UnitTester
 *  @version    $Id$
 */

/**#@+
 * Includes SimpleTest files.
 */
require_once dirname(__FILE__) . '/invoker.php';
require_once dirname(__FILE__) . '/scorer.php';
require_once dirname(__FILE__) . '/expectation.php';
/**#@-*/

/**
 *    Wrapper for providing a custom @see ListInvoker 
 *    which is used to list the tests / cases invoked.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class ListTestReporter extends SimpleReporterDecorator {
	protected $make_list;
	
    /**
     *    Set up a ListTestReporter instance.
     *    @param SimpleScorer $reporter       Reporter to receive events.
     */
    function __construct($reporter) {
        parent::__construct($reporter);
		$this->make_list = false;
    }

	/**
	 *    Signals that the next run will list the tests.
     *    @param boolean $do_list             run will show Tests' names if true.
     *    @return SimpleReporterDecorator     This instance.
     *    @access public
	 */
	function makeList($do_list = true) {
		$this->make_list = $do_list;
        $this->reporter->makeList($do_list);
		return $this;
	}

    /**
     *    Can wrap the invoker when listing the tests is enabled.
     *    @param SimpleInvoker $invoker   Individual test runner.
     *    @return SimpleInvoker           Wrapped test runner.
     *    @access public
     */
    function createInvoker($invoker) {
		if ($this->make_list) {
			return new ListTestInvoker($this->reporter->createInvoker($invoker));
		}
		else {
			return $this->reporter->createInvoker($invoker);
		}
    }
}

/**
 *    Invoker decorator for 'list' feature which lists the tests / cases on the display.
 *    @package    SimpleTest
 *    @subpackage UnitTester
 */
class ListTestInvoker extends SimpleInvokerDecorator {

    /**
     *    Set up the ListTestInvoker.
     *    @param SimpleInvoker $invoker      Chained test method runner.
     *    @access public
     */
    function __construct($invoker) {
        parent::__construct($invoker);
    }

    /**
     *    Lists rather then invokes a test method. Consequently,
	 *    we do not invoke the setUp() and tearDown() calls either.
     *    @param string $method    Test method to call.
     *    @access public
     */
    function invoke($method) {
        $context = SimpleTest::getContext();
        $test = $this->getTestCase(); // $context->getTest();
        $reporter = $context->getReporter();
		// use 'user signal' as the way to render the list entry:
		$case = get_class($test);
		if ($case != $test->getLabel()) {
			$case = sprintf("%s [%s]", $case, $test->getLabel());
		}
		$show_breadcrumb = $reporter->includeBreadCrumb(false);
		$reporter->paintSignal("Test", sprintf("Case: %s | Method: %s", $case, $method));
		$reporter->includeBreadCrumb($show_breadcrumb);
    }
}
