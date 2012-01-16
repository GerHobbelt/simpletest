<?php
/**
 *  Base include file for SimpleTest
 *  @package    SimpleTest
 *  @subpackage UnitTester
 *  @version    $Id$
 */

/**#@+
 * Includes SimpleTest files and defined the root constant
 * for dependent libraries.
 */
require_once(dirname(__FILE__) . '/invoker.php');
require_once(dirname(__FILE__) . '/errors.php');
require_once(dirname(__FILE__) . '/compatibility.php');
require_once(dirname(__FILE__) . '/scorer.php');
require_once(dirname(__FILE__) . '/expectation.php');
require_once(dirname(__FILE__) . '/dumper.php');
require_once(dirname(__FILE__) . '/simpletest.php');
require_once(dirname(__FILE__) . '/exceptions.php');
require_once(dirname(__FILE__) . '/reflection_php5.php');
require_once(dirname(__FILE__) . '/shell_tester.php');
/**#@-*/
if (! defined('SIMPLE_TEST')) {
    /**
     * @ignore
     */
    define('SIMPLE_TEST', dirname(__FILE__) . DIRECTORY_SEPARATOR);
}

/**
 *    Basic test case. This is the smallest unit of a test
 *    suite. It searches for
 *    all methods that start with the the string "test" and
 *    runs them. Working test cases extend this class.
 *    @package      SimpleTest
 *    @subpackage   UnitTester
 */
class SimpleTestCase {
    protected $label = false;
    protected $reporter;
    protected $observers;
    protected $should_skip = false;
	protected $expect_fail = false;

    /**
     *    Sets up the test with no display.
     *    @param string $label    If no test name is given then
     *                            the class name is used.
     *    @access public
     */
    function __construct($label = false) {
        if ($label) {
            $this->label = $label;
        }
		unset($this->reporter);   // explicitly indicate that this member var is expected to be set/unset.
		$this->observers = array();
    }

    /**
     *    Accessor for the test name for subclasses.
     *    @return string           Name of the test.
     *    @access public
     */
    function getLabel() {
        return $this->label ? $this->label : get_class($this);
    }

    /**
     *    This is a placeholder for skipping tests. In this
     *    method you place skipIf() and skipUnless() calls to
     *    set the skipping state.
     *    @access public
     */
    function skip() {
    }

    /**
     *    Will issue a message to the reporter and tell the test
     *    case to skip if the incoming flag is true.
     *    @param string $should_skip    Condition causing the tests to be skipped.
     *    @param string $message        Text of skip condition.
     *    @access public
     */
    function skipIf($should_skip, $message = '%s') {
        if ($should_skip && ! $this->should_skip) {
            $this->should_skip = true;
            $message = sprintf($message, 'Skipping [' . get_class($this) . ']');
            $this->reporter->paintSkip($message . $this->getAssertionLine());
        }
    }

    /**
     *    Accessor for the protected variable $shoud_skip
     *    @access public
     */
    function shouldSkip() {
        return $this->should_skip;
    }

    /**
     *    Will issue a message to the reporter and tell the test
     *    case to skip if the incoming flag is false.
     *    @param string $shouldnt_skip  Condition causing the tests to be run.
     *    @param string $message        Text of skip condition.
     *    @access public
     */
    function skipUnless($shouldnt_skip, $message = false) {
        $this->skipIf(! $shouldnt_skip, $message);
    }

    /**
     *    Used to invoke the single tests.
     *    @return SimpleInvoker        Individual test runner.
     *    @access public
     */
    function createInvoker() {
        return new SimpleErrorTrappingInvoker(
                new SimpleExceptionTrappingInvoker(new SimpleInvoker($this)));
    }

    /**
     *    Uses reflection to run every method within itself
     *    starting with the string "test" unless a method
     *    is specified.
     *    @param SimpleReporter $reporter    Current test reporter.
     *    @return boolean                    True if all tests passed.
     *    @access public
     */
    function run($reporter) {
        $context = SimpleTest::getContext();
        $context->setTest($this);
        $context->setReporter($reporter);
        $this->reporter = $reporter;
        $started = false;
        foreach ($this->getTests() as $method) {
            if ($reporter->shouldInvoke($this->getLabel(), $method)) {
                $this->skip();
                if ($this->should_skip) {
                    break;
                }
                if (! $started) {
                    $reporter->paintCaseStart($this->getLabel());
                    $started = true;
                }
                $invoker = $this->reporter->createInvoker($this->createInvoker());
                $invoker->before($method);
                $invoker->invoke($method);
                $invoker->after($method);
            }
        }
        if ($started) {
            $reporter->paintCaseEnd($this->getLabel());
        }
        unset($this->reporter);
        //$context->setTest(null);
        return $reporter->getStatus();
    }

