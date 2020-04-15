<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Exception;
use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Models\User;

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
        $this->middleware('auth:api', ['except' => ['login', 'create']]);
    }

    /**
     * ユーザー登録
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(AuthRequest $request)
    {
        try {
            $user = new User;
            $user->fill($request->all());
            $user->password = bcrypt($request->password);
            $user->save();

            return $this->responseToClient('OK', $user, 200);
        } catch (Exception $e) {
            return $this->responseToClient('ERROR', $e, $this->HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * パスワード変更
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            if (!Hash::check($request['current_password'], auth('api')->user()->password)) {
                return $this->responseToClient('現在のパスワードが異なります', null, 400);
            }
            $user = auth('api')->user();
            $user['password'] = bcrypt($request['new_password']);
            $user->save();
            return $this->responseToClient('OK', null, 200);
        } catch (Exception $e) {
            return $this->responseToClient('ERROR', $e, $this->HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * ユーザー削除
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy()
    {
        try {
            // 認証ユーザーからIDを取得し削除
            $userId = auth('api')->user()->id;
            User::destroy($userId);

            return $this->responseToClient('OK', null, 200);
        } catch (Exception $e) {
            return $this->responseToClient('ERROR', $e, $this->HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * ログイン
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        try {
            $credentials = request(['email', 'password']);

            if (! $token = auth('api')->attempt($credentials)) {
                return $this->responseToClient('メールアドレス、またはパスワードが異なります。', null, 401);
            }

            return $this->respondWithToken($token);
        } catch (Exception $e) {
            $data = [
                "DB_HOST" => env('DB_HOST', 'unknown'),
                "DB_DATABASE" => env('DB_DATABASE', 'unknown'),
                "DB_PORT" => env('DB_PORT', 'unknown'),
                "DB_USERNAME" => env('DB_USERNAME', 'unknown'),
                "DB_PASSWORD" => env('DB_PASSWORD', 'unknown')
            ];
            return $this->responseToClient('ERROR',$data , $this->HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * ユーザー情報取得
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        try {
            $udata = auth('api')->user();
            return $this->responseToClient('OK', $udata, 200);
        } catch (Exception $e) {
            return $this->responseToClient('ERROR', $e, $this->HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * ログアウト
     * ※ログアウト前に使用していたトークンは使えなくなる
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        try {
            auth('api')->logout();
            return $this->responseToClient('Successfully logged out', null, 200);
        } catch (Exception $e) {
            return $this->responseToClient('ERROR', $e, $this->HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * トークンを更新
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        try {
            return $this->respondWithToken(auth('api')->refresh());
        } catch (Exception $e) {
            return $this->responseToClient('ERROR', $e, $this->HTTP_INTERNAL_ERROR);
        }
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
            'id' => auth('api')->user()->id,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ];
        return $this->responseToClient('Token Generated', $data, 200);
    }
}
