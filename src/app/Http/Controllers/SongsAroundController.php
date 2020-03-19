<?php

namespace App\Http\Controllers;

use App\Http\Requests\SongsAroundRequest;
use Illuminate\Http\Request;
use App\Models\SongCaptureLog;
use Exception;
use Illuminate\Support\Facades\DB;

class SongsAroundController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['index','store']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            // パラメータを元に周囲で聴かれている曲を取得
            $items = SongCaptureLog::excludeUser($request->input('userId'))
                        ->withinDistance(
                            $request->input('latitude'),
                            $request->input('longitude'),
                            $request->input('distance')
                        )
                        ->sumPopularityBySongs()
                        ->orderBy('popularity', 'desc')
                        ->take($this->DATA_LIMIT)
                        ->get();

            // DBから取得したデータを元にレスポンスを作成
            $responseItems = [];
            $rankIndex = 1;
            foreach ($items as $item) {
                $responseItem = [];
                $responseItem['rank'] = $rankIndex;
                $responseItem['spotify_song_id'] = $item['spotify_song_id'];
                $responseItem['popularity'] = (int)$item['popularity'];
                $responseItems[] = $responseItem;
                $rankIndex++;
            }

            return $this->responseToClient('OK', $responseItems, $this->HTTP_OK);
        } catch (Exception $e) {
            return $this->responseToClient('ERROR', $e, $this->HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SongsAroundRequest $request)
    {
        try {
            $items = $request->all();
            return DB::transaction(function () use ($items) {
                foreach ($items as $item) {
                    SongCaptureLog::create($item);
                }
                return $this->responseToClient('OK', null, $this->HTTP_OK);
            });
        } catch (Exception $e) {
            return $this->responseToClient('ERROR', $e, $this->HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        //
    }
}
