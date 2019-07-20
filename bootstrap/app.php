<?php

require_once '../vendor/autoload.php';

$config = require_once '../config/index.php';
date_default_timezone_set($config['app']['timezone']);

$app = new \Slim\App(['settings' => $config]);

// Set up dependencies
$dependencies = require_once 'dependencies.php';
$dependencies($app);

// Set up Registry
$registry = require_once 'registry.php';
$registry($app);

//Set up error handling
$error_handlers = require_once 'error-handlers.php';
$error_handlers($app);

// Register middleware
$middleware = require_once 'middleware.php';
$middleware($app);

// Register routes
$routes = require_once '../routes/web.php';
$routes($app);
