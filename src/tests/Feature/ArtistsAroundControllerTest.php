<?php

namespace Tests\Feature;

use App\Models\ArtistAround;
use App\Models\ArtistAroundHistory;
use App\Models\History;
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
    public function 登録した周辺アーティスト情報の人気度を取得でき､取得情報が検索履歴に登録されていること()
    {
        // ユーザー登録
        $user = factory(User::class)->create([
            'id' => 1
        ]);
        $this->actingAs($user);

        // 周辺アーティスト情報を登録
        $dummyUser = factory(User::class)->create([
            'id' => 2
        ]);
        $artist = factory(ArtistAround::class)->create([
            'user_id' => $dummyUser['id']
        ]);

        $latitude = $artist['latitude'];
        $longitude = $artist['longitude'];
        $distance = 1000;
        $userId = $user['id'];

        $headers = [
            'Accept' => 'application/json' ,
        ];

        // 登録したアーティスト以外のIDを指定して周辺アーティスト情報を取得
        $response = $this->get("api/artists?userId=${userId}&latitude=${latitude}&longitude=${longitude}&distance=${distance}", $headers);

        // 登録内容とレスポンスが等しいことを確認
        $response->assertOk()->assertJson([
            'data' => [[
                'spotify_artist_id' => $artist['spotify_artist_id'],
                'popularity' => $artist['popularity'],
            ]]
        ]);

        // 検索履歴(History,ArtistAroundHistory)に取得結果が保存されていることを確認
        // History
        $history = History::where('user_id', $userId)->get();
        $this->assertEquals($history[0]['latitude'], $latitude);
        $this->assertEquals($history[0]['longitude'], $longitude);
        $this->assertEquals($history[0]['distance'], $distance);
        // ArtistAroundHistory
        $artistAroundHistory = ArtistAroundHistory::where('history_id', $history[0]['id'])->get()[0];
        $this->assertEquals($artistAroundHistory['popularity'], $artist['popularity']);
        $this->assertEquals($artistAroundHistory['spotify_artist_id'], $artist['spotify_artist_id']);

    }

    /**
     * @test
     */
    public function 周辺アーティスト情報の最大取得件数が25件以下であること()
    {
        $latitudeOfSkyTree = 35.709544;
        $longitudeOfSkyTree = 139.809049;
        $excludeUserId = 2;
        $distance = 1000;

        factory(User::class)->create([
            'id' => 1
        ]);

        // 周辺アーティスト情報を30件登録
        for ($i=0; $i < 30; $i++) {
            factory(ArtistAround::class)->create([
                'user_id' => 1 ,
                'latitude' =>  $latitudeOfSkyTree,
                'longitude' => $longitudeOfSkyTree
            ]);
        }

        $user = factory(User::class)->create([
            'id' => $excludeUserId
        ]);
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
        $user = factory(User::class)->create();
        $this->actingAs($user);

        // リクエストBody作成
        $requestBody = [
            [
                'spotify_artist_id' => '1234567890abcdefg',
                'user_id' => $user['id'],
                'longitude' => 12.345678,
                'latitude' => 21.345678,
                'popularity' => 24
            ],
            [
              'spotify_artist_id' => '21234567890abcdefg',
                'user_id' => $user['id'],
                'longitude' => 212.345678,
                'latitude' => 221.345678,
                'popularity' => 224
            ]
        ];

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
            $this->assertEquals($artists[$i]['user_id'], $requestBody[$i]['user_id']);
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
                'user_id' => 'gfedcba0987654321',
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

    /**
     * @test
     */
    public function 周辺アーティスト情報の検索履歴を取得できること()
    {
        // テスト用ユーザー作成
        $user = factory(User::class)->create([
            'id' => 1
        ]);
        $this->actingAs($user);
        $userId = $user['id'];

        // テスト用アーティスト検索履歴情報(History, ArtistAroundHistory)を作成
        $history = factory(History::class)->create([
            'user_id' => $userId
        ]);
        $historyId = $history['id'];
        $artistAroundHistory = factory(ArtistAroundHistory::class)->create([
            'history_id' => $historyId
        ]);

        // 取得条件から外れるテストデータを作成
        $dummyUser  = factory(User::class)->create([
            'id' => 2
        ]);
        $dummyHistory = factory(History::class)->create([
            'user_id' => $dummyUser['id']
        ]);
        factory(ArtistAroundHistory::class)->create([
            'history_id' => $dummyHistory['id']
        ]);

        // 生成したユーザーIDをパラメータに設定してリクエスト実行
        $headers = [
            'Accept' => 'application/json'
        ];
        $userIdStr = (string)$userId;
        $uri = "api/artists/history?userId=" . $userIdStr;
        $response = $this->get($uri, $headers);

        // リクエストが成功し､検索履歴が正しいJSON形式で取得できることを確認
        $response->assertOk()->assertJson([
            'message' => 'OK',
            'data' => [
                [
                    'id' => $history['id'],
                    'user_id' => $history['user_id'],
                    'latitude' => $history['latitude'],
                    'longitude' => $history['longitude'],
                    'distance' => $history['distance'],
                    'created_at' => $history['created_at'],
                    'artists_around_histories' => [
                        [
                            'rank' => $artistAroundHistory['rank'],
                            'popularity' => $artistAroundHistory['popularity'],
                            'spotify_artist_id' => $artistAroundHistory['spotify_artist_id']
                        ]
                    ]
                ]
            ]
        ]);

        // データが1件取得されることを確認
        $this->assertEquals(1, count($response['data']));
    }

    /**
     * @test
     */
    public function 周辺アーティスト情報の検索履歴を削除できること()
    {
        // テスト用ユーザー作成
        $user = factory(User::class)->create();
        $this->actingAs($user);
        $userId = $user['id'];

        // テスト用アーティスト検索履歴情報(History, ArtistAroundHistory)を作成
        $history = factory(History::class)->create([
            'user_id' => $userId
        ]);
        $historyId = $history['id'];
        factory(ArtistAroundHistory::class)->create([
            'history_id' => $historyId
        ]);

        // 生成したユーザーIDをパラメータに設定してリクエスト実行
        $headers = [
            'Accept' => 'application/json'
        ];
        $historyIdStr = (string)$historyId;
        $uri = "api/artists/history?historyId=" . $historyIdStr;
        $response = $this->delete($uri, $headers);

        // リクエストが成功し､検索履歴が正しいJSON形式で取得できることを確認
        $response->assertOk()->assertJson([
            'message' => 'OK'
        ]);

        // データが削除されていることを確認
        $resHistory = History::find($historyId);
        $this->assertNull($resHistory);
        $resArtistAroundHistory = ArtistAroundHistory::where('history_id', $historyId)->get();
        $this->assertEquals(0, count($resArtistAroundHistory));
    }
}
