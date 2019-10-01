<?php

return [
    /**
     *  Slim own configuration settings
     */

    // When true, additional information about exceptions are displayed by the default error handler.
    'displayErrorDetails' => getenv('APP_DEBUG') === 'true',

    // Allow the web server to send the content-length header
    'addContentLengthHeader' => false,

    // Only set this if you need access to route within middleware
    'determineRouteBeforeAppMiddleware' => true,

    // path to filename to caching routes. By default, it is disabled
    'routerCacheFile' => getenv('ROUTER_CACHE_ENABLED') === 'true' ? __DIR__ . '/../storage/routes' : false,

    /**
     * Other configuration settings
     */

    'app' => [
        'name' => getenv('APP_NAME') ?: 'Slim',
        'env' => getenv('APP_ENV') ?: 'production',
        'locale' => getenv('APP_LOCALE') ?: 'en',
        'url' => getenv('APP_URL') ?: 'http://localhost',
        'timezone' => getenv('APP_TIME_ZONE') ?: 'UTC',
        'base_dir' => dirname(__DIR__),
    ],

    'database' => [
        'driver' => getenv('DB_DRIVER') ?: 'mysql',
        'host' => getenv('DB_HOST') ?: '127.0.0.1',
        'port' => getenv('DB_PORT') ?: '3306',
        'database' => getenv('DB_DATABASE') ?: 'slim_api_db',
        'username' => getenv('DB_USERNAME') ?: 'root',
        'password' => getenv('DB_PASSWORD') ?: '',
        'charset'   =>  'utf8',
        'collation' =>  'utf8_unicode_ci',
        'prefix' => ''
    ],

    'logger' => [
        'name' => 'app',
        'path' => __DIR__ . '/../storage/logs/app.log',
        'level' => \Monolog\Logger::DEBUG,
    ],

    'cors' => [
        "allowed_origins" => ['*'],
        "allowed_methods" => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
        "allow_headers" => ['X-Requested-With', 'Content-Type', 'Accept', 'Origin', 'Authorization'],
        "expose_headers" =>['Cache-Control', 'Content-Language', 'Content-Type', 'Expires', 'Last-Modified', 'Pragma'],
        "credentials" => false,
        "max_age" => 3600,
    ],

    'jwt-auth' => [
        // Supported Algorithms: ['HS256', 'HS384', 'HS512', 'RS256', 'RS384', 'RS512']
        'algo' => getenv('JWT_ALGO') ?: 'HS256',

        // For symmetric algorithms only (HMAC) like ['HS256', 'HS384', 'HS512'],
        'secret' => getenv('JWT_SECRET'),

        // For asymmetric algorithms
        'keys' => [
            // Absolute path to your public key (e.g. /path/to/public/key)
            'public' => getenv('JWT_PUBLIC_KEY'),
            // Absolute path to your private key (e.g. /path/to/public/key)
            'private' => getenv('JWT_PRIVATE_KEY'),
            // Passphrase used to the encrypt private key
            'pass_phrase' => getenv('JW_PASS_PHRASE') ?: ''
        ],

        // Grace period (seconds) to allow for clock skew. Applies to the`iat`, nbf` and `exp` claims.
        'leeway' => 0,

        // Length of time (in minutes) that the token will be valid for,
        'token_lifetime' => 60,
        'authenticable' => App\Models\User::class, // Model class to authenticate against
    ]
];
