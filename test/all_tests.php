<?php
require_once(dirname(__FILE__) . '/../autorun.php');

class AllTests extends TestSuite {
    function __construct() {
        /*
        And a little hack to make sure PHP does not timeout
        */
        //   http://www.php.net/manual/en/info.configuration.php#ini.max-execution-time
        set_time_limit(max(5 * 60, ini_get('max_execution_time')));

        parent::__construct('All tests for SimpleTest ' . SimpleTest::getVersion());
        $this->addFile(dirname(__FILE__) . '/unit_tests.php');
        $this->addFile(dirname(__FILE__) . '/shell_test.php');
        $this->addFile(dirname(__FILE__) . '/live_test.php');
        $this->addFile(dirname(__FILE__) . '/acceptance_test.php');
    }

    /*
     * This dummy test function only exists so that the SimpleTest class scanner
     * will 'see' this class as the scanner will be looking for classes with
     * one or more methods named 'test<something>' and doesn't/cannot know
     * about the collector (addFile()) code in our constructor.
     */
    function testDummy() {
    }
}