    /**
     *    Gets a list of test names. Normally that will
     *    be all internal methods that start with the
     *    name "test". This method should be overridden
     *    if you want a different rule.
     *    @return array        List of test names.
     *    @access public
     */
    function getTests() {
        $methods = array();
        foreach (get_class_methods(get_class($this)) as $method) {
            if ($this->isTest($method)) {
                $methods[] = $method;
            }
        }
        return $methods;
    }

    /**
     *    Tests to see if the method is a test that should
     *    be run. Currently any method that starts with 'test'
     *    is a candidate.
     *    @param string $method        Method name to try.
     *    @return boolean              True if test method.
     *    @access public
     */
    static function isTest($method) {
        return (strtolower(substr($method, 0, 4)) == 'test');
    }

    /**
     *    Announces the start of the test.
     *    @param string $method    Test method just started.
     *    @access public
     */
    function before($method) {
        $this->reporter->paintMethodStart($method);
        $this->observers = array();
    }

    /**
     *    Sets up unit test wide variables at the start
     *    of each test method. To be overridden in
     *    actual user test cases.
     *    @access public
     */
    function setUp() {
    }

    /**
     *    Clears the data set in the setUp() method call.
     *    To be overridden by the user in actual user test cases.
     *    @access public
     */
    function tearDown() {
    }

    /**
     *    Announces the end of the test. Includes private clean up.
     *    @param string $method    Test method just finished.
     *    @access public
     */
    function after($method) {
		if (is_array($this->expect_fail) && !$this->expect_fail['handled']) {
			// uncaught 'expected fail'
            trigger_error("Uncaught 'expected fail': you invoked expectFail() without following it up with an assertion/test.");
		}
        for ($i = 0; $i < count($this->observers); $i++) {
            $this->observers[$i]->atTestEnd($method, $this);
        }
        $this->reporter->paintMethodEnd($method);
		$this->expect_fail = false;
    }

    /**
     *    Sets up an observer for the test end.
     *    @param object $observer    Must have atTestEnd()
     *                               method.
     *    @access public
     */
    function tell($observer) {
        $this->observers[] = $observer;
    }

    /**
     *    @deprecated
     */
    function pass($message = "Pass") {
        if (! isset($this->reporter)) {
            trigger_error('Can only make assertions within test methods');
        }
        $this->reporter->incrementPassCount();
        $this->reporter->paintPass(
                $message . $this->getAssertionLine());
        return true;
    }

    /**
     *    Sends a fail event with a message.
     *    @param string $message        Message to send.
     *    @access public
     */
    function fail($message = "Fail") {
        if (! isset($this->reporter)) {
            trigger_error('Can only make assertions within test methods');
        }
        $this->reporter->incrementFailCount();
        $this->reporter->paintFail(
                $message . $this->getAssertionLine());
        return false;
    }

    /**
     *    Formats a PHP error and dispatches it to the
     *    reporter.
     *    @param integer $severity  PHP error code.
     *    @param string $message    Text of error.
     *    @param string $file       File error occoured in.
     *    @param integer $line      Line number of error.
     *    @access public
     */
    function error($severity, $message, $file, $line) {
        if (! isset($this->reporter)) {
            trigger_error('Can only make assertions within test methods');
        }
        $this->reporter->incrementErrorCount();
        $this->reporter->paintError(
                "Unexpected PHP error [$message] severity [$severity] in [$file line $line]");
    }

    /**
     *    Formats an exception and dispatches it to the
     *    reporter.
     *    @param Exception $exception    Object thrown.
     *    @access public
     */
    function exception($exception) {
        if (! isset($this->reporter)) {
            trigger_error('Can only make assertions within test methods');
        }
        $this->reporter->incrementExceptionCount();
        $this->reporter->paintException($exception);
    }

