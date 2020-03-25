<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\ArtistCaptureLog;
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

$factory->define(ArtistCaptureLog::class, function (Faker $faker) {
    return [
        'spotify_artist_id' => $faker->shuffle('abcdefghijklmnopqrstuvwx'),
        'spotify_user_id' => $faker->shuffle('abcdefghijklmnopqrstuvwxyz1'),
        'latitude' => $faker->latitude(),
        'longitude' => $faker->longitude(),
        'popularity' => $faker->numberBetween(0, 100)
    ];
});
