<?php

use Slim\App;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\UidProcessor;
use App\Libraries\Validation\Validator;
use Slim\Handlers\Strategies\RequestResponseArgs;
use Illuminate\Database\Capsule\Manager as Capsule;
use Respect\Validation\Validator as RespectValidator;

return function (App $app) {
    $container = $app->getContainer();

    // Illuminate Database
    $capsule = new Capsule;
    $capsule->addConnection($container['settings']['database']);
    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    $container['db'] = function ($container) use ($capsule) {
        return $capsule;
    };

    $container['foundHandler'] = function () {
        return new RequestResponseArgs;
    };

    // Monolog set up
    $container['logger'] = function ($container) {
        $logger_settings = $container->get('settings')['logger'];

        $logger = new Logger($logger_settings['name']);
        $logger->pushProcessor(new UidProcessor());
        $logger->pushHandler(new StreamHandler($logger_settings['path'], $logger_settings['level']));

        return $logger;
    };

    // Request Validator
    $container['validator'] = function () {
        RespectValidator::with('App\\Libraries\\Validation\\Rules\\');

        return new Validator;
    };
};
