<?php

use Illuminate\Database\Seeder;
use App\Models\SongCaptureLog;

class SongCaptureLogsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //一括削除
        SongCaptureLog::truncate();

        //特定のデータを追加
        SongCaptureLog::create([
            'spotify_song_id' => '2uNKRyEbaWCzKAX5c31wwn',
            'spotify_user_id' => 'ueue55sss2v3568ej83tvsgme',
            'longitude' => '139.8002856',
            'latitude' => '35.6947093',
            'popularity' => 48
        ]);
    }
}
