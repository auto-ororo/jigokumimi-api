<?php

use Illuminate\Database\Seeder;
use App\Models\TrackCaptureLog;

class TrackCaptureLogsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //一括削除
        TrackCaptureLog::truncate();

        //特定のデータを追加
        TrackCaptureLog::create([
            'spotify_track_id' => '2uNKRyEbaWCzKAX5c31wwn',
            'spotify_user_id' => 'ueue55sss2v3568ej83tvsgme',
            'longitude' => '139.8002856',
            'latitude' => '35.6947093',
            'popularity' => 48
        ]);
    }
}
