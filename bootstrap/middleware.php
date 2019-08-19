<?php

use Slim\App;

// List of middleware to register
$app_middleware = [
    \App\Middleware\TrailingSlash::class,
    \App\Middleware\Cors::class,
    \App\Middleware\BindRoute::class
];

return function (App $app, array $except = []) use ($app_middleware) {
    $container = $app->getContainer();

    foreach ($app_middleware as $middleware_class) {
        if (!empty($except) && in_array($middleware_class, $except)) {
            continue;
        }

        $app->add(new $middleware_class($container));
    }
};
