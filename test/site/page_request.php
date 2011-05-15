<?php
// $Id$

class PageRequest {
    protected $parsed;
    
    function PageRequest($raw) {
        $statements = explode('&', $raw);
        $this->parsed = array();
        foreach ($statements as $statement) {
            if (strpos($statement, '=') === false) {
                continue;
            }
            $this->parseStatement($statement);
        }
    }
    
    protected function parseStatement($statement) {
        list($key, $value) = explode('=', $statement);
        $key = urldecode($key);
        if (preg_match('/(.*)\[\]$/', $key, $matches)) {
            $key = $matches[1];
            if (! isset($this->parsed[$key])) {
                $this->parsed[$key] = array();
            }
            $this->addValue($key, $value);
        } elseif (isset($this->parsed[$key])) {
            $this->addValue($key, $value);
        } else {
            $this->setValue($key, $value);
        }
    }
    
    protected function addValue($key, $value) {
        if (! is_array($this->parsed[$key])) {
            $this->parsed[$key] = array($this->parsed[$key]);
        }
        $this->parsed[$key][] = urldecode($value);
    }
    
    protected function setValue($key, $value) {
        $this->parsed[$key] = urldecode($value);
    }
    
    function getAll() {
        return $this->parsed;
    }
    
    static function get() {
        $request = new PageRequest($_SERVER['QUERY_STRING']);
        return $request->getAll();
    }
    
    static function post() {
        $request = new PageRequest(file_get_contents("php://input"));	// HTTP_RAW_POST_DATA -- http://us.php.net/manual/en/wrappers.php.php
        return $request->getAll();
    }
}
?>