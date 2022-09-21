<?php

namespace Mcisback\PhpExpresso\Server;

use Mcisback\PhpExpresso\Http\Request;
use Mcisback\PhpExpresso\Http\Response;

class PhpExpresso {
    function __construct($server, $request) {
        $this->server = $server;
        $this->middlewares = [];
        $this->map = [
            'get' => [],
            'post' => [],
            'put' => [],
            'patch' => [],
            'delete' => [],
            'head' => [],
        ];

        // $this->routeParams = [];

        $this->request = new Request($this->server, $request);
        $this->response = new Response();
    }

    public function dispatch(string $method, string $route, \Closure $callback) {
        $req = $this->request;
        $res = $this->response;
        // $params = $this->routeParams;

        if(!array_key_exists($method, $this->map)) {
            throw new \Exception("Method $method not supported");
        }

        $this->map[$method][$route] = function(array $params=[]) use ($req, $res, $callback) {
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

    // public function next() {
    //     // foreach ($this->middlewares as $middleware) {
    //     //     yield $middleware;
    //     // }

    //     return next($this->middlewares);
    // }

    protected function runMiddlewares() {
        if(count($this->middlewares) === 0) {
            return false;
        }

        // print_r($this->middlewares);

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
        // echo 'PATH INFO: ' . $_SERVER['PATH_INFO'];

        // print_r($this->server);

        $route = $this->request->url()->path === '' ? '/' : $this->request->url()->path;

        if(str_starts_with($route, '/index.php')) {
            $route = str_replace('/index.php', '', $route);
        }

        // Serve statics here ?

        // $params = preg_match($pattern, $route, $matches) ?? [];

        // echo "ROUTE: {$this->server['REQUEST_URI']} $route";

        // echo "<br>";

        // print_r($this->map[$method]);

        // echo "<br>";
        // echo "<br>";
        // echo "<br>";

        // print_r($this->map[$method][$route]);

        // var_dump(!isset($this->map[$method][$route]));

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

    public function matchRoute($route, $method) {

        foreach ($this->map[$method] as $candidate => $closure) {
            // echo "$candidate: $route";
            // echo "<br />";

            if(preg_match("%$candidate%", $route, $params)){
                // echo "MATCH !";
                // echo "<br />";
                
                // print_r($params);

                // $this->routeParams = $params;

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