<?php

namespace Tests\Feature;

use App\Models\ArtistAround;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class ArtistsAroundControllerTest extends TestCase
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
    public function 登録した周辺アーティスト情報の人気度を取得できること()
    {
        // 周辺アーティスト情報を登録
        $artist = factory(ArtistAround::class)->create();

        $latitude = $artist['latitude'];
        $longitude = $artist['longitude'];
        $excludeUserId = $artist['spotify_user_id'] . 'exclude';
        $distance = 1000;

        $user = factory(User::class)->create();
        $this->actingAs($user);

        $headers = [
            'Accept' => 'application/json' ,
        ];

        // 登録したアーティスト以外のIDを指定して周辺アーティスト情報を取得
        $response = $this->get("api/artists?userId=${excludeUserId}&latitude=${latitude}&longitude=${longitude}&distance=${distance}", $headers);

        // 登録内容とレスポンスが等しいことを確認
        $response->assertOk()->assertJson([
            'data' => [[
                'spotify_artist_id' => $artist['spotify_artist_id'],
                'popularity' => $artist['popularity'],
            ]]
        ]);
    }

    /**
     * @test
     */
    public function 周辺アーティスト情報の最大取得件数が25件以下であること()
    {
        $latitudeOfSkyTree = 35.709544;
        $longitudeOfSkyTree = 139.809049;
        $excludeUserId = '222';
        $distance = 1000;

        // 周辺アーティスト情報を30件登録
        for ($i=0; $i < 30; $i++) {
            factory(ArtistAround::class)->create([
                'latitude' =>  $latitudeOfSkyTree,
                'longitude' => $longitudeOfSkyTree
            ]);
        }

        $user = factory(User::class)->create();
        $this->actingAs($user);

        $headers = [
            'Accept' => 'application/json' ,
        ];

        // 登録したアーティスト以外のアーティストIDを指定して周辺アーティスト情報を取得
        $response = $this->get("api/artists?userId=${excludeUserId}&latitude=${latitudeOfSkyTree}&longitude=${longitudeOfSkyTree}&distance=${distance}", $headers);

        $data = $response['data'];
        $this->assertCount(25, $data);
    }

    /**
     * @test
     */
    public function 周辺アーティスト情報を複数登録できること()
    {
        // リクエストBody作成
        $requestBody = [
            [
                'spotify_artist_id' => '1234567890abcdefg',
                'spotify_user_id' => 'gfedcba0987654321',
                'longitude' => 12.345678,
                'latitude' => 21.345678,
                'popularity' => 24
            ],
            [
              'spotify_artist_id' => '21234567890abcdefg',
                'spotify_user_id' => '2gfedcba0987654321',
                'longitude' => 212.345678,
                'latitude' => 221.345678,
                'popularity' => 224
            ]
        ];

        $user = factory(User::class)->create();
        $this->actingAs($user);

        $headers = [
            'Accept' => 'application/json' ,
        ];

        // リクエストBodyを元に周辺アーティスト情報が作成されることを確認
        $response = $this->post('api/artists', $requestBody, $headers);
        $response->assertOk()->assertJson([
            'message' => 'OK'
        ]);

        $artists = ArtistAround::orderBy('id', 'asc')->get();

        // データが2件登録されていることを確認
        $this->assertEquals(2, count($artists));

        // 登録内容も併せて確認する
        for ($i=0; $i < 2; $i++) {
            $this->assertEquals($artists[$i]['spotify_artist_id'], $requestBody[$i]['spotify_artist_id']);
            $this->assertEquals($artists[$i]['spotify_user_id'], $requestBody[$i]['spotify_user_id']);
            $this->assertEquals($artists[$i]['longitude'], $requestBody[$i]['longitude']);
            $this->assertEquals($artists[$i]['latitude'], $requestBody[$i]['latitude']);
            $this->assertEquals($artists[$i]['popularity'], $requestBody[$i]['popularity']);
        }
    }

    /**
     * @test
     */
    public function 入力情報が不足した状態で周辺アーティスト情報の登録ができないこと()
    {
        // 入力情報が不足しているリクエストBody作成
        $requestBody = [
            [
                'spotify_artist_id' => '1234567890abcdefg',
                'spotify_user_id' => 'gfedcba0987654321',
                'longitude' => 12.345678,
                'latitude' => 21.345678
                // 'popularity' => 24 ←コメントアウト
            ]
        ];

        $user = factory(User::class)->create();
        $this->actingAs($user);

        $headers = [ 'Accept' => 'application/json' ,
        ];

        // 登録を試みるとエラーレスポンスが返却されることを確認
        $response = $this->post('api/artists', $requestBody, $headers);
        $response->assertStatus(400);

        // データが登録されていないことを確認
        $artist = ArtistAround::all();
        $this->assertEquals(0, count($artist));
    }
}
