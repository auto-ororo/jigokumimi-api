<?php

use Illuminate\Database\Seeder;
use App\Models\ArtistAround;

class ArtistsAroundTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //一括削除
        ArtistAround::truncate();

        //特定のデータを追加
        ArtistAround::create([
            'spotify_artist_id' => '2uNKRyEbaWCzKAX5c31wwn',
            'user_id' => 1,
            'longitude' => '139.8002856',
            'latitude' => '35.6947093',
            'popularity' => 48
        ]);
    }
}
