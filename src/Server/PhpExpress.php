<?php

namespace Mcisback\PhpExpress\Server;

use Mcisback\PhpExpress\Http\Request;
use Mcisback\PhpExpress\Http\Response;

class PhpExpress {
    function __construct($server, $request) {
        $this->request = new Request($request);
        $this->response = new Response();
        $this->server = $server;
        $this->middlewares = [];
        $this->map = [
            'get' => [],
            'post' => [],
        ];
    }

    public function get(string $route, \Closure $callback) {
        $req = $this->request;
        $res = $this->response;

        $this->map['get'][$route] = function() use ($req, $res, $callback) {
            return $callback($req, $res);
        };
    }

    public function post(string $route, \Closure $callback) {
        $req = $this->request;
        $res = $this->response;

        $this->map['post'][$route] = function() use ($req, $res, $callback) {
            return $callback($req, $res);
        };
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

        $route = $this->server['REQUEST_URI'];
        $route = $route === '' ? '/' : $route;

        $method = strtolower($this->server['REQUEST_METHOD']);

        if(str_contains($route, '/index.php')) {
            $route = str_replace('/index.php', '', $route);
        }

        // echo "ROUTE: {$this->server['REQUEST_URI']} $route";

        // echo "<br>";

        // print_r($this->map[$method]);

        // echo "<br>";
        // echo "<br>";
        // echo "<br>";

        // print_r($this->map[$method][$route]);

        // var_dump(!isset($this->map[$method][$route]));

        if(!isset($this->map[$method])) {
            $this->response->status(403);

            if($this->request->wantsJson()) {
                return $this->response->json([
                    'success' => false,
                    'status' => 'error',
                    'message' => 'Method Not Found'
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
                    <h1>Error 403</h1>
                    <h2>Method Not Found</h2>
                </body>
                </html>
            ');
        }

        if(!isset($this->map[$method][$route])) {
            $this->response->status(404);

            if($this->request->wantsJson()) {
                return $this->response->json([
                    'success' => false,
                    'status' => 'error',
                    'message' => 'Route Not Found'
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
                    <h1>Error 404</h1>
                    <h2>Route Not Found</h2>
                </body>
                </html>
            ');
        }

        $this->runMiddlewares();

        return $this->map[$method][$route]();
    }
}