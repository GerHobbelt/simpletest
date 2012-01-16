<?php
require_once(dirname(__FILE__) . '/../autorun.php');
require_once(dirname(__FILE__) . '/../shell_tester.php');
require_once(dirname(__FILE__) . '/support/test1.php');

class TestOfAutorun extends UnitTestCase {
    function testLoadIfIncluded() {
        $tests = new TestSuite();
        $tests->addFile(dirname(__FILE__) . '/support/test1.php');
        $this->assertEqual($tests->getSize(), 1);
    }

    function testExitStatusOneIfTestsFail() {
        $sh = new SimpleShell();
        $exit_status = $sh->execute('php ' . dirname(__FILE__) . '/support/failing_test.php');
        $this->assertEqual($exit_status, 1);
    }

    function testExitStatusZeroIfTestsPass() {
        $sh = new SimpleShell();
        $exit_status = $sh->execute('php ' . dirname(__FILE__) . '/support/passing_test.php');
        $this->assertEqual($exit_status, 0);
    }
}

