<?php

use Faker\Generator as Faker;

/**
 * Model Factories Definitions
*/

$factory->define(App\Models\User::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
    ];
});
