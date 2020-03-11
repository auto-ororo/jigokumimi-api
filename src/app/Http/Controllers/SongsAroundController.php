<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SongCaptureLog;
use Exception;

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
        $items = SongCaptureLog::orderBy('id')->take($this->DATA_LIMIT)->get();

        return $this->responseToClient('OK', $items, $this->HTTP_OK);
    }

    public function create(Request $request)
    {
        try {
            $items = $request->all();
            foreach ($items as $key => $item) {
                SongCaptureLog::create($item);
            }
            return $this->responseToClient('OK', null, $this->HTTP_OK);
        } catch (Exception $e) {
            return $this->responseToClient('ERROR', $e, $this->HTTP_INTERNAL_ERROR);
        }
    }
}
