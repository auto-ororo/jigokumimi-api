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
    public function 指定した距離以内に存在する周辺曲情報が取得できること()
    {

        // 位置情報を東京スカイツリーに設定した周辺曲情報を登録
        $latitudeOfSkyTree = 35.709544;
        $longitudeOfSkyTree = 139.809049;
        $track = factory(TrackCaptureLog::class)->create([
            'latitude' => $latitudeOfSkyTree,
            'longitude' => $longitudeOfSkyTree
        ]);

        // 基準となる位置情報を押上駅に設定し､半径500m以内の周辺曲情報を検索
        $latitudeOfOshiageStation = 35.710332;
        $longitudeOfOshiageStation = 139.813297;
        $distance = 500;
        $withInTrack = TrackCaptureLog::withinDistance($latitudeOfOshiageStation, $longitudeOfOshiageStation, $distance)->first();

        // スカイツリーの周辺曲情報が取得できていることを確認
        $this->assertEquals($track['id'], $withInTrack['id']);
    }

    /**
     * @test
     */
    public function 指定した距離外に存在する周辺曲情報が取得できないこと()
    {

        // 位置情報を東京スカイツリーに設定した周辺曲情報を登録
        $latitudeOfSkyTree = 35.709544;
        $longitudeOfSkyTree = 139.809049;
        $track = factory(TrackCaptureLog::class)->create([
            'latitude' => $latitudeOfSkyTree,
            'longitude' => $longitudeOfSkyTree
        ]);

        // 基準となる位置情報を東京タワーに設定し､半径500m以内の周辺曲情報を検索
        $latitudeOfTokyoTower = 35.658580;
        $longitudeOfTokyoTower = 139.745433;
        $distance = 500;
        $withInTrack = TrackCaptureLog::withinDistance($latitudeOfTokyoTower, $longitudeOfTokyoTower, $distance)->first();

        // スカイツリーの周辺曲情報が取得できていることを確認
        $this->assertNull($withInTrack);
    }

    /**
     * @test
     */
    public function 同距離の周辺曲情報が取得できること()
    {

        // 位置情報を東京スカイツリーに設定した周辺曲情報を登録
        $latitudeOfSkyTree = 35.709544;
        $longitudeOfSkyTree = 139.809049;
        $track = factory(TrackCaptureLog::class)->create([
            'latitude' => $latitudeOfSkyTree,
            'longitude' => $longitudeOfSkyTree
        ]);

        // 基準となる位置情報をスカイツリーに設定し､半径500m以内の周辺曲情報を検索
        $distance = 500;
        $withInTrack = TrackCaptureLog::withinDistance($latitudeOfSkyTree, $longitudeOfSkyTree, $distance)->first();

        // スカイツリーの周辺曲情報が取得できていることを確認
        $this->assertEquals($track['id'], $withInTrack['id']);
    }

    /**
     * @test
     */
    public function 指定ユーザーが周辺曲情報から除外されること()
    {
        $targetspotifyuserid = 'asdffdsa';

        factory(trackcapturelog::class)->create([
            'spotify_user_id' => $targetspotifyuserid
        ]);

        $includespotifyuserid = '12345678';

        $targettrack = factory(trackcapturelog::class)->create([
            'spotify_user_id' => $includespotifyuserid
        ]);
        $withintracks = trackcapturelog::excludeuser($targetspotifyuserid)->get();

        $this->assertequals(1, count($withintracks));

        // スカイツリーの周辺曲情報が取得できていることを確認
        $this->assertequals($targettrack['id'], $withintracks[0]['id']);
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
