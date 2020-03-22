<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    /**
     * コンストラクタ
     * loginをトークン認証から除外
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    /**
     * ログイン
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (! $token = auth('api')->attempt($credentials)) {
            return $this->responseToClient('メールアドレス、またはパスワードが異なります。', null, 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * ユーザー情報取得
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $tmp = auth('api');
        $udata = $tmp->user();

        return $this->responseToClient('OK', $udata, 200);
    }

    /**
     * ログアウト
     * ※ログアウト前に使用していたトークンは使えなくなる
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth('api')->logout();

        return $this->responseToClient('Successfully logged out', null, 200);
    }

    /**
     * トークンを更新
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth('api')->refresh());
    }

    /**
     * トークンを添えてレスポンス返却
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        $data = [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ];
        return $this->responseToClient('Token Generated', $data, 200);
    }
}
