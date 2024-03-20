<?php

namespace PhpExpresso\Router;

use PhpExpresso\Http\Request;
use PhpExpresso\Http\Response;

class Router {
    function __construct($prefix = '') {
        $this->middlewares = [];
        $this->prefix = $prefix;
        $this->map = [
            'get' => [],
            'post' => [],
            'put' => [],
            'patch' => [],
            'delete' => [],
            'head' => [],
        ];

        $this->session = null;

        $this->request = new Request($_SERVER, $_REQUEST);
        $this->response = new Response();
    }

    public function prefix($prefix) {
        $this->prefix = $prefix;
    }
    
    public function session($session=null) {
        if($session === null) {
            return $this->session;
        }

        $this->session = $session;
    }

    public function dispatch(string $method, string $route, \Closure $callback) {
        $req = $this->request;
        $res = $this->response;

        if(!array_key_exists($method, $this->map)) {
            throw new \Exception("Method $method not supported");
        }

        $this->map[$method][$this->prefix . $route] = function(array $params=[]) use ($req, $res, $callback) {
            return $callback($req, $res, ...$params);
        };
    }

    public function __call($method, $args) {
        if(array_key_exists($method, $this->map)) {
            return $this->dispatch($method, ...$args);
        }
    }

    public function use(\Closure $middleware) {
        return $this->middleware($middleware);
    }

    public function middleware(\Closure $middleware) {
        $this->middlewares[] = $middleware;
    }

    protected function runMiddlewares() {
        if(count($this->middlewares) === 0) {
            return false;
        }

        $middleware = current($this->middlewares);
        $middlewares = $this->middlewares;

        $middleware($this->request, $this->response, function(&$req, &$res, \Closure $_next) use (&$middlewares) {
            $next = next($middlewares);

            if($next === false) {
                return;
            }

            return $next($req, $res, $_next);
        });
    }

    public function run() {
        $route = $this->request->url()->path === '' ? '/' : $this->request->url()->path;

        if(str_starts_with($route, '/index.php')) {
            $route = str_replace('/index.php', '', $route);
        }

        // Serve statics dirs here ?

        $this->runMiddlewares();

        return $this->matchRoute($route, $this->request->method());
    }

    public function printError($statusCode, $errorMessage) {
        $this->response->status($statusCode);

        if($this->request->wantsJson()) {
            return $this->response->json([
                'success' => false,
                'status' => 'error',
                'httpStatus' => $statusCode,
                'message' => $errorMessage
            ]);
        }

        return $this->response->html('
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta http-equiv="X-UA-Compatible" content="IE=edge">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Not Found</title>
            </head>
            <body>
                <h1>Error '.$statusCode.'</h1>
                <h2>'.$errorMessage.'</h2>
            </body>
            </html>
        ');
    }

    public function print403() {
        return $this->printError(403, 'Method Not Found');
    }

    public function print404() {
        return $this->printError(404, 'Route Not Found');
    }

    public function matchRoute(string $route, string $method) {

        foreach ($this->map[$method] as $candidate => $closure) {

            if(preg_match("%$candidate%", $route, $params)){

                if(is_array($params) && count($params) >= 2) {
                    array_shift($params);
                } else {
                    $params = [];
                }

                return $closure($params);
            }
        }
    }
}
