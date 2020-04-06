<?php

namespace Tests\Feature;

use App\Models\TrackAround;
use App\Models\TrackAroundHistory;
use App\Models\History;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class TracksAroundControllerTest extends TestCase
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
    public function 登録した周辺曲情報の人気度を取得でき､取得情報が検索履歴に登録されていること()
    {
        // 検索条件に該当するユーザーを生成
        $dummuUser = factory(User::class)->create();

        // 周辺曲情報を登録
        $track = factory(TrackAround::class)->create([
            'user_id' => $dummuUser['id']
        ]);

        $latitude = $track['latitude'];
        $longitude = $track['longitude'];
        $distance = 1000;

        $user = factory(User::class)->create();
        $this->actingAs($user);
        $excludeUserId = $user['id'];

        $headers = [
            'Accept' => 'application/json',
        ];

        // 登録した曲以外の曲IDを指定して周辺曲情報を取得
        $response = $this->get("api/tracks?userId=${excludeUserId}&latitude=${latitude}&longitude=${longitude}&distance=${distance}", $headers);

        // 登録内容とレスポンスが等しいことを確認
        $response->assertOk()->assertJson([
            'data' => [[
                'spotify_track_id' => $track['spotify_track_id'],
                'popularity' => $track['popularity'],
            ]]
        ]);

        // 検索履歴(History,TrackAroundHistory)に取得結果が保存されていることを確認
        // History
        $history = History::where('user_id', $excludeUserId)->get();
        $this->assertEquals($history[0]['latitude'], $latitude);
        $this->assertEquals($history[0]['longitude'], $longitude);
        $this->assertEquals($history[0]['distance'], $distance);
        // TrackAroundHistory
        $trackAroundHistory = TrackAroundHistory::where('history_id', $history[0]['id'])->get()[0];
        $this->assertEquals($trackAroundHistory['popularity'], $track['popularity']);
        $this->assertEquals($trackAroundHistory['spotify_track_id'], $track['spotify_track_id']);
    }

    /**
     * @test
     */
    public function 周辺曲情報の最大取得件数が25件以下であること()
    {
        // 検索条件に該当するユーザーを生成
        $dummuUser = factory(User::class)->create();

        $user = factory(User::class)->create();
        $excludeUserId = $user['id'];
        $this->actingAs($user);

        $latitudeOfSkyTree = 35.709544;
        $longitudeOfSkyTree = 139.809049;
        $distance = 1000;

        // 周辺曲情報を30件登録
        for ($i=0; $i < 30; $i++) {
            factory(TrackAround::class)->create([
                'user_id' => $dummuUser['id'],
                'latitude' =>  $latitudeOfSkyTree,
                'longitude' => $longitudeOfSkyTree
            ]);
        }

        $headers = [
            'Accept' => 'application/json' ,
        ];

        // 登録した曲以外の曲IDを指定して周辺曲情報を取得
        $response = $this->get("api/tracks?userId=${excludeUserId}&latitude=${latitudeOfSkyTree}&longitude=${longitudeOfSkyTree}&distance=${distance}", $headers);

        $data = $response['data'];
        $this->assertCount(25, $data);
    }

    /**
     * @test
     */
    public function 周辺曲情報を複数登録できること()
    {

        $user = factory(User::class)->create();

        // リクエストBody作成
        $requestBody = [
            [
                'spotify_track_id' => '1234567890abcdefg',
                'user_id' => $user['id'],
                'longitude' => 12.345678,
                'latitude' => 21.345678,
                'popularity' => 24
            ],
            [
              'spotify_track_id' => '21234567890abcdefg',
                'user_id' => $user['id'],
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

        // リクエストBodyを元に周辺曲情報が作成されることを確認
        $response = $this->post('api/tracks', $requestBody, $headers);
        $response->assertOk()->assertJson([
            'message' => 'OK'
        ]);

        $tracks = TrackAround::orderBy('id', 'asc')->get();

        // データが2件登録されていることを確認
        $this->assertEquals(2, count($tracks));

        // 登録内容も併せて確認する
        for ($i=0; $i < 2; $i++) {
            $this->assertEquals($tracks[$i]['spotify_track_id'], $requestBody[$i]['spotify_track_id']);
            $this->assertEquals($tracks[$i]['user_id'], $requestBody[$i]['user_id']);
            $this->assertEquals($tracks[$i]['longitude'], $requestBody[$i]['longitude']);
            $this->assertEquals($tracks[$i]['latitude'], $requestBody[$i]['latitude']);
            $this->assertEquals($tracks[$i]['popularity'], $requestBody[$i]['popularity']);
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
                'spotify_track_id' => '1234567890abcdefg',
                'user_id' => 1,
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
        $response = $this->post('api/tracks', $requestBody, $headers);
        $response->assertStatus(400);

        // データが登録されていないことを確認
        $track = TrackAround::all();
        $this->assertEquals(0, count($track));
    }


    /**
     * @test
     */
    public function 周辺曲情報の検索履歴を取得できること()
    {
        // テスト用ユーザー作成
        $user = factory(User::class)->create([
            'id' => 1
        ]);
        $this->actingAs($user);
        $userId = $user['id'];

        // テスト用曲検索履歴情報(History, TrackAroundHistory)を作成
        $history = factory(History::class)->create([
            'user_id' => $userId
        ]);
        $historyId = $history['id'];
        $trackAroundHistory = factory(TrackAroundHistory::class)->create([
            'history_id' => $historyId
        ]);

        // 取得条件から外れるテストデータを作成
        $dummyUser  = factory(User::class)->create([
            'id' => 2
        ]);
        $dummyHistory = factory(History::class)->create([
            'user_id' => $dummyUser['id']
        ]);
        factory(TrackAroundHistory::class)->create([
            'history_id' => $dummyHistory['id']
        ]);

        // 生成したユーザーIDをパラメータに設定してリクエスト実行
        $headers = [
            'Accept' => 'application/json'
        ];
        $userIdStr = (string)$userId;
        $uri = "api/tracks/history?userId=" . $userIdStr;
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
                    'tracks_around_histories' => [
                        [
                            'rank' => $trackAroundHistory['rank'],
                            'popularity' => $trackAroundHistory['popularity'],
                            'spotify_track_id' => $trackAroundHistory['spotify_track_id']
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
    public function 周辺曲情報の検索履歴を削除できること()
    {
        // テスト用ユーザー作成
        $user = factory(User::class)->create();
        $this->actingAs($user);
        $userId = $user['id'];

        // テスト用曲検索履歴情報(History, TrackAroundHistory)を作成
        $history = factory(History::class)->create([
            'user_id' => $userId
        ]);
        $historyId = $history['id'];
        factory(TrackAroundHistory::class)->create([
            'history_id' => $historyId
        ]);

        // 生成したユーザーIDをパラメータに設定してリクエスト実行
        $headers = [
            'Accept' => 'application/json'
        ];
        $historyIdStr = (string)$historyId;
        $uri = "api/tracks/history?historyId=" . $historyIdStr;
        $response = $this->delete($uri, $headers);

        // リクエストが成功し､検索履歴が正しいJSON形式で取得できることを確認
        $response->assertOk()->assertJson([
            'message' => 'OK'
        ]);

        // データが削除されていることを確認
        $resHistory = History::find($historyId);
        $this->assertNull($resHistory);
        $resTrackAroundHistory = TrackAroundHistory::where('history_id', $historyId)->get();
        $this->assertEquals(0, count($resTrackAroundHistory));
    }
}
