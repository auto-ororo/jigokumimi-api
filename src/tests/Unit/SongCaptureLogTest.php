<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\SongCaptureLog;
use App\Models\User;

class SongCaptureLogTest extends TestCase
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
        $song = factory(SongCaptureLog::class)->create([
            'latitude' => $latitudeOfSkyTree,
            'longitude' => $longitudeOfSkyTree
        ]);

        // 基準となる位置情報を押上駅に設定し､半径500m以内の周辺曲情報を検索
        $latitudeOfOshiageStation = 35.710332;
        $longitudeOfOshiageStation = 139.813297;
        $distance = 500;
        $withInSong = SongCaptureLog::withinDistance($latitudeOfOshiageStation, $longitudeOfOshiageStation, $distance)->first();

        // スカイツリーの周辺曲情報が取得できていることを確認
        $this->assertEquals($song['id'], $withInSong['id']);
    }

    /**
     * @test
     */
    public function 指定した距離外に存在する周辺曲情報が取得できないこと()
    {

        // 位置情報を東京スカイツリーに設定した周辺曲情報を登録
        $latitudeOfSkyTree = 35.709544;
        $longitudeOfSkyTree = 139.809049;
        $song = factory(SongCaptureLog::class)->create([
            'latitude' => $latitudeOfSkyTree,
            'longitude' => $longitudeOfSkyTree
        ]);

        // 基準となる位置情報を東京タワーに設定し､半径500m以内の周辺曲情報を検索
        $latitudeOfTokyoTower = 35.658580;
        $longitudeOfTokyoTower = 139.745433;
        $distance = 500;
        $withInSong = SongCaptureLog::withinDistance($latitudeOfTokyoTower, $longitudeOfTokyoTower, $distance)->first();

        // スカイツリーの周辺曲情報が取得できていることを確認
        $this->assertNull($withInSong);
    }

    /**
     * @test
     */
    public function 同距離の周辺曲情報が取得できること()
    {

        // 位置情報を東京スカイツリーに設定した周辺曲情報を登録
        $latitudeOfSkyTree = 35.709544;
        $longitudeOfSkyTree = 139.809049;
        $song = factory(SongCaptureLog::class)->create([
            'latitude' => $latitudeOfSkyTree,
            'longitude' => $longitudeOfSkyTree
        ]);

        // 基準となる位置情報をスカイツリーに設定し､半径500m以内の周辺曲情報を検索
        $distance = 500;
        $withInSong = SongCaptureLog::withinDistance($latitudeOfSkyTree, $longitudeOfSkyTree, $distance)->first();

        // スカイツリーの周辺曲情報が取得できていることを確認
        $this->assertEquals($song['id'], $withInSong['id']);
    }

    /**
     * @test
     */
    public function 指定ユーザーが周辺曲情報から除外されること()
    {
        $targetspotifyuserid = 'asdffdsa';

        factory(songcapturelog::class)->create([
            'spotify_user_id' => $targetspotifyuserid
        ]);

        $includespotifyuserid = '12345678';

        $targetsong = factory(songcapturelog::class)->create([
            'spotify_user_id' => $includespotifyuserid
        ]);
        $withinsongs = songcapturelog::excludeuser($targetspotifyuserid)->get();

        $this->assertequals(1, count($withinsongs));

        // スカイツリーの周辺曲情報が取得できていることを確認
        $this->assertequals($targetsong['id'], $withinsongs[0]['id']);
    }

    /**
     * @test
     */
    public function 曲ごとに人気度が集計されること()
    {
        $targetSpotifySongId1 = '111';
        $targetSpotifySongId2 = '222';

        // 同一曲IDで人気度の異なるデータを作成
        $numArray = [20, 80, 50];
        foreach ($numArray as $el) {
            factory(songcapturelog::class)->create([
                'spotify_song_id' => $targetSpotifySongId1,
                'popularity' => $el
            ]);
        }

        // 上で作成した曲とは異なるIDを持つデータを作成
        factory(songcapturelog::class)->create([
            'spotify_song_id' => $targetSpotifySongId2,
            'popularity' => 15
        ]);

        $songs = songcapturelog::sumPopularityBySongs()->orderBy('popularity', 'desc')->get();

        // 曲IDごとに集計されていることを確認
        $this->assertequals(2, count($songs));

        // 人気度が集計されていることを確認
        $this->assertequals(150, $songs[0]['popularity']);
    }
}
