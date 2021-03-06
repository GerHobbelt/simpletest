<?php
/**
 *  base include file for SimpleTest
 *  @package    SimpleTest
 *  @subpackage UnitTester
 *  @version    $Id$
 */

/**#@+
 *  include other SimpleTest class files
 */
require_once(dirname(__FILE__) . '/scorer.php');
//require_once(dirname(__FILE__) . '/arguments.php');
/**#@-*/

/**
 *    Sample minimal test displayer. Generates only
 *    failure messages and a pass count.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class HtmlReporter extends SimpleReporter {
    protected $character_set;

    /**
     *    Does nothing yet. The first output will
     *    be sent on the first test start. For use
     *    by a web browser.
     *    @access public
     */
    function __construct($character_set = 'UTF-8') {
        parent::__construct();
        $this->character_set = $character_set;
    }

    /**
     *    Paints the top of the web page setting the
     *    title to the name of the starting test.
     *    @param string $test_name      Name class of test.
     *    @access public
     */
    function paintHeader($test_name) {
        $this->sendNoCacheHeaders();
        /* only transmit the HTML blurb when there hasn't been transmitted other content before us */
        if (!headers_sent()) {
            print "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">";
            print "<html>\n<head>\n<title>$test_name</title>\n";
            print "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=" .
                    $this->character_set . "\">\n";
            print "<style type=\"text/css\">\n";
            print $this->getCss() . "\n";
            print "</style>\n";
            print "</head>\n<body>\n";
            print "<h1>$test_name</h1>\n";
            flush();
        }
    }

    /**
     *    Send the headers necessary to ensure the page is
     *    reloaded on every request. Otherwise you could be
     *    scratching your head over out of date test data.
     *    @access public
     */
    static function sendNoCacheHeaders() {
        if (! headers_sent()) {
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");
        }
    }

    /**
     *    Paints the CSS. Add additional styles here.
     *    @return string            CSS code as text.
     *    @access protected
     */
    protected function getCss() {
        return "\n" .
               "span.fail { background-color: inherit; color: red; }\n" .
               "span.pass { background-color: inherit; color: darkgreen; }\n" .
               "span.skip { background-color: inherit; color: darkgreen; }\n" .
               "span.error { background-color: inherit; color: red; }\n" .
               "span.exception { background-color: inherit; color: red; }\n" .
               "span.signal { background-color: inherit; color: orange; }\n" .
               "pre { background-color: lightgray; color: inherit; }\n";
    }

    /**
     *    Paints the end of the test with a summary of
     *    the passes and failures.
     *    @param string $test_name        Name class of test.
     *    @access public
     */
    function paintFooter($test_name) {
        $colour = ($this->getFailCount() + $this->getExceptionCount() > 0 ? "red" : "green");
        print "<div style=\"";
        print "padding: 8px; margin-top: 1em; background-color: $colour; color: white;";
        print "\">";
        print $this->getTestCaseProgress() . "/" . $this->getTestCaseCount();
        print " test cases complete:\n";
        print "<strong>" . $this->getPassCount() . "</strong> passes, ";
        print "<strong>" . $this->getFailCount() . "</strong> fails and ";
        print "<strong>" . $this->getExceptionCount() . "</strong> exceptions.";
        print "</div>\n";
        print "</body>\n</html>\n";
    }

    /**
     *    Paints the test success (pass) with a breadcrumbs
     *    trail of the nesting test suites below the
     *    top level test.
     *
     *    Note: Will only render a message when the SIMPLETEST_PAINT_PASS flag
     *          has been set.
     *
     *    @param string $message    Pass message displayed in
     *                              the context of the other tests.
     */
    function paintPass($message) {
        parent::paintPass($message);
        print "<p class=\"st-paint pass\"><span class=\"pass\">Pass</span>: ";
        if ($this->includeBreadCrumb()) {
            $breadcrumb = $this->getTestList();
            array_shift($breadcrumb);
            print implode(" -&gt; ", $breadcrumb);
            print " -&gt; ";
        }
        print $this->htmlEntities($message) . "</p>\n";
    }

    /**
     *    Paints the test failure with a breadcrumbs
     *    trail of the nesting test suites below the
     *    top level test.
     *    @param string $message    Failure message displayed in
     *                              the context of the other tests.
     */
    function paintFail($message) {
        parent::paintFail($message);
        print "<p class=\"st-paint fail\"><span class=\"fail\">Fail</span>: ";
        if ($this->includeBreadCrumb()) {
            $breadcrumb = $this->getTestList();
            array_shift($breadcrumb);
            print implode(" -&gt; ", $breadcrumb);
            print " -&gt; ";
        }
        print $this->htmlEntities($message) . "</p>\n";
        if ($this->includeStackTrace()) {
            print "<pre>\n";
            debug_print_backtrace();
            print "</pre>\n";
        }
    }

    /**
     *    Paints a PHP error.
     *    @param string $message        Message is ignored.
     *    @access public
     */
    function paintError($message) {
        parent::paintError($message);
        print "<p class=\"st-paint error\"><span class=\"error\">Exception</span>: ";
        if ($this->includeBreadCrumb()) {
            $breadcrumb = $this->getTestList();
            array_shift($breadcrumb);
            print implode(" -&gt; ", $breadcrumb);
            print " -&gt; ";
        }
        print "<strong>" . $this->htmlEntities($message) . "</strong></p>\n";
    }

    /**
     *    Paints a PHP exception.
     *    @param Exception $exception        Exception to display.
     *    @access public
     */
    function paintException($exception) {
        parent::paintException($exception);
        print "<p class=\"st-paint exception\"><span class=\"exception\">Exception</span>: ";
        if ($this->includeBreadCrumb()) {
            $breadcrumb = $this->getTestList();
            array_shift($breadcrumb);
            print implode(" -&gt; ", $breadcrumb);
            print " -&gt; ";
        }
        $message = 'Unexpected exception of type [' . get_class($exception) .
                '] with message ['. $exception->getMessage() .
                '] in ['. $exception->getFile() .
                ' line ' . $exception->getLine() . ']';
        print "<strong>" . $this->htmlEntities($message) . "</strong></p>\n";
    }

    /**
     *    Prints the message for skipping tests.
     *    @param string $message    Text of skip condition.
     *    @access public
     */
    function paintSkip($message) {
        parent::paintSkip($message);
        print "<p class=\"st-paint skip\"><span class=\"skip\">Skipped</span>: ";
        if ($this->includeBreadCrumb()) {
            $breadcrumb = $this->getTestList();
            array_shift($breadcrumb);
            print implode(" -&gt; ", $breadcrumb);
            print " -&gt; ";
        }
        print $this->htmlEntities($message) . "</p>\n";
    }

    /**
     *    Prints the message for user generated events (SimpleTestCase::signal()).
     *    @param string $type        Event type as text.
     *    @param mixed $payload      Message or object.
     *    @access public
     */
    function paintSignal($type, $payload) {
        parent::paintSignal($type, $payload);
        print "<p class=\"st-paint signal\"><span class=\"signal\">$type</span>: ";
        if ($this->includeBreadCrumb()) {
            $breadcrumb = $this->getTestList();
            array_shift($breadcrumb);
            print implode(" -&gt; ", $breadcrumb);
            print " -&gt; ";
        }
        print $this->htmlEntities($this->serializePayload($payload)) . "</p>\n";
    }

    /**
     *    Paints message text such as user messages.
     *    @param string $message        Text to show.
     *    @access public
     */
    function paintMessage($message) {
        parent::paintMessage($message);
        print '<p class="st-message">' . $this->htmlEntities($message) . '</p>';
    }

    /**
     *    Paints formatted text such as dumped variables.
     *    @param string $message        Text to show.
     *    @access public
     */
    function paintFormattedMessage($message) {
        parent::paintFormattedMessage($message);
        print '<pre class="st-fmtd-message">' . $this->htmlEntities($message) . '</pre>';
    }

    /**
     *    Character set adjusted entity conversion.
     *    @param string $message    Plain text or Unicode message.
     *    @return string            Browser readable message.
     *    @access protected
     */
    protected function htmlEntities($message) {
        $message = rtrim($message);
        $message = ltrim($message, "\n\r\x0B");
        $message = htmlentities($message, ENT_COMPAT | ENT_DISALLOWED, $this->character_set, true /* double encode */);
        //$message = str_replace("\n", "\n<br />", $message);
        return $message;
    }
}

