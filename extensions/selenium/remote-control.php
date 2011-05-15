<?php
/**
 * Selenium Remote Control Class
 * @package        SimpleTest
 * @subpackage     Extensions
 */
/**
 *
 * Based on the Domain51_Testing_Selenium class available at
 * http://domain51.googlecode.com/svn/Domain51/trunk/
 *
 * @author Travis Swicegood <development [at] domain51 [dot] com>
 * @package        SimpleTest
 * @subpackage     Extensions
 */
class SimpleSeleniumRemoteControl
{
	protected $browser = '';
	protected $browserUrl = '';
	protected $host = 'localhost';
	protected $port = 4444;
	protected $timeout = 30000;
	protected $sessionId = null;

	protected $commandMap = array(
		'bool' => array(
			'verify', 
			'verifyTextPresent', 
			'verifyTextNotPresent',
			'verifyValue'
		),
		'string' => array(
			'getNewBrowserSession',
		),
	);

	public function __construct($browser, $browserUrl, $host = 'localhost', $port = 4444, $timeout = 30000) {
		$this->browser = $browser;
		$this->browserUrl = $browserUrl;
		$this->host = $host;
		$this->port = $port;
		$this->timeout = $timeout;
	}

	public function sessionIdParser($response) {
		return substr($response, 3);
	}
	
	public function start() {
		$response = $this->cmd('getNewBrowserSession', array($this->browser, $this->browserUrl));
		$this->sessionId = $this->sessionIdParser($response);
	}

	public function stop() {
		$this->cmd('testComplete');
		$this->sessionId = null;
	}

	public function __call($method, $arguments) {
		$response = $this->cmd($method, $arguments);
		
		foreach ($this->commandMap as $type => $commands) {
			if (!in_array($method, $commands)) {
				continue;
				$type = null;
			}
			break;
		}

		switch ($type) {
			case 'bool' :
				return substr($response, 0, 2) == 'OK' ? true : false;
				break;

			case 'string' :
			default:
				return $response;
		}
	}
	
	protected function server() {
		return "http://{$this->host}:{$this->port}/selenium-server/driver/";
	}

    public function buildUrlCmd($method, $arguments = array()) {
        $params = array(
            'cmd=' . urlencode($method),
        );
        $i = 1;
        foreach ($arguments as $param) {
            $params[] = $i++ . '=' . urlencode(trim($param));
        }
        if (isset($this->sessionId)) {
            $params[] = 'sessionId=' . $this->sessionId;
        }

        return $this->server()."?".implode('&', $params);
    }

	public function cmd($method, $arguments = array()) {
          $url = $this->buildUrlCmd($method, $arguments);
          $response = $this->sendRequest($url);
          return $response;
	}

	public function isUp() {
        return (bool)@fsockopen($this->host, $this->port, $errno, $errstr, 30);
	}
	
	protected function initCurl($url) {
        if (!function_exists('curl_init')) {
            throw new Exception('this code currently requires the curl extension');
        }
        if (!$ch = curl_init($url)) {
            throw new Exception('Unable to setup curl');
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, floor($this->timeout));
		return $ch;	
	}
	
	protected function sendRequest($url) {
        $ch = $this->initCurl($url);
        $result = curl_exec($ch);
        if (($errno = curl_errno($ch)) != 0) {
            throw new Exception('Curl returned non-null errno ' . $errno . ':' . curl_error($ch));
        }
        curl_close($ch);
        return $result;
	}
}
