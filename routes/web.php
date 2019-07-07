<?php

use Slim\App;
use App\Controllers\HomeController;

return function (App $app) {

    // Add your routes
    $app->redirect('/', '/api/v1', 301);

    $app->group('/api/v1', function () {
        $this->get('', HomeController::class . ':index')->setName('home');
    });
};
