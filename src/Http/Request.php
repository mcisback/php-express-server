<?php

namespace Mcisback\PhpExpress\Http;

class Request {
    public function __construct($phpRequest) {
        $this->req = $phpRequest;
        $this->_headers = getallheaders();
        $this->_isJson = str_contains($this->_headers['Content-Type'], 'application/json');
        $this->_wantsjson = str_contains($this->_headers['Accept'], 'application/json');

        if($this->_isJson) {
            // takes raw data from the request 
            $json = file_get_contents('php://input');
            // Converts it into a PHP object 
            $this->req = json_decode($json, true);
        }
    }

    public function isJson() {
        return $this->_isJson;
    }

    public function wantsJson() {
        return $this->_wantsjson;
    }

    public function hasHeader($key) {
        return array_key_exists($key, $this->_headers);
    }

    public function header($key) {
        return $this->_headers[$key] ?? null;
    }

    public function headers() {
        return $this->_headers;
    }

    public function get($key) {
        return $this->req[$key];
    }

    public function has($key) {
        return isset($this->req[$key]);
    }

    public function all() {
        return $this->req;
    }
}
