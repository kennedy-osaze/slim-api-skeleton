<?php

use Slim\App;
use Monolog\Logger;
use App\Libraries\Jwt\Jwt;
use App\Libraries\Factory;
use Faker\Factory as Faker;
use App\Libraries\Auth\Auth;
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
        $logger_settings = $container['settings']['logger'];

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

    // Jwt
    $container['jwt'] = function ($container) {
        return new Jwt($container['settings']['jwt-auth'], $container->request);
    };

    // Auth
    $container['auth'] = function ($container) {
        $authenticable_class = $container['settings']['jwt-auth']['authenticable'];

        return new Auth($container, new $authenticable_class);
    };

    // Illuminate Factory
    $container['factory'] = function ($container) {
        $app_settings = $container['settings']['app'];
        $base_path = rtrim($app_settings['base_dir'], '\/');
        $factories_path = $base_path . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'factories';

        return Factory::build(Faker::create($app_settings['locale'] ?: null), $factories_path);
    };
};
