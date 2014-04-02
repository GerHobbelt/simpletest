<?php
// $Id$
require_once(dirname(__FILE__) . '/../autorun.php');
require_once(dirname(__FILE__) . '/../collector.php');

class ExtensionsTests extends TestSuite {
    function skip() {
        // TODO: this is a useless skip test right now as we only support PHP5; however, we may need to skip 5.0 / 5.1 versions: check how far we can go back there.
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

    /*
     * This dummy test function only exists so that the SimpleTest class scanner
     * will 'see' this class as the scanner will be looking for classes with
     * one or more methods named 'test<something>' and doesn't/cannot know
     * about the collector (addFile()) code in our constructor.
     */
    function testDummy() {
    }
}
