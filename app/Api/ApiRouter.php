<?php

namespace App\Api\ApiRouter;

use App\Data\Users;

function getRouter() {
    $apiRouter = new Router();

    $apiRouter->get('/users', function($req, $res) {
        return $res->json(Users::getAll());
    });
}
