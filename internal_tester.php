<?php
/**
 *  base include file for SimpleInternalValidator
 *  @package    SimpleTest
 *  @subpackage SimpleInternalValidator
 *  @version    $Id$
 */

/**#@+
 *  include other SimpleTest class files
 */
require_once(dirname(__FILE__) . '/unit_tester.php');
/**#@-*/

/**
 *    Internal validation test class for SimpleTest internals testing.
 *    @package  SimpleTest
 *    @subpackage   SimpleInternalValidator
 */
final class SimpleInternalValidator extends UnitTestCase {

    function __construct() {
        parent::__construct('SimpleInternalValidator');
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
        if (!$this->checkExpectation($expectation, $compare)) {
            $failmsg = $this->constructFailMessage($expectation, $compare, $message);
            $stack = array();
            if (function_exists('debug_backtrace')) {
                $stack = array_reverse(debug_backtrace());
            }
            $state = 0;
            $stackdump = '';
            $i = 0;
            foreach ($stack as $frame) {
                if ($state < 2 && !empty($frame['function']) && strncmp($frame['function'], 'invoke', 6) == 0) {
                    $state = 1;
                }
                else if ($state == 1) {
                    $state = 2;
                }
                else if ($state == 2 && !empty($frame['class']) && strcmp($frame['class'], 'SimpleInternalValidator') == 0) {
                    $state = 3;
                }
                if ($state == 2) {
                    $file = strtr(@$frame['file'], '\\', '/');
                    $path = explode('/', $file);
                    if (count($path) > 2) {
                        $path = array_slice($path, count($path) - 2);
                        $file = implode('/', $path);
                    }
                    $i++;
                    $stackdump .= "[" . $i . "] " . @$frame['class'] . @$frame['type'] . @$frame['function'] . ' at line ' . @$frame['line'] . ' in file ' . $file . "\n";
                }
            }
            throw new Exception($failmsg . " --> Stack trace:\n" . $stackdump);
        }
    }
}


function InternalValidator() {
    return new SimpleInternalValidator();
}
