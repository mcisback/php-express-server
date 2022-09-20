<?php

namespace Mcisback\PhpExpress\Http;

class Response {
    public function __construct($statusCode=200) {
        $this->response = '';
        $this->headerSent = false;
        $this->status = $statusCode;
    }

    public function sendHeaders(bool $replace=true) {
        if(!$this->headerSent) {
            foreach ($this->headers as $header => $value) {
                header("$header: $value", $replace);
            }
        }

        $this->headerSent = true;
    }

    public function send(string $data='') {
        $this->response .= $data;

        $this->sendStatus();
        $this->sendHeaders();

        echo $this->response;
    }

    public function status(int $statusCode) {
        $this->statusCode = $statusCode;

        return $this;
    }

    public function sendStatus() {
        http_response_code($this->statusCode);
    }

    public function header(string $key, string $value) {
        $this->headers[$key] = $value;

        return $this;
    }

    public function headers(array $headers) {
        $this->headers = [...$this->headers, ...$headers];

        return $this;
    }

    public function html(string $data) {
        $this->header('Content-Type', 'text/html; charset=utf-8');

        $this->send($data);
    }
    
    public function json(array $data, bool $assoc = true) {
        $this->header('Content-Type', 'application/json; charset=utf-8');

        $this->response = json_encode($data, $assoc);

        $this->send();
    }
}