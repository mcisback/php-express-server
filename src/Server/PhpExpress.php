<?php

namespace Mcisback\PhpExpress\Server;

use Mcisback\PhpExpress\Http\Request;
use Mcisback\PhpExpress\Http\Response;

class PhpExpress {
    function __construct($server, $request) {
        // $this->isSSL = ($server['HTTPS'] ?? 'off') == 'off';

        

        $this->host = $server['HTTP_HOST'];
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

        $requestUrl = ($this->isSSL() ? 'https://' : 'http://') . $this->host . $_SERVER['REQUEST_URI'];

        $this->url = (object) parse_url($requestUrl);

        $this->request = new Request($request, $this->url->query);
        $this->response = new Response();
    }

    public function isSSL(){
        if(isset($this->server['HTTP_X_FORWARDED_PROTO']) && $this->server['HTTP_X_FORWARDED_PROTO']=="https") {
            return true; 
        }
        elseif(isset($this->server['HTTPS'])){ return true; }
        elseif($this->server['SERVER_PORT'] == 443){ return true; }
        else{ return false; }
    }

    public function dispatch(string $method, string $route, \Closure $callback) {
        $req = $this->request;
        $res = $this->response;

        if(!array_key_exists($method, $this->map)) {
            throw new \Exception("Method $method not supported");
        }

        $this->map[$method][$route] = function() use ($req, $res, $callback) {
            return $callback($req, $res);
        };
    }

    public function __call($method, $args) {
        if(array_key_exists($method, $this->map)) {
            return $this->dispatch($method, ...$args);
        }
    }

    // public function get(string $route, \Closure $callback) {
    //     return $this->dispatch('get', $route, $callback);
    // }

    // public function post(string $route, \Closure $callback) {
    //     return $this->dispatch('post', $route, $callback);
    // }

    // public function patch(string $route, \Closure $callback) {
    //     return $this->dispatch('patch', $route, $callback);
    // }

    // public function put(string $route, \Closure $callback) {
    //     return $this->dispatch('put', $route, $callback);
    // }

    // public function delete(string $route, \Closure $callback) {
    //     return $this->dispatch('delete', $route, $callback);
    // }

    // public function head(string $route, \Closure $callback) {
    //     return $this->dispatch('head', $route, $callback);
    // }

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

        

        $route = $this->url->path === '' ? '/' : $this->url->path;

        $method = strtolower($this->server['REQUEST_METHOD']);

        if(str_starts_with($route, '/index.php')) {
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