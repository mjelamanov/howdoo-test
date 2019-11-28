<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\ApiToken;
use App\User;
use Faker\Generator as Faker;

$factory->define(ApiToken::class, function (Faker $faker) {
    return [
        'token' => Str::random(),
        'expires_at' => $faker->dateTimeBetween('yesterday', 'tomorrow'),
        'user_id' => function() {
            return factory(User::class)->create();
        },
    ];
});

$factory->state(ApiToken::class, 'expired', function (Faker $faker) {
    return [
        'expires_at' => $faker->dateTimeBetween('-10 days', '-1 minute'),
    ];
});

$factory->state(ApiToken::class, 'active', function (Faker $faker) {
    return [
        'expires_at' => $faker->dateTimeBetween('now', 'tomorrow'),
    ];
});
