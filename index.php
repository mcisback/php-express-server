<?php

require_once __DIR__ . '/vendor/autoload.php';


$app = new Mcisback\PhpExpress\Server\PhpExpress($_SERVER, $_REQUEST);

$app->get('/hello_json', function($req, $res) {
    return $res->json([
        'message' => 'Hello, World'
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


$app->run();