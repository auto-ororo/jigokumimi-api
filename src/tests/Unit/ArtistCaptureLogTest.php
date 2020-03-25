<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\ArtistCaptureLog;

class ArtistCaptureLogTest extends TestCase
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
    public function アーティストごとに人気度が集計されること()
    {
        $targetSpotifyArtistId1 = '111';
        $targetSpotifyArtistId2 = '222';

        // 同一アーティストIDで人気度の異なるデータを作成
        $numArray = [20, 80, 50];
        foreach ($numArray as $el) {
            factory(ArtistCaptureLog::class)->create([
                'spotify_artist_id' => $targetSpotifyArtistId1,
                'popularity' => $el
            ]);
        }

        // 上で作成した曲とは異なるIDを持つデータを作成
        factory(ArtistCaptureLog::class)->create([
            'spotify_artist_id' => $targetSpotifyArtistId2,
            'popularity' => 15
        ]);

        $artists = ArtistCaptureLog::sumPopularityByArtists()->orderBy('popularity', 'desc')->get();

        // アーティストIDごとに集計されていることを確認
        $this->assertequals(2, count($artists));

        // 人気度が集計されていることを確認
        $this->assertequals(150, $artists[0]['popularity']);
    }
}
