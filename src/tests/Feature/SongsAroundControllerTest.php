<?php

namespace Tests\Feature;

use App\Models\SongCaptureLog;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class SongsAroundControllerTest extends TestCase
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
    public function 登録した周辺曲情報の人気度を取得できること()
    {
        // 周辺曲情報を登録
        $song = factory(SongCaptureLog::class)->create();

        $latitude = $song['latitude'];
        $longitude = $song['longitude'];
        $excludeUserId = $song['spotify_user_id'] . 'exclude';
        $distance = 1000;

        // $params = [
        //     'userId' => $excludeUserId,
        //     'latitude' => $latitude,
        //     'longitude' => $longitude,
        //     'distance' => $distance
        // ];

        // 登録した曲以外の曲IDを指定して周辺曲情報を取得
        $response = $this->get("api/songs?userId=${excludeUserId}&latitude=${latitude}&longitude=${longitude}&distance=${distance}");

        // 登録内容とレスポンスが等しいことを確認
        $response->assertOk()->assertJson([
            'data' => [[
                'spotify_song_id' => $song['spotify_song_id'],
                'popularity' => $song['popularity'],
            ]]
        ]);
    }

    /**
     * @test
     */
    public function 周辺曲情報の最大取得件数が25件以下であること()
    {
        $latitudeOfSkyTree = 35.709544;
        $longitudeOfSkyTree = 139.809049;
        $excludeUserId = '222';
        $distance = 1000;

        // 周辺曲情報を30件登録
        for ($i=0; $i < 30; $i++) {
            factory(SongCaptureLog::class)->create([
                'latitude' =>  $latitudeOfSkyTree,
                'longitude' => $longitudeOfSkyTree
            ]);
        }

        // 登録した曲以外の曲IDを指定して周辺曲情報を取得
        $response = $this->get("api/songs?userId=${excludeUserId}&latitude=${latitudeOfSkyTree}&longitude=${longitudeOfSkyTree}&distance=${distance}");

        $data = $response['data'];
        $this->assertCount(25, $data);
    }

    /**
     * @test
     */
    public function 周辺曲情報を複数登録できること()
    {
        // リクエストBody作成
        $requestBody = [
            [
                'spotify_song_id' => '1234567890abcdefg',
                'spotify_user_id' => 'gfedcba0987654321',
                'longitude' => 12.345678,
                'latitude' => 21.345678,
                'popularity' => 24
            ],
            [
              'spotify_song_id' => '21234567890abcdefg',
                'spotify_user_id' => '2gfedcba0987654321',
                'longitude' => 212.345678,
                'latitude' => 221.345678,
                'popularity' => 224
            ]
        ];

        // リクエストBodyを元に周辺曲情報が作成されることを確認
        $response = $this->post('api/songs', $requestBody);
        $response->assertOk()->assertJson([
            'message' => 'OK'
        ]);

        $songs = SongCaptureLog::orderBy('id', 'asc')->get();

        // データが2件登録されていることを確認
        $this->assertEquals(2, count($songs));

        // 登録内容も併せて確認する
        for ($i=0; $i < 2; $i++) {
            $this->assertEquals($songs[$i]['spotify_song_id'], $requestBody[$i]['spotify_song_id']);
            $this->assertEquals($songs[$i]['spotify_user_id'], $requestBody[$i]['spotify_user_id']);
            $this->assertEquals($songs[$i]['longitude'], $requestBody[$i]['longitude']);
            $this->assertEquals($songs[$i]['latitude'], $requestBody[$i]['latitude']);
            $this->assertEquals($songs[$i]['popularity'], $requestBody[$i]['popularity']);
        }
    }

    /**
     * @test
     */
    public function 入力情報が不足した状態で周辺曲情報の登録ができないこと()
    {
        // 入力情報が不足しているリクエストBody作成
        $requestBody = [
            [
                'spotify_song_id' => '1234567890abcdefg',
                'spotify_user_id' => 'gfedcba0987654321',
                'longitude' => 12.345678,
                'latitude' => 21.345678
                // 'popularity' => 24 ←コメントアウト
            ]
        ];

        // 登録を試みるとエラーレスポンスが返却されることを確認
        $response = $this->post('api/songs', $requestBody);
        $response->assertStatus(400)->assertJson([
            'status' => 400
        ]);

        // データが登録されていないことを確認
        $song = SongCaptureLog::all();
        $this->assertEquals(0, count($song));
    }
}
