<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\ArtistAroundHistory;
use App\Models\History;
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

$factory->define(ArtistAroundHistory::class, function (Faker $faker) {
    return [
        'history_id' => function() {
            return factory(History::class)->create()->id;
        },
        'spotify_artist_id' => $faker->shuffle('abcdefghijklmnopqrstuvwx'),
        'rank' => $faker->numberBetween(0, 100),
        'popularity' => $faker->numberBetween(0, 100),
    ];
});
