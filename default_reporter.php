<?php
/**
 *  Optional include file for SimpleTest
 *  @package    SimpleTest
 *  @subpackage UnitTester
 *  @version    $Id$
 */

/**#@+
 *  include other SimpleTest class files
 */
require_once(dirname(__FILE__) . '/simpletest.php');
require_once(dirname(__FILE__) . '/scorer.php');
require_once(dirname(__FILE__) . '/reporter.php');
require_once(dirname(__FILE__) . '/xml.php');
require_once(dirname(__FILE__) . '/test_list.php');
/**#@-*/

/**
 *    Parser for command line arguments. Extracts
 *    the a specific test to run and engages XML
 *    reporting when necessary.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class SimpleCommandLineParser {
    protected $to_property = array(
            'case' => 'case', 'c' => 'case', 'class' => 'case',
            'test' => 'test', 't' => 'test', 'method' => 'test',
    );
    protected $case = '';
    protected $test = '';
    protected $xml = false;
    protected $dry = false;
    protected $make_list = false;
    protected $pass = false;
    protected $help = false;
    protected $no_skips = false;
	protected $breadcrumb = true;
	protected $stacktrace = true;

    /**
     *    Parses raw command line arguments into object properties.
     *    @param array $arguments        Raw commend line arguments.
     */
    function __construct($arguments) {
        if (! is_array($arguments)) {
            return;
        }
		$arguments = array_merge(array(), $arguments); // clone array, so we can edit it locally at no risk.
        foreach ($arguments as $i => $argument) {
            if (preg_match('/^--?(test|case|class|method|t|c)=(.+)$/', $argument, $matches)) {
                $property = $this->to_property[$matches[1]];
                $this->$property = $matches[2];
				unset($arguments[$i]);
            } elseif (preg_match('/^--?(test|case|class|method|t|c)$/', $argument, $matches)) {
                $property = $this->to_property[$matches[1]];
                if (isset($arguments[$i + 1])) {
                    $this->$property = $arguments[$i + 1];
					unset($arguments[$i + 1]);
                }
				unset($arguments[$i]);
            } elseif (preg_match('/^--?(xml|x)$/', $argument)) {
                $this->xml = true;
				unset($arguments[$i]);
            } elseif (preg_match('/^--?(dry|n)$/', $argument)) {
                $this->dry = true;
				unset($arguments[$i]);
            } elseif (preg_match('/^--?(list|l)$/', $argument)) {
                $this->make_list = true;
				unset($arguments[$i]);
            } elseif (preg_match('/^--?(no-breadcrumb|nb)$/', $argument)) {
                $this->breadcrumb = false;
				unset($arguments[$i]);
            } elseif (preg_match('/^--?(no-stacktrace|nt)$/', $argument)) {
                $this->stacktrace = false;
				unset($arguments[$i]);
            } elseif (preg_match('/^--?(show-pass|pass|p)$/', $argument)) {
                $this->pass = true;
				unset($arguments[$i]);
            } elseif (preg_match('/^--?(no-skip|no-skips|s)$/', $argument)) {
                $this->no_skips = true;
				unset($arguments[$i]);
            } elseif (preg_match('/^--?(help|h)$/', $argument)) {
                $this->help = true;
				unset($arguments[$i]);
            }
        }
	
		// TODO: print error message about unrecognized commandline arguments:
        foreach ($arguments as $i => $argument) {
            $this->help = true;
		}
    }

    /**
     *    Run only this test.
     *    @return string        Test name to run.
     */
    function getTest() {
        return $this->test;
    }

    /**
     *    Run only this test suite.
     *    @return string        Test class name to run.
     */
    function getTestCase() {
        return $this->case;
    }

    /**
     *    Output should be XML or not.
     *    @return boolean        True if XML desired.
     */
    function isXml() {
        return $this->xml;
    }

    /**
     *    Tests should be a dry run or not.
     *    @return boolean        True if a 'dry run' is desired.
     */
    function isDryRun() {
        return $this->dry;
    }

    /**
     *    Tests should be listed rather than run.
     *    @return boolean        True if a 'list run' is desired.
     */
    function isListRun() {
        return $this->make_list;
    }

    /**
     *    Output should include pass messages.
     *    @return boolean        True for pass message inclusion.
     */
    function showPasses() {
        return $this->pass;
    }

    /**
     *    Output should include 'breadcrumb' invocation chains when available.
     *    @return boolean        True for breadcrumb inclusion.
     */
    function showBreadCrumb() {
        return $this->breadcrumb;
    }

    /**
     *    Output should include stack traces when available.
     *    @return boolean        True for stack trace inclusion.
     */
    function showStackTrace() {
        return $this->stacktrace;
    }

    /**
     *    Output should suppress skip messages.
     *    @return boolean        True for no skips.
     */
    function noSkips() {
        return $this->no_skips;
    }

    /**
     *    Output should be a help message. Disabled during XML mode.
     *    @return boolean        True if help message desired.
     */
    function help() {
        return $this->help && ! $this->xml;
    }

    /**
     *    Returns plain-text help message for command line runner.
     *    @return string         String help message
     */
    function getHelpText() {
        return <<<HELP
SimpleTest command line default reporter (autorun)
Usage: php <test_file> [args...]

    --case=<class>
    --class=<class>
    -c <class>      Run only the test-case <class>
	
	--test=<method>
	--method=<method>
    -t <method>     Run only the test method <method>
	
	--no-skip
    -s              Suppress skip messages
	
	--xml
    -x              Return test results in XML
	
	--dry
	-n              Request a dry run
	
	--list
	-l              Request a list of the tests (cases / methods)
	
	--show-pass
	-p              Show pass messages too

	--no-breadcrumb
	-nb             Do not show breadcrumbs in the test results

	--no-stacktrace
	-nt             Do not show stack traces in the (failed) test results

	--help
    -h              Display this help message

HELP;
    }
}


