<?php
/**
 *  Autorunner which runs all tests cases found in a file
 *  that includes this module.
 *  @package    SimpleTest
 *  @version    $Id$
 */

/**#@+
 * include simpletest files
 */
require_once dirname(__FILE__) . '/autorun_helper.php';
/**#@-*/

$GLOBALS['SIMPLETEST_AUTORUNNER_INITIAL_CLASSES'] = get_declared_classes();
$GLOBALS['SIMPLETEST_AUTORUNNER_INITIAL_PATH'] = getcwd();
register_shutdown_function('simpletest_autorun');

