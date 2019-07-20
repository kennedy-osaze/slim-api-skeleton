<?php

use Slim\App;
use App\Models\User;

return function (App $app) {
    $container = $app->getContainer();

    $container['route-bindings'] = [
        'user' => User::class,

        // 'user' => function ($value) {
        //     return User::where('id', $value)->first();
        // },
    ];
};
