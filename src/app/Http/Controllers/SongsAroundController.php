<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SongCaptureLog;

class SongsAroundController extends Controller
{
    /**
     * コンストラクタ
     * トークン認証の除外設定
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['index','create']]);
    }

    public function index(Request $request)
    {
        $items = SongCaptureLog::all();
        return response($items, 200);
    }

    public function create(Request $request)
    {
        try {
            $items = $request->all();
            foreach ($items as $key => $item) {
                SongCaptureLog::create($item);
            }
            return response($request->all(), 200);
        } catch (Throwable $th) {
            return response($th, 500);
        }
    }
}
