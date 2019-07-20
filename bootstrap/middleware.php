<?php

use Slim\App;

return function (App $app) {
    $container = $app->getContainer();

    // Application middleware
    $app->add(new \App\Middleware\TrailingSlash($container));

    $app->add(new \App\Middleware\Cors($container));

    $app->add(new \App\Middleware\BindRoute($container));
};
