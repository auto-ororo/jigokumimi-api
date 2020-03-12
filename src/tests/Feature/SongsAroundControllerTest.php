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
    public function 登録した周辺曲情報を取得できること()
    {

        // 周辺曲情報を登録
        $song = factory(SongCaptureLog::class)->create();

        // 周辺曲情報を取得
        $response = $this->get('api/songs');

        // 登録内容とレスポンスが等しいことを確認
        $response->assertOk()->assertJson([
            'data' => [[
                'id' => $song['id'],
                'spotify_song_id' => $song['spotify_song_id'],
                'spotify_user_id' => $song['spotify_user_id'],
                'latitude' => $song['latitude'],
                'longitude' => $song['longitude'],
                'popularity' => $song['popularity'],
                'created_at' => $song['created_at'],
                'updated_at' => $song['updated_at'],
            ]]
        ]);
    }

    /**
     * @test
     */
    public function 周辺曲情報の最大取得件数が25件以下であること()
    {
        // 周辺曲情報を30件登録
        for ($i=0; $i < 30; $i++) {
            factory(SongCaptureLog::class)->create();
        }

        // 周辺曲情報を取得
        $response = $this->get('api/songs');

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

        // リクエストBodyに設定した値が返却されることを確認
        $response = $this->get('api/songs');
        $response->assertOk()->assertJson([
            'message' => 'OK',
           'data' => $requestBody
        ]);
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
        $response->assertStatus(500)->assertJson([
            'message' => 'ERROR'
        ]);

        // データが登録されていないことを確認
        $song = SongCaptureLog::all();
        $this->assertEquals(0, count($song));
    }
}
