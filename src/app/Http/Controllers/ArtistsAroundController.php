<?php

namespace App\Http\Controllers;

use App\Http\Requests\ArtistsAroundRequest;
use Illuminate\Http\Request;
use App\Models\ArtistCaptureLog;
use Exception;
use Illuminate\Support\Facades\DB;

class ArtistsAroundController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            // パラメータを元に周囲で聴かれている曲を取得
            $items = ArtistCaptureLog::excludeUser($request->input('userId'))
                        ->withinDistance(
                            $request->input('latitude'),
                            $request->input('longitude'),
                            $request->input('distance')
                        )
                        ->sumPopularityByArtists()
                        ->orderBy('popularity', 'desc')
                        ->take($this->DATA_LIMIT)
                        ->get();

            // DBから取得したデータを元にレスポンスを作成
            $responseItems = [];
            $rankIndex = 1;
            foreach ($items as $item) {
                $responseItem = [];
                $responseItem['rank'] = $rankIndex;
                $responseItem['spotify_artist_id'] = $item['spotify_artist_id'];
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ArtistsAroundRequest $request)
    {
        try {
            $items = $request->all();
            return DB::transaction(function () use ($items) {
                foreach ($items as $item) {
                    ArtistCaptureLog::create($item);
                }
                return $this->responseToClient('OK', null, $this->HTTP_OK);
            });
        } catch (Exception $e) {
            return $this->responseToClient('ERROR', $e, $this->HTTP_INTERNAL_ERROR);
        }
    }
}