/**
 *    Sample minimal test displayer. Generates only
 *    failure messages and a pass count. For command
 *    line use. I've tried to make it look like JUnit,
 *    but I wanted to output the errors as they arrived
 *    which meant dropping the dots.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class TextReporter extends SimpleReporter {

    /**
     *    Does nothing yet. The first output will
     *    be sent on the first test start.
     */
    function __construct() {
        parent::__construct();
    }

    /**
     *    Paints the title only.
     *    @param string $test_name        Name class of test.
     *    @access public
     */
    function paintHeader($test_name) {
        if (! SimpleReporter::inCli()) {
            header('Content-type: text/plain');
        }
        print "$test_name\n";
        flush();
    }

    /**
     *    Paints the end of the test with a summary of
     *    the passes and failures.
     *    @param string $test_name        Name class of test.
     *    @access public
     */
    function paintFooter($test_name) {
        if ($this->getFailCount() + $this->getExceptionCount() == 0) {
            print "OK\n";
        } else {
            print "FAILURES!!!\n";
        }
        print "Test cases run: " . $this->getTestCaseProgress() .
                "/" . $this->getTestCaseCount() .
                ", Passes: " . $this->getPassCount() .
                ", Failures: " . $this->getFailCount() .
                ", Exceptions: " . $this->getExceptionCount() . "\n";
    }

    /**
     *    Paints the test success as a stack trace.
     *    @param string $message    Success message displayed in
     *                              the context of the other tests.
     *    @access public
     */
    function paintPass($message) {
        parent::paintPass($message);
        print $this->getPassCount() . ") $message\n";
        if ($this->includeBreadCrumb()) {
            $breadcrumb = $this->getTestList();
            array_shift($breadcrumb);
            print "\tin " . implode("\n\tin ", array_reverse($breadcrumb));
            print "\n";
        }
    }

    /**
     *    Paints the test failure as a stack trace.
     *    @param string $message    Failure message displayed in
     *                              the context of the other tests.
     *    @access public
     */
    function paintFail($message) {
        parent::paintFail($message);
        print $this->getFailCount() . ") $message\n";
        if ($this->includeBreadCrumb()) {
            $breadcrumb = $this->getTestList();
            array_shift($breadcrumb);
            print "\tin " . implode("\n\tin ", array_reverse($breadcrumb));
            print "\n";
        }
    }

    /**
     *    Paints a PHP error or exception.
     *    @param string $message        Message to be shown.
     *    @access public
     *    @abstract
     */
    function paintError($message) {
        parent::paintError($message);
        print "Exception " . $this->getExceptionCount() . "!\n$message\n";
        if ($this->includeBreadCrumb()) {
            $breadcrumb = $this->getTestList();
            array_shift($breadcrumb);
            print "\tin " . implode("\n\tin ", array_reverse($breadcrumb));
            print "\n";
        }
    }

    /**
     *    Paints a PHP error or exception.
     *    @param Exception $exception      Exception to describe.
     *    @access public
     *    @abstract
     */
    function paintException($exception) {
        parent::paintException($exception);
        $message = 'Unexpected exception of type [' . get_class($exception) .
                '] with message ['. $exception->getMessage() .
                '] in ['. $exception->getFile() .
                ' line ' . $exception->getLine() . ']';
        print "Exception " . $this->getExceptionCount() . "!\n$message\n";
        if ($this->includeBreadCrumb()) {
            $breadcrumb = $this->getTestList();
            array_shift($breadcrumb);
            print "\tin " . implode("\n\tin ", array_reverse($breadcrumb));
            print "\n";
        }
    }

    /**
     *    Prints the message for user generated events (SimpleTestCase::signal()).
     *    @param string $type        Event type as text.
     *    @param mixed $payload      Message or object.
     *    @access public
     */
    function paintSignal($type, $payload) {
        parent::paintSignal($type, $payload);
        print $type . ': ' . $this->serializePayload($payload) . "\n";
        if ($this->includeBreadCrumb()) {
            $breadcrumb = $this->getTestList();
            array_shift($breadcrumb);
            print "\tin " . implode("\n\tin ", array_reverse($breadcrumb));
            print "\n";
        }
    }

    /**
     *    Prints the message for skipping tests.
     *    @param string $message    Text of skip condition.
     *    @access public
     */
    function paintSkip($message) {
        parent::paintSkip($message);
        print "Skip: $message\n";
    }

    /**
     *    Paints message text such as user messages.
     *    @param string $message        Text to show.
     *    @access public
     */
    function paintMessage($message) {
        parent::paintMessage($message);
        print "$message\n";
    }

    /**
     *    Paints formatted text such as dumped variables.
     *    @param string $message        Text to show.
     *    @access public
     */
    function paintFormattedMessage($message) {
        parent::paintFormattedMessage($message);
        print "$message\n";
        flush();
    }
}