class WebCommandLineParser extends SimpleCommandLineParser {
    /**
     *    Parses the GET/POST request data to detect properties we are interested in.
     *    @param array $arguments        Raw set of GET/POST/... items
     */
    function __construct($arguments) {
        if (! is_array($arguments)) {
            return;
        }
		$arguments = array_merge(array(), $arguments); // clone array, so we can edit it locally at no risk.
        foreach ($arguments as $i => $argument) {
            if (preg_match('/^(test|case|class|method|t|c)$/', $i)) {
                $property = $this->to_property[$i];
                $this->$property = $argument;
				unset($arguments[$i]);
            } elseif (preg_match('/^(xml|x)$/', $i)) {
                $this->xml = $this->convertValueToBoolean($argument);
				unset($arguments[$i]);
            } elseif (preg_match('/^(dry|n)$/', $i)) {
                $this->dry = $this->convertValueToBoolean($argument);
				unset($arguments[$i]);
            } elseif (preg_match('/^(list|l)$/', $i)) {
                $this->make_list = $this->convertValueToBoolean($argument);
				unset($arguments[$i]);
            } elseif (preg_match('/^(no-breadcrumb|nb)$/', $i)) {
                $this->breadcrumb = !$this->convertValueToBoolean($argument);
				unset($arguments[$i]);
            } elseif (preg_match('/^(no-stacktrace|nt)$/', $i)) {
                $this->stacktrace = !$this->convertValueToBoolean($argument);
				unset($arguments[$i]);
            } elseif (preg_match('/^(show-pass|pass|p)$/', $i)) {
                $this->pass = $this->convertValueToBoolean($argument);
				unset($arguments[$i]);
            } elseif (preg_match('/^(no-skip|no-skips|s)$/', $i)) {
                $this->no_skips = $this->convertValueToBoolean($argument);
				unset($arguments[$i]);
            } elseif (preg_match('/^(help|h)$/', $i)) {
                $this->help = $this->convertValueToBoolean($argument);
				unset($arguments[$i]);
            }
        }
	
		// TODO: print error message about unrecognized request arguments:
        foreach ($arguments as $i => $argument) {
            $this->help = true;
		}
    }