    /**
     *    For user defined expansion of the available messages.
     *    @param string $type       Tag for sorting the signals.
     *    @param mixed $payload     Extra user specific information.
     */
    function signal($type, $payload) {
        if (! isset($this->reporter)) {
            trigger_error('Can only make assertions within test methods');
        }
        $this->reporter->paintSignal($type, $payload);
    }

	protected function getURLregex() {
		static $uri_re;
		if (empty($uri_re)) {
			$uri_re = '=^(((([a-z][a-z0-9.+-]+:)?//)';               // mandatory scheme + authority start ('//') -- iff authority is specified
			$uri_re .=  '([%!$&\'\(\)*+,;=:a-z0-9_~.-]+@)?';         // optional userinfo
			$uri_re .=  '([%!$&\'\(\)*+,;=:\[\]a-z0-9_~.-]+)';       // authority
			$uri_re .=  '/)|/)?';                                    // making scheme + hier-part optional -- no requirement for FQDNs here
																	 // (Note that we also consume the optional leading / of the path here.)
			$uri_re .=  '((\.+/)*[%!$&\'\(\)*+,;=:@a-z0-9_~-]+[./]+';
			$uri_re .=   '[%!$&\'\(\)*+,;=:@/a-z0-9_~.-]*[%!$&\'\(\)*+,;=:@/a-z0-9_~-])';	
																	 // mandatory path, must be obvious, so must contain at least a dot ...
																	 // ... before the end or a '/' slash beyond the start ...
																	 // ... and definitely no period at the very end
			$uri_re .=  '(\?[%!$&\'\(\)*+,;=:@/?\[\]a-z0-9_~.-]+)?'; // optional query, we accept '[' and ']' in there as well (not in RFC)
			$uri_re .=  '(#[%!$&\'\(\)*+,;=:@/?\[\]a-z0-9_~.-]+)?';	 // optional fragment, we accept '[' and ']' in there as well (not in RFC)
			$uri_re .=  '$=i';
		}
		return $uri_re;
	}
	
	/**
     * The next assert is expected to fail.
     *
     * Use $this->expectedFail()->assert... to mark the assert as an
     * expected fail. 
	 *
	 * You may want to use expectFail() in two different scenarios:
	 *
	 * 1) If your test reveals a bug then use this function with the
     *    relevant parameter to link/refer to your bugtracker. This is 
	 *    necessary because it is much easier to write a test than 
	 *    fix a bug. It's also self-documenting -- before a release, 
	 *    all these expectFail() calls should be removed.
	 *
	 * 2) You want to test the behaviour of SimpleTest itself, including
	 *    how it renders failed tests. This is a special case which 
	 *    applies to all expectFail() calls in the SimpleTest:./test/
	 *    directory.
     *
     * @param $issue
     *   A message describing the known bug or an absolute URL pointing 
	 *   to the issue in any bugtracker.
	 */
    function expectFail($issue = null) {

		if (!empty($issue)) {
			// test if $issue is a URI as per RFC3986; if it is, embed it in an 'expected to fail' message:
			if (preg_match($this->getURLregex(), $issue) == 1) {
				$issue = "%s -> This is expected to fail due to a <a href=\"$issue\">known bug</a>.";
			}
			$this->expect_fail = array('message' => $issue, 'handled' => false);
		}
		else {
			$this->expect_fail = array('message' => '%s -> This is expected to fail.', 'handled' => false);
		}
		return $this;
	}
   
	/**
	 *    Construct 'pass' message. Takes expected fails into account.
	 */
	protected function constructPassMessage($expectation, $compare, $message = "%s") {
		$rv = sprintf($message,
                      $expectation->overlayMessage($compare, $this->getDumper()));
		if (is_array($this->expect_fail)) {
			$rv = sprintf($this->expect_fail['message'], $rv);
		}
		return $rv;
	}	
   
	/**
	 *    Construct 'fail' message. Takes expected fails into account.
	 */
	protected function constructFailMessage($expectation, $compare, $message = "%s") {
		$rv = sprintf($message,
                      $expectation->overlayMessage($compare, $this->getDumper()));
		if (is_array($this->expect_fail)) {
			$rv = sprintf('Unexpected Pass: ' . $this->expect_fail['message'], $rv);
		}
		return $rv;
	}	
	
