<?php

$config = require_once './config/index.php';
$database = $config['database'];

return [
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/database/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/database/seeds'
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_database' => 'local',

        'production' => [
            'adapter' => $database['driver'],
            'host' => $database['host'],
            'name' => $database['database'],
            'user' => $database['username'],
            'pass' => $database['password'],
            'port' => $database['port'],
            'charset' => $database['charset'],
            'collation' => $database['collation'],
        ],

        'local' => [
            'adapter' => $database['driver'],
            'host' => $database['host'],
            'name' => $database['database'],
            'user' => $database['username'],
            'pass' => $database['password'],
            'port' => $database['port'],
            'charset' => $database['charset'],
            'collation' => $database['collation'],
        ],

        'testing' => [
            'adapter' => 'sqlite',
            'memory' => 'true'
        ],
    ],
    'version_order' => 'creation'
];
