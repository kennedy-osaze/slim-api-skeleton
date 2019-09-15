<?php

// Load environment variables
(\Dotenv\Dotenv::create(__DIR__ . '/../'))->load();

$config = require '../config/index.php';

// Instantiate the application
$app = new \Slim\App(['settings' => $config]);

// Set up dependencies
$dependencies = require 'dependencies.php';
$dependencies($app);

// Set up Registry
$registry = require 'registry.php';
$registry($app);

//Set up error handling
$error_handlers = require 'error-handlers.php';
$error_handlers($app);

// Register middleware
$middleware = require 'middleware.php';
$middleware($app);

// Register routes
$routes = require '../routes/web.php';
$routes($app);

return $app;