	/**
	 * Obtain the dumper instance related to this test.
	 */
	public function getDumper() {
		$dumper = null;
		if ($this->reporter) {
			$dumper = $this->reporter->getDumper();
		}
		else if ($context = SimpleTest::getContext()) {
			if ($reporter = $context->getReporter()) {
				$dumper = $reporter->getDumper();
			}
		} 
		if (!$dumper) {
			$dumper = $expectation->getDumper();
		}
		return $dumper;
	}
	
    /**
     *    Runs an expectation directly, taking a possibly expected fail 
	 *    into account by turning the tables then.
     *    @param SimpleExpectation $expectation  Expectation subclass.
     *    @param mixed $compare                  Value to compare.
     *    @return boolean                        True on pass / expected fail, false on fail / unexpected pass.
     *    @access protected
     */
    protected function checkExpectation($expectation, $compare) {
        $rv = $expectation->test($compare);
		if (is_array($this->expect_fail)) {
			$rv = !$rv;
			$this->expect_fail['handled'] = true;
		}
		return $rv;
	}
		
    /**
     *    Runs an expectation directly, for extending the
     *    tests with new expectation classes.
     *    @param SimpleExpectation $expectation  Expectation subclass.
     *    @param mixed $compare               Value to compare.
     *    @param string $message                 Message to display.
     *    @return boolean                        True on pass
     *    @access public
     */
    function assert($expectation, $compare, $message = '%s') {
        if ($this->checkExpectation($expectation, $compare)) {
            return $this->pass($this->constructPassMessage($expectation, $compare, $message));
        } else {
            return $this->fail($this->constructFailMessage($expectation, $compare, $message));
        }
    }

    /**
     *    Uses a stack trace to find the line of an assertion.
     *    @return string           Line number of first assert*
     *                             method embedded in format string.
     *    @access public
     */
    function getAssertionLine() {
        $trace = new SimpleStackTrace(array('assert', 'expect', 'pass', 'fail', 'skip'));
        return $trace->traceMethod();
    }

    /**
     *    Sends a formatted dump of a variable to the
     *    test suite for those emergency debugging
     *    situations.
     *    @param mixed $variable    Variable to display.
     *    @param string $message    Message to display.
     *    @return mixed             The original variable.
     *    @access public
     */
    function dump($variable, $message = false) {
		if ($this->reporter) {
        $dumper = $this->reporter->getDumper();
        $formatted = $dumper->dump($variable);
        if ($message) {
            $formatted = $message . "\n" . $formatted;
        }
        $this->reporter->paintFormattedMessage($formatted);
		}
        return $variable;
    }

    /**
     *    Accessor for the number of subtests including myself.
     *    @return integer           Number of test cases.
     *    @access public
     */
    function getSize() {
        return 1;
    }
}

/**
 *  Helps to extract test cases automatically from a file.
 *    @package      SimpleTest
 *    @subpackage   UnitTester
 */
class SimpleFileLoader {

    /**
     *    Builds a test suite from a library of test cases.
     *    The new suite is composed into this one.
     *    @param string $test_file        File name of library with
     *                                    test case classes.
     *    @return TestSuite               The new test suite.
     *    @access public
     */
    function load($test_file) {
        $existing_classes = get_declared_classes();
        $existing_globals = get_defined_vars();
		// as the included file can contain errors, we don't want to crash, but report those instead!
		//
		// See also: http://us3.php.net/manual/en/function.php-check-syntax.php
		//
		// NOTE: you can also load the file content and then pull it through eval(): that one though will 
		//       NOT report the parse error, only return FALSE  ( http://nl.php.net/manual/en/function.eval.php )
		$parse_err = -1;
		if (is_readable($test_file)) {
			$code = @file_get_contents($test_file);
			if ($code === false) {
				return new BadTestSuite($test_file, "Could not load the contents of the file");
			}
			
			$shell = new SimpleShell();
			$parse_err = $shell->execute('php -l "' . realpath($test_file) . '"');
			if ($parse_err) {
				// either we're not being allowed to run a php cli, or we got an actual parse error: find out which it is
				$out = $shell->getOutput();
				if (strpos($out, 'syntax error') !== false) {
					return new BadTestSuite($test_file, "There is a SYNTAX ERROR in the file:\n" . trim($out));
				}
				/*
				 * ELSE: seems we weren't able to run the php cli; we cannot fall back to the eval() way of checking the code
				 * as that would also /execute/ the code, which is sorta okay, apart from the fact that the code-under-test
				 * may collide with the simpletest code itself (e.g. when simpletest is used to test itself), resulting in
				 * eval failures such as 'cannot redefine class', while the code to test is perfectly fine.
				 *
				 * We can, however, use runkit_lint_file(), IFF it exists in our PHP install...
				 */
				if (function_exists('runkit_lint_file')) 
				{
					$ret = runkit_lint_file($test_file);
					if ($ret === false)
					{
						// to display the code causing the error: $code = htmlentities($code, ENT_NOQUOTES);
						return new BadTestSuite($test_file, "There is a SYNTAX ERROR ($php_errormsg) in the file. Besides, you should adjust your setup so we can invoke 'php -l' as that gives you much more info about this error than a mere 'syntax error'.");
					}
				}
			}
			
			include_once($test_file);		// or should this really be 'include' instead of 'include_once'?
		}
		else {
            return new BadTestSuite($test_file, "You don't have read access to the file");
		}
        $new_globals = get_defined_vars();
        $this->makeFileVariablesGlobal($existing_globals, $new_globals);
        $new_classes = array_diff(get_declared_classes(), $existing_classes);
        if (empty($new_classes)) {
            $new_classes = $this->scrapeClassesFromFile($test_file);
        }
        $classes = $this->selectRunnableTests($new_classes);
        return $this->createSuiteFromClasses($test_file, $classes);
    }