	protected function convertValueToBoolean($arg) {
		$arg = strval($arg);
		if (!empty($arg) && (strpos('JTYjty', $arg[0]) !== false || intval($arg, 10) != 0)) {
			return true;
		}
		if ($arg === '') {
			return true;
		}
		return false;
	}

    /**
     *    Returns plain-text help message for command line runner.
     *    @return string         String help message
     */
    function getHelpText() {
        return <<<HELP
		
<div class="help-message">
	<h1>Request parameters recognized by SimpleTest (autorun)</h1>
	<dl>
		<dt>case=<var>class</var></dt>
		<dt>class=<var>class</var></dt>
		<dt>c=<var>class</var></dt>
		<dd>Run only the test-case <var>class</var></dd>

		<dt>test=<var>method</var></dt>
		<dt>method=<var>method</var></dt>
		<dt>t=<var>method</var></dt>
		<dd>Run only the test method <var>method</var></dd>
			
		<dt>no-skip</dt>
		<dt>s</dt>
		<dd>Suppress skip messages</dd>
			
		<dt>xml</dt>
		<dt>x</dt>
		<dd>Return test results in XML</dd>

		<dt>dry</dt>
		<dt>n</dt>
		<dd>Request a dry run</dd>

		<dt>list</dt>
		<dt>l</dt>
		<dd>Request a list of the tests (cases / methods)</dd>

		<dt>show-pass</dt>
		<dt>pass</dt>
		<dt>p</dt>
		<dd>Show pass messages too</dd>

		<dt>no-breadcrumb</dt>
		<dt>nb</dt>
		<dd>Do not show breadcrumbs in the test results</dd>

		<dt>no-stacktrace</dt>
		<dt>nt</dt>
		<dd>Do not show stack traces in the (failed) test results</dd>
		
		<dt>help</dt>
		<dt>h</dt>
		<dd>Display this help message</dd>
	</dl>

	<p>Note that you can switch any of the boolean options ON or OFF by providing a 'true' (T/J/Y/non zero integer number)
	or 'false' (F/N/0) value. <em>No value</em> specified implies 'true'.</p>
</div>

HELP;
    }
}




/**
 *    The default reporter used by SimpleTest's autorun
 *    feature. The actual reporters used are dependency
 *    injected and can be overridden.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class DefaultReporter extends SimpleReporterDecorator {

    /**
     *  Assembles the appropriate reporter for the environment.
     */
    function __construct() {
		$in_cli = SimpleReporter::inCli();
		
        if ($in_cli) {
            $parser = new SimpleCommandLineParser($_SERVER['argv']);
		}
		else {
            $parser = new WebCommandLineParser(array_merge(array(), (isset($_GET) && is_array($_GET) ? $_GET : array()), (isset($_POST) && is_array($_POST) ? $_POST : array())));
		}
        $interfaces = ($parser->isXml() ? array('XmlReporter') : ($in_cli ? array('TextReporter') : array('HtmlReporter')));
		if ($parser->help()) {
			// I'm not sure if we should do the echo'ing here -- ezyang
			echo $parser->getHelpText();
			exit(1);
		}
		$reporter = new SelectiveReporter(
				SimpleTest::preferred($interfaces),
				$parser->getTestCase(),
				$parser->getTest());
		if ($parser->noSkips()) {
			$reporter = new NoSkipsReporter($reporter);
		}
		if (!$parser->showPasses()) {
			$reporter = new NoPassesReporter($reporter);
		}
		$reporter = new ListTestReporter($reporter);
        parent::__construct($reporter);
		$this->makeDry($parser->isDryRun());
		$this->makeList($parser->isListRun());
		$this->includeBreadCrumb($parser->showBreadCrumb());
		$this->includeStackTrace($parser->showStackTrace());
    }
}
