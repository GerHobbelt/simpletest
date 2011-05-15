<?php
// $Id$
require_once(dirname(__FILE__) . '/../autorun.php');
require_once(dirname(__FILE__) . '/../collector.php');

class ExtensionsTests extends TestSuite {
	function skip() {
		$this->skipIf(version_compare(phpversion(), '5', '<'),
                      'Many extensions only work with PHP5 and above');
	}

    function __construct() {
        parent::__construct('Extension tests for SimpleTest ' . SimpleTest::getVersion());

		$nodes = new RecursiveDirectoryIterator(dirname(__FILE__).'/../extensions/');
		foreach(new RecursiveIteratorIterator($nodes) as $node) {
			if (preg_match('/test\.php$/', $node->getFilename())) {
		        $this->addFile($node->getPathname());
			}        	
        }
    }
}
?>