    /**
     *    Imports new variables into the global namespace.
     *    @param hash $existing   Variables before the file was loaded.
     *    @param hash $new        Variables after the file was loaded.
     *    @access protected
     */
    protected function makeFileVariablesGlobal($existing, $new) {
        $globals = array_diff(array_keys($new), array_keys($existing));
        foreach ($globals as $global) {
            $GLOBALS[$global] = $new[$global];
        }
    }

    /**
     *    Lookup classnames from file contents, in case the
     *    file may have been included before.
     *    Note: This is probably too clever by half. Figuring this
     *    out after a failed test case is going to be tricky for us,
     *    never mind the user. A test case should not be included
     *    twice anyway.
     *    @param string $test_file        File name with classes.
     *    @access protected
     */
    protected function scrapeClassesFromFile($test_file) {
        preg_match_all('~^\s*class\s+(\w+)(\s+(extends|implements)\s+\w+)*\s*\{~mi',
                        file_get_contents($test_file),
                        $matches );
        return $matches[1];
    }

    /**
     *    Calculates the incoming test cases. Skips abstract
     *    and ignored classes.
     *    @param array $candidates   Candidate classes.
     *    @return array              New classes which are test
     *                               cases that shouldn't be ignored.
     *    @access public
     */
    function selectRunnableTests($candidates) {
        $classes = array();
        foreach ($candidates as $class) {
            if (TestSuite::getBaseTestCase($class)) {
                $reflection = new SimpleReflection($class);
                if ($reflection->isAbstract()) {
                    SimpleTest::ignore($class);
                } else {
					// only pick classes which do have test methods we can run:
					$methods = $reflection->getMethods();
					foreach($methods as $method) {
						if (SimpleTestCase::isTest($method))
						{
							$classes[] = $class;
							break;
						}
					}
                }
            }
        }
        return $classes;
    }

    /**
     *    Builds a test suite from a class list.
     *    @param string $title       Title of new group.
     *    @param array $classes      Test classes.
     *    @return TestSuite          Group loaded with the new
     *                               test cases.
     *    @access public
     */
    function createSuiteFromClasses($title, $classes) {
        if (count($classes) == 0) {
            $suite = new BadTestSuite($title, "No runnable test cases in [$title]");
            return $suite;
        }
        SimpleTest::ignoreParentsIfIgnored($classes);
        $suite = new TestSuite($title);
        foreach ($classes as $class) {
            if (! SimpleTest::isIgnored($class)) {
                $suite->add($class);
            }
        }
        return $suite;
    }
}

/**
 *    This is a composite test class for combining
 *    test cases and other RunnableTest classes into
 *    a group test.
 *    @package      SimpleTest
 *    @subpackage   UnitTester
 */
class TestSuite {
    protected $label;
    protected $test_cases;

    /**
     *    Sets the name of the test suite.
     *    @param string $label    Name sent at the start and end
     *                            of the test.
     *    @access public
     */
    function __construct($label = false) {
        $this->label = $label;
        $this->test_cases = array();
    }

