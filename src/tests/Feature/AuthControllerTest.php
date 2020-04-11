<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class AuthControllerTest extends TestCase
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
    public function ユーザーの新規登録ができること()
    {
        $inputName = 'hogetaro';
        $inputEmail = 'aaa@gmail.com';
        $inputPassword = '12345678';
        $inputPasswordRetype = '12345678';


        $data = [ # 登録するユーザー情報
            'name' =>  $inputName,
            'email' => $inputEmail,
            'password' => $inputPassword,
            'password_confirmation' => $inputPasswordRetype
        ];

        // 新規登録
        $response = $this->post('api/auth/create', $data);

        // 新規登録成功
        $response->assertOk()->assertJson([
            'data' => [
                'name' =>  $inputName,
                'email' => $inputEmail
            ]
        ]);

        $loginData = [ #  ログイン用のデータ
            'email' => $inputEmail,
            'password' => $inputPassword
        ];

        // ログイン
        $response = $this->post('api/auth/login', $loginData);

        // ログイン成功
        $response->assertOk();

        // DBに登録されているユーザー情報が入力情報と等しいことを確認
        $dbUser = User::all()[0];
        $this->assertEquals($dbUser['name'], $inputName);
        $this->assertEquals($dbUser['email'], $inputEmail);
    }

    /**
     * @test
     */
    public function ユーザーのパスワード変更ができること()
    {
        $currentPassword = 'test1111';

        // DBに保存された User モデルが返る
        $user = factory(User::class)->create([
            'password'  => bcrypt($currentPassword)
        ]);

        $newPassword = '12345678';
        $newPasswordRetype = '12345678';

        $data = [ # 更新するユーザー情報
            'current_password'=> $currentPassword,
            'new_password' => $newPassword,
            'new_password_confirmation' => $newPasswordRetype
        ];

        $this->actingAs($user);

        // パスワード変更
        $response = $this->put('api/auth/changePassword', $data);

        // 通信成功
        $response->assertOk();

        // 新しいパスワードがハッシュ化されてDBに登録されていることを確認
        $afterUser = User::find($user['id']);
        $this->assertTrue(Hash::check($newPassword, $afterUser['password']));
    }

    /**
     * @test
     */
    public function 現在のパスワードが異なる時､パスワード変更時に405エラーになること()
    {
        $currentPassword = 'test1111';

        // DBに保存された User モデルが返る
        $user = factory(User::class)->create([
            'password'  => bcrypt($currentPassword)
        ]);

        $wrongPassword = 'wrongtest1111';
        $newPassword = '12345678';
        $newPasswordRetype = '12345678';

        $data = [ # 更新するユーザー情報(現在のパスワードに異なる値を設定)
            'current_password'=> $wrongPassword,
            'new_password' => $newPassword,
            'new_password_confirmation' => $newPasswordRetype
        ];

        $this->actingAs($user);

        // パスワード変更
        $response = $this->put('api/auth/changePassword', $data);

        // ステータスが405となり､エラーメッセージが返却されること
        $response->assertStatus(400)->assertJson([
            'message' => '現在のパスワードが異なります'
        ]);
    }


    /**
     * @test
     */
    public function ユーザー削除ができること()
    {
        // DBに保存された User モデルが返る
        $user = factory(User::class)->create([
        ]);

        $this->actingAs($user);

        // パスワード変更
        $response = $this->delete('api/auth/delete');

        // リクエストが成功しDBからユーザーが削除されていること
        $response->assertOk();
        $afterUser = User::find($user['id']);
        $this->assertNull($afterUser);
    }

    /**
     * @test
     */
    public function ログイン・ログアウトができること()
    {
        $passStr = 'test1111';

        // DBに保存された User モデルが返る
        $user = factory(User::class)->create([
            'password'  => bcrypt($passStr)
        ]);

        $data = [ #  ログイン用のデータ
            'email' => $user['email'],
            'password' => $passStr
        ];

        // ログイン
        $response = $this->post('api/auth/login', $data);

        // ログイン成功
        $response->assertOk();

        $headers = [
            'Accept' => 'application/json' ,
            'Authorization' => 'Bearer ' . $response['data']['access_token']
        ];

        // ログアウト
        $response = $this->post('api/auth/logout', $data = [], $headers);

        // ログアウト成功
        $response->assertOk();

        // ログアウト前のトークンでアクセス
        $response = $this->get('api/auth/me', $headers);

        // 認証失敗
        $response->assertStatus(401);
    }


    /**
     * @test
     */
    public function 誤ったパスワードでログインができないこと()
    {
        $passStr = 'test1111';
        $passWrongStr = 'test2222';

        // DBに保存された User モデルが返る
        $user = factory(User::class)->create([
            'password'  => bcrypt($passStr)
        ]);

        $data = [ #  ログイン用のデータ
            'email' => $user['email'],
            'password' => $passWrongStr
        ];

        // POST リクエスト
        $response = $this->post('api/auth/login', $data);

        // 認証失敗
        $response->assertStatus(401);
    }

    /**
     * @test
     */
    public function 誤ったメールアドレスでログインができないこと()
    {
        $passStr = 'test1111';

        // DBに保存された User モデルが返る
        $user = factory(User::class)->create([
            'password'  => bcrypt($passStr)
        ]);

        $data = [ #  ログイン用のデータ
            'email' => $user['email'] . 'foge',
            'password' => $passStr
        ];

        // POST リクエスト
        $response = $this->post('api/auth/login', $data);

        // 認証失敗
        $response->assertStatus(401);
    }

    /**
     * @test
     */
    public function メールアドレスとパスワードが入力されていない状態でログインができないこと()
    {
        $passStr = 'test1111';

        // DBに保存された User モデルが返る
        $user = factory(User::class)->create([
            'password'  => bcrypt($passStr)
        ]);

        // POST リクエスト
        $response = $this->post('api/auth/login');

        // 認証失敗
        $response->assertStatus(401);
    }

    /**
     * @test
     */
    public function refreshでトークンがリフレッシュされること()
    {
        $passStr = 'test1111';

        $user = factory(User::class)->create([
            'password' => bcrypt($passStr)
        ]);

        $this->actingAs($user);

        $beforeRefreshToken = JWTAuth::fromUser($user);

        $headers = ['Accept' => 'application/json', 'Authorization' => 'Bearer ' . $beforeRefreshToken];

        $response = $this->post('/api/auth/refresh', [], $headers);

        // リフレッシュ成功
        $response->assertOk()->assertJson([
           'data' => [
               'access_token' => true
            ]
        ]);

        $afterRefreshToken = $response['data']['access_token'];

        // レスポンスのトークンがリフレッシュ前のトークンと異なることを確認
        $this->assertNotEquals($beforeRefreshToken, $afterRefreshToken);
    }


    /**
     * @test
     */
    public function meで取得するユーザー情報が正しいこと()
    {
        $passStr = 'test1111';

        $user = factory(User::class)->create([
            'password' => bcrypt($passStr)
        ]);

        $this->actingAs($user);

        $response = $this->get('/api/auth/me');

        // レスポンスのユーザー情報が実際のユーザー情報と等しいことを確認
        $response->assertOk()->assertJson([
            'data' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'email_verified_at' => $user['email_verified_at'],
                'created_at' => $user['created_at'],
                'updated_at' => $user['updated_at'],
            ]
        ]);
    }
}
