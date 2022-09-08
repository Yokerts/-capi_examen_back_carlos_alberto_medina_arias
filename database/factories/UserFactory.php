<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\User;
use Faker\Generator as Faker;

$factory->define(User::class, function (Faker $faker) {
    return [
        'domicilio' => $faker->address(),
        'numero_exterior' => Str::random(3),
        'colonia' => $faker->name(),
        'cp' => Str::random(5),
        'ciudad' => $faker->name(),
        'fecha_nacimiento' => now(),
        'remember_token' => Str::random(10),

    ];
});
