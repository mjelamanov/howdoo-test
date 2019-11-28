<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Document;
use App\Enums\DocumentStatus;
use App\User;
use Faker\Generator as Faker;

$factory->define(Document::class, function (Faker $faker) {
    return [
        'status' => $faker->randomElement(DocumentStatus::values()),
        'payload' => [
            'actor' => $faker->words(rand(1, 3), true),
            'meta' => [
                'type' => 'quick',
                'color' => 'brown'
            ],
            'actions' => [
                [
                    'action' => 'jump over',
                    'actor' => 'lazy dog',
                ],
            ],
        ],
        'user_id' => function() {
            return factory(User::class)->create();
        },
    ];
});

$factory->state(Document::class, 'draft', [
    'status' => DocumentStatus::DRAFT(),
]);

$factory->state(Document::class, 'published', [
    'status' => DocumentStatus::PUBLISHED(),
]);
