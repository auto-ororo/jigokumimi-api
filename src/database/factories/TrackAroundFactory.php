<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\TrackAround;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(TrackAround::class, function (Faker $faker) {
    return [
        'spotify_track_id' => $faker->shuffle('abcdefghijklmnopqrstuvwx'),
        'user_id' => $faker->numberBetween(0, 100),
        'latitude' => $faker->latitude(),
        'longitude' => $faker->longitude(),
        'popularity' => $faker->numberBetween(0, 100)
    ];
});