/**
 *    Runs just a single test group, a single case or
 *    even a single test within that case.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class SelectiveReporter extends SimpleReporterDecorator {
    protected $just_this_case = false;
    protected $just_this_test = false;
    protected $on;

    /**
     *    Selects the test case or group to be run,
     *    and optionally a specific test.
     *    @param SimpleScorer $reporter    Reporter to receive events.
     *    @param string $just_this_case    Only this case or group will run.
     *    @param string $just_this_test    Only this test method will run.
     */
    function __construct($reporter, $just_this_case = false, $just_this_test = false) {
        if (!empty($just_this_case)) {
            $this->just_this_case = strtolower($just_this_case);
            $this->off();
        } else {
            $this->on();
        }
        if (!empty($just_this_test)) {
            $this->just_this_test = strtolower($just_this_test);
        }
        parent::__construct($reporter);
    }

    /**
     *    Compares criteria to actual the case/group name.
     *    @param string $test_case    The incoming test.
     *    @return boolean             True if matched.
     *    @access protected
     */
    protected function matchesTestCase($test_case) {
        return $this->just_this_case == strtolower($test_case);
    }

    /**
     *    Compares criteria to actual the test name. If no
     *    name was specified at the beginning, then all tests
     *    can run.
     *    @param string $method       The incoming test method.
     *    @return boolean             True if matched.
     *    @access protected
     */
    protected function shouldRunTest($test_case, $method) {
        if ($this->isOn() || $this->matchesTestCase($test_case)) {
            if ($this->just_this_test) {
                return $this->just_this_test == strtolower($method);
            } else {
                return true;
            }
        }
        return false;
    }

    /**
     *    Switch on testing for the group or subgroup.
     *    @access protected
     */
    protected function on() {
        $this->on = true;
    }

    /**
     *    Switch off testing for the group or subgroup.
     *    @access protected
     */
    protected function off() {
        $this->on = false;
    }

    /**
     *    Is this group actually being tested?
     *    @return boolean     True if the current test group is active.
     *    @access protected
     */
    protected function isOn() {
        return $this->on;
    }

    /**
     *    Veto everything that doesn't match the method wanted.
     *    @param string $test_case       Name of test case.
     *    @param string $method          Name of test method.
     *    @return boolean                True if test should be run.
     *    @access public
     */
    function shouldInvoke($test_case, $method) {
        if ($this->shouldRunTest($test_case, $method)) {
            return $this->reporter->shouldInvoke($test_case, $method);
        }
        return false;
    }

    /**
     *    Paints the start of a group test.
     *    @param string $test_case     Name of test or other label.
     *    @param integer $size         Number of test cases starting.
     *    @access public
     */
    function paintGroupStart($test_case, $size) {
        if ($this->just_this_case && $this->matchesTestCase($test_case)) {
            $this->on();
        }
        $this->reporter->paintGroupStart($test_case, $size);
    }

    /**
     *    Paints the end of a group test.
     *    @param string $test_case     Name of test or other label.
     *    @access public
     */
    function paintGroupEnd($test_case) {
        $this->reporter->paintGroupEnd($test_case);
        if ($this->just_this_case && $this->matchesTestCase($test_case)) {
            $this->off();
        }
    }
}

/**
 *    Suppresses skip messages.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class NoSkipsReporter extends SimpleReporterDecorator {

    /**
     *    Does nothing.
     *    @param string $message    Text of skip condition.
     *    @access public
     */
    function paintSkip($message) { }
}

/**
 *    Suppresses pass messages.
 *    @package SimpleTest
 *    @subpackage UnitTester
 */
class NoPassesReporter extends SimpleReporterDecorator {

    /**
     *    Does nothing.
     *    @param string $message    Text of pass condition.
     *    @access public
     */
    function paintPass($message) { }
}
