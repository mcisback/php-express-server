<?php

require_once __DIR__ . '/vendor/autoload.php';


$app = new Mcisback\PhpExpress\Server\PhpExpress($_SERVER, $_REQUEST);

$cors = function (&$req, &$res, \Closure $next) {
    $res->headers([
        "Access-Control-Allow-Origin" => "*",
        "Access-Control-Allow-Headers" => "*",
    ]);

    $next($req, $res, $next);
};

$app->use($cors);

$app->get('/hello_json', function($req, $res) {
    return $res->json([
        'message' => 'Hello, World',
        'query_string' => $req->query('param1'),
    ]);
});

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

$app->post('/receive_json', function($req, $res) {
    return $res->json([
        'received_json' => $req->all(),
        'query_string' => $req->query(),
    ]);
});


$app->run();