    /**
     *    Accessor for the test name for subclasses. If the suite
     *    wraps a single test case the label defaults to the name of that test.
     *    @return string           Name of the test.
     *    @access public
     */
    function getLabel() {
        if (! $this->label) {
            return ($this->getSize() == 1) ?
                    get_class($this->test_cases[0]) : get_class($this);
        } else {
            return $this->label;
        }
    }

    /**
     *    Adds a test into the suite by instance or class. The class will
     *    be instantiated if it's a test suite.
     *    @param SimpleTestCase $test_case  Suite or individual test
     *                                      case implementing the
     *                                      runnable test interface.
     *    @access public
     */
    function add($test_case) {
        if (! is_string($test_case)) {
            $this->test_cases[] = $test_case;
        } elseif (TestSuite::getBaseTestCase($test_case) == 'testsuite') {
            $this->test_cases[] = new $test_case();
        } else {
            $this->test_cases[] = $test_case;
        }
    }

    /**
     *    Builds a test suite from a library of test cases.
     *    The new suite is composed into this one.
     *    @param string $test_file        File name of library with
     *                                    test case classes.
     *    @access public
     */
    function addFile($test_file) {
        $extractor = new SimpleFileLoader();
        $this->add($extractor->load($test_file));
    }

    /**
     *    Delegates to a visiting collector to add test
     *    files.
     *    @param string $path                  Path to scan from.
     *    @param SimpleCollector $collector    Directory scanner.
     *    @access public
     */
    function collect($path, $collector) {
        $collector->collect($this, $path);
    }

    /**
     *    Invokes run() on all of the held test cases, instantiating
     *    them if necessary.
     *    @param SimpleReporter $reporter    Current test reporter.
     *    @access public
     */
    function run($reporter) {
        $reporter->paintGroupStart($this->getLabel(), $this->getSize());
        for ($i = 0, $count = count($this->test_cases); $i < $count; $i++) {
            if (is_string($this->test_cases[$i])) {
                $class = $this->test_cases[$i];
                $test = new $class();
                $test->run($reporter);
                unset($test);
            } else {
                $this->test_cases[$i]->run($reporter);
            }
        }
        $reporter->paintGroupEnd($this->getLabel());
        return $reporter->getStatus();
    }

    /**
     *    Number of contained test cases.
     *    @return integer     Total count of cases in the group.
     *    @access public
     */
    function getSize() {
        $count = 0;
        foreach ($this->test_cases as $case) {
            if (is_string($case)) {
                if (! SimpleTest::isIgnored($case)) {
                    $count++;
                }
            } else {
                $count += $case->getSize();
            }
        }
        return $count;
    }

    /**
     *    Test to see if a class is derived from the
     *    SimpleTestCase class.
     *    @param string $class     Class name.
     *    @access public
     */
    static function getBaseTestCase($class) {
        while ($class = get_parent_class($class)) {
            $class = strtolower($class);
            if ($class == 'simpletestcase' || $class == 'testsuite') {
                return $class;
            }
        }
        return false;
    }
}

/**
 *    This is a failing group test for when a test suite hasn't
 *    loaded properly.
 *    @package      SimpleTest
 *    @subpackage   UnitTester
 */
class BadTestSuite {
    protected $label;
    protected $error;

    /**
     *    Sets the name of the test suite and error message.
     *    @param string $label    Name sent at the start and end
     *                            of the test.
     *    @access public
     */
    function __construct($label, $error) {
        $this->label = $label;
        $this->error = $error;
    }

    /**
     *    Accessor for the test name for subclasses.
     *    @return string           Name of the test.
     *    @access public
     */
    function getLabel() {
        return $this->label;
    }

    /**
     *    Sends a single error to the reporter.
     *    @param SimpleReporter $reporter    Current test reporter.
     *    @access public
     */
    function run($reporter) {
        $reporter->paintGroupStart($this->getLabel(), $this->getSize());
        $reporter->incrementFailCount();
        $reporter->paintFail('Bad TestSuite [' . $this->getLabel() .
                '] with error [' . $this->error . ']');
        $reporter->paintGroupEnd($this->getLabel());
        return $reporter->getStatus();
    }

    /**
     *    Number of contained test cases. Always zero.
     *    @return integer     Total count of cases in the group.
     *    @access public
     */
    function getSize() {
        return 0;
    }
}

