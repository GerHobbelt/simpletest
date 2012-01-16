<?php
// $Id: $
require_once(dirname(__FILE__) . '/../../../autorun.php');

class CoverageUnitTests extends TestSuite {
    function __construct() {
        parent::__construct('Coverage Unit tests');
        $path = dirname(__FILE__) . '/*_test.php';
        foreach(glob($path) as $test) {
            $this->addFile($test);
        }
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
