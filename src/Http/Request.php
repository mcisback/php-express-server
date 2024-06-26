<?php

namespace PhpExpresso\Http;

class Request {
    public function __construct(array $server, array $phpRequest) {
        $this->server = $server;
        $this->host = $server['HTTP_HOST'];
        $this->method = strtolower($this->server['REQUEST_METHOD']);
        $this->req = $phpRequest;
        $this->_headers = getallheaders();
        $this->_isJson = str_contains($this->_headers['Content-Type'] ?? '', 'application/json');
        $this->_wantsjson = str_contains($this->_headers['Accept'] ?? '', 'application/json');
        $this->_bearer = null;
        $this->_data = [];

        $this->url = (object) parse_url(
            ($this->isSSL() ? 'https://' : 'http://') . $this->host . $this->server['REQUEST_URI']
        );

        $qs = [];

        if(is_array($this->url->query)) {
            $qs = $this->url->query;
        } else {
            parse_str($this->url->query, $qs);
        }

        // print_r($qs);
        // exit;

        $this->qs = $this->qs ?? [];

        if($this->_isJson) {
            // takes raw data from the request 
            $json = file_get_contents('php://input');
            // Converts it into a PHP object 
            $this->req = json_decode($json, true);
        }
    }

    public function __call($methodName, $args) {
        if(array_key_exists($methodName, $this->_data)) {
            $tmp = $this->data($methodName);
            
            if(is_callable($tmp)) {
                return $tmp(...$args);
            }

            return $tmp;
        }
    }

    public function register(string $key, $value) {
        return $this->data($key, $value);
    }

    public function data(string $key=null, $value=null) {
        if($key === null && $value === null){
            return $this->_data;
        }

        if($value === null) {
            return $this->_data[$key] ?? null;
        }

        $this->_data[$key] = $value;
     }

    public function bearer(string $header='Authorization', string $pattern='/\s*Bearer\s*(.+)$/') {
        if($this->bearer === null) {
            if($this->hasHeader($header)) {
                $auth = $this->header($header);
    
                if(preg_match($pattern, $auth, $matches)) {
                    $this->_bearer = $matches[1] ?? null;
                }
            }
        }

        return $this->bearer;
    }

    public function method() {
        return $this->method;
    }

    public function host() {
        return $this->host;
    }

    public function url() {
        return $this->url;
    }

    public function isSSL(){
        if(isset($this->server['HTTP_X_FORWARDED_PROTO']) && $this->server['HTTP_X_FORWARDED_PROTO']=="https") {
            return true; 
        }
        elseif(isset($this->server['HTTPS'])){ return true; }
        elseif($this->server['SERVER_PORT'] == 443){ return true; }
        else{ return false; }
    }

    public function query($key=null) {
        if($key === null) {
            return $this->qs;
        }

        return $this->qs[$key] ?? '';
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
