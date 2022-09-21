<?php

namespace Mcisback\PhpExpresso\Http;

class Session {
    public function __construct() {
        session_start();

        $this->session = &$_SESSION;
    }

    public function reset() {
        session_reset();
    }

    public function unset() {
        session_unset();
    }

    public function abort() {
        session_abort();
    }

    public function all() {
        return $this->session;
    }

    public function get($key) {
        return $this->session[$key] ?? '';
    }

    public function set($key, $value) {
        return $this->session[$key] = $value;
    }

    public function has($key) {
        return isset($this->session[$key]) ?? false;
    }

    public function delete($key) {
        unset($this->session[$key]);
    }

    public function destroy() {
        session_destroy();
    }
}
