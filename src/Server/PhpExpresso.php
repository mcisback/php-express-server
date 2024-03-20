<?php

namespace PhpExpresso\Server;

use PhpExpresso\Http\Request;
use PhpExpresso\Http\Response;
use PhpExpresso\Router\Router;

class PhpExpresso {
    function __construct() {
        $this->routers = [];
        $this->routers[] = new Router();

        $this->session = null;

        $this->request = new Request($_SERVER, $_REQUEST);
        $this->response = new Response();
    }

    public function __call($method, $args) {
        foreach($this->routers as $router) {
            if(array_key_exists($method, $router->map)) {
                return $router->dispatch($method, ...$args);
            }
        }
    }

    public function router(Router $router) {
        $this->routers[] = $router;
    }

    public function use($route = '', \Closure | Router $handler) {
        if( is_a($handler, Router::class) ) {
            $this->router($handler);
        } elseif( is_a($handler, \Closure::class) ) {
            foreach($this->routers as $router) {
                $router->prefix($route);

                $router->use($handler);
            }
        } else {
            throw new \Exception("${__class__}::use arguments must be Closure or Router");
        }
    }
}