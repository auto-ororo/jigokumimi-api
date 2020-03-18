<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\SongCaptureLog;

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
}
