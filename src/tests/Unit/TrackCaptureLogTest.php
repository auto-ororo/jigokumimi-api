<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\TrackCaptureLog;
use App\Models\User;

class TrackCaptureLogTest extends TestCase
{
    use RefreshDatabase;

    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     */
    public function 曲ごとに人気度が集計されること()
    {
        $targetSpotifyTrackId1 = '111';
        $targetSpotifyTrackId2 = '222';

        // 同一曲IDで人気度の異なるデータを作成
        $numArray = [20, 80, 50];
        foreach ($numArray as $el) {
            factory(trackcapturelog::class)->create([
                'spotify_track_id' => $targetSpotifyTrackId1,
                'popularity' => $el
            ]);
        }

        // 上で作成した曲とは異なるIDを持つデータを作成
        factory(trackcapturelog::class)->create([
            'spotify_track_id' => $targetSpotifyTrackId2,
            'popularity' => 15
        ]);

        $tracks = trackcapturelog::sumPopularityByTracks()->orderBy('popularity', 'desc')->get();

        // 曲IDごとに集計されていることを確認
        $this->assertequals(2, count($tracks));

        // 人気度が集計されていることを確認
        $this->assertequals(150, $tracks[0]['popularity']);
    }
}
