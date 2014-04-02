<?php
// $Id$
require_once(dirname(__FILE__) . '/../default_server.php');
require_once(dirname(__FILE__) . '/../remote.php');
require_once(dirname(__FILE__) . '/../reporter.php');


// nasty hack: ensure the commanline i processed before anything else in here so that the default server URI can indeed be set by the user:
$ignore_me = new DefaultReporter();

// The following URL will depend on your own installation.
$test_url = str_replace('site/', 'visual_test.php', WebserverDefaults::getServerUrl());
if (isset($_SERVER['SCRIPT_URI'])) {
    $base_uri = $_SERVER['SCRIPT_URI'];
    $test_url = str_replace('remote_test.php', 'visual_test.php', $base_uri);
} elseif (isset($_SERVER['HTTP_HOST']) && isset($_SERVER['PHP_SELF'])) {
    $base_uri = 'http://'. $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
    $test_url = str_replace('remote_test.php', 'visual_test.php', $base_uri);
}

$test = new TestSuite('Remote tests');
$test->add(new RemoteTestCase($test_url . '?xml=yes', $test_url . '?xml=yes&dry=yes'));
if (SimpleReporter::inCli()) {
    exit ($test->run(new NoPassesReporter(new TextReporter())) ? 0 : 1);
}
$test->run(new NoPassesReporter(new HtmlReporter()));
