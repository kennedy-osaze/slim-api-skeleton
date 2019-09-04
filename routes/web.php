<?php

use Slim\App;
use App\Controllers\HomeController;

return function (App $app) {
    $container = $app->getContainer();

    $app->group('/v1', function () use ($container) {
        $this->get('', HomeController::class . ':index')->setName('home');

        $this->get('/users/{user}', HomeController::class . ':me')->setName('me');
    });
};
