<?php
require_once(dirname(__FILE__) . '/../autorun.php');

class BadTestCases extends TestSuite {
    function __construct() {
        parent::__construct('A bad test case');
        $this->addFile(dirname(__FILE__) . '/support/empty_test_file.php');
    }
}
?>