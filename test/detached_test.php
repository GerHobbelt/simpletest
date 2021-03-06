<?php
// $Id$
require_once('../detached.php');
require_once('../reporter.php');

// The following URL will depend on your own installation.
$command = 'php ' . dirname(__FILE__) . '/visual_test.php --xml';

$test = new TestSuite('Remote tests');
$test->add(new DetachedTestCase($command, $command . ' --dry'));
if (SimpleReporter::inCli()) {
    exit ($test->run(new NoPassesReporter(new TextReporter())) ? 0 : 1);
}
$test->run(new NoPassesReporter(new HtmlReporter()));
