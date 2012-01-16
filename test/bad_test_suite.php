<?php
require_once(dirname(__FILE__) . '/../autorun.php');

class BadTestCases extends TestSuite {
    function __construct() {
        parent::__construct('A bad test case');
        $this->addFile(dirname(__FILE__) . '/support/empty_test_file.php');
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
