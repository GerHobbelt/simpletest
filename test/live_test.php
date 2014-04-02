<?php
// $Id$
require_once(dirname(__FILE__) . '/../autorun.php');
require_once(dirname(__FILE__) . '/../socket.php');
require_once(dirname(__FILE__) . '/../http.php');
require_once(dirname(__FILE__) . '/../web_tester.php');
require_once(dirname(__FILE__) . '/../compatibility.php');

if (SimpleTest::getDefaultProxy()) {
    SimpleTest::ignore('LiveHttpTestCase');
}

class LiveHttpTestCase extends UnitTestCase {

    protected function getServerInfo()
    {
        static $scheme_ports = array('http' => 80, 'https' => 443, 'telnet' => 23);

        WebTestCase::setDefaultServerUrl();
        $url = WebTestCase::getDefaultServerUrl();
        if (!empty($url))
        {
            $info = parse_url($url);
            if (isset($info['scheme']) && array_key_exists($info['scheme'], $scheme_ports) && !isset($info['port']))
            {
                $info['port'] = $scheme_ports[$info['scheme']];
            }
        }
        else
        {
            $info = array();
        }
        return $info;
    }

    function testBadSocket() {
        $socket = new SimpleSocket('bad_url', 111, 5);
        $this->assertTrue($socket->isError());
        $this->assertPattern(
                '/Cannot open \\[bad_url:111\\] with \\[/',
                $socket->getError());
        $this->assertFalse($socket->isOpen());
        $this->assertFalse($socket->write('A message'));
    }

    function testSocketClosure() {
        $site = $this->getServerInfo();
        $socket = new SimpleSocket($site['host'], $site['port'], 15, 8);
        $this->assertTrue($socket->isOpen());
        $this->assertTrue($socket->write("GET {$site['path']}network_confirm.php HTTP/1.0\r\n"));
        $socket->write("Host: {$site['host']}\r\n");
        $socket->write("Connection: close\r\n\r\n");
        $this->assertEqual($socket->read(), "HTTP/1.1");
        $socket->close();
        $this->assertIdentical($socket->read(), false);
    }

    function testRecordOfSentCharacters() {
        $site = $this->getServerInfo();
        $socket = new SimpleSocket($site['host'], $site['port'], 15);
        $this->assertTrue($socket->write("GET {$site['path']}network_confirm.php HTTP/1.0\r\n"));
        $socket->write("Host: {$site['host']}\r\n");
        $socket->write("Connection: close\r\n\r\n");
        $socket->close();
        $this->assertEqual($socket->getSent(),
                "GET {$site['path']}network_confirm.php HTTP/1.0\r\n" .
                "Host: {$site['host']}\r\n" .
                "Connection: close\r\n\r\n");
    }
}
