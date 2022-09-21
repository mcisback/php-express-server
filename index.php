<?php

require_once __DIR__ . '/vendor/autoload.php';

use Mcisback\PhpExpresso\Server\PhpExpresso;
use Mcisback\PhpExpresso\Http;

$app = new PhpExpresso($_SERVER, $_REQUEST);

// Use Middlewares Like This:
$cors = function (&$req, &$res, \Closure $next) {
    $res->headers([
        "Access-Control-Allow-Origin" => "*",
        "Access-Control-Allow-Headers" => "*",
    ]);

    $next($req, $res, $next);
};

$app->use($cors);

// Or Like This:

$app->use(function (&$req, &$res, \Closure $next) {
    // Use your own session driver
    $req->register('session', new Http\Session());

    $token = $req->bearer() ?? null;

    if($token !== null) {
        $req->session()->set('token', $token);
        $req->session()->set('loggedIn', 'yes');
    }
    
    $req->register('isLoggedIn', function() use(&$req) {
        return $req->session()->get('loggedIn') === true;
    });

    $next($req, $res, $next);
});

$app->get('/session', function($req, $res) {
    return $res->json([
        'message' => 'Hello, World',
        'bearer' => $req->bearer(),
        'session' => $req->session(),
        'loggedIn' => $req->session()->get('loggedIn'),
        'isLoggedIn' => $req->isLoggedIn(),
    ]);
});


// Serve Static Files
$app->get('/errors/([^\.]+\.\w+)', function($req, $res, $fileName) {
    return $res->html(
        file_get_contents(__DIR__ . '/resources/views/errors/' . $fileName)
    );
});

// Match Patterns
$app->get('/hello_pattern/?(\d*)', function($req, $res, $id) {
    return $res->json([
        'message' => 'Hello, World',
        'query_string' => $req->query(),
        'id' => $id,
    ]);
});

// Serve JSON And Read QueryString
$app->get('/hello_json', function($req, $res) {
    return $res->json([
        'message' => 'Hello, World',
        'query_string' => $req->query(),
    ]);
});

// Server HTML
$app->get('/hello_html', function($req, $res) {
    return $res->html('
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Not Found</title>
        </head>
        <body>
            <h1>Hello, World !</h1>
            <h2>From PhpExpress</h2>
        </body>
        </html>
    ');
});

// Receive and process JSON Data
$app->post('/receive_json', function($req, $res) {
    return $res->json([
        'received_json' => $req->all(),
        'query_string' => $req->query(),
    ]);
});

// Run App
$app->run();