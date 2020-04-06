<?php

namespace App\Http\Controllers;

use App\Http\Requests\TracksAroundRequest;
use Illuminate\Http\Request;
use App\Models\TrackAroundHistory;
use App\Models\History;
use App\Models\TrackAround;
use Exception;
use Illuminate\Support\Facades\DB;

class TracksAroundController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $userId = $request->input('userId');
            $latitude = $request->input('latitude');
            $longitude = $request->input('longitude');
            $distance = $request->input('distance');

            // パラメータを元に周囲で聴かれている曲を取得
            $items = TrackAround::excludeUser($userId)
                        ->withinDistance(
                            $latitude,
                            $longitude,
                            $distance
                        )
                        ->sumPopularityByTracks()
                        ->orderBy('popularity', 'desc')
                        ->take($this->DATA_LIMIT)
                        ->get();

            // パラメータを元にHistoryオブジェクトを作成
            $history = [
                'user_id' => $userId,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'distance' => $distance
            ];

            return DB::transaction(function () use ($items, $history) {
                // History登録
                $historyId = History::create($history)->id;

                // DBから取得したデータを元にレスポンスを作成
                $responseItems = [];
                $rankIndex = 1;
                foreach ($items as $item) {
                    $responseItem = [];
                    $responseItem['rank'] = $rankIndex;
                    $responseItem['history_id'] = $historyId;
                    $responseItem['spotify_track_id'] = $item['spotify_track_id'];
                    $responseItem['popularity'] = (int)$item['popularity'];
                    $responseItems[] = $responseItem;
                    $rankIndex++;

                    $historyItem = [];
                    $historyItem['history_id'] = $historyId;
                    $historyItem['rank'] = $rankIndex;
                    $historyItem['spotify_track_id'] = $item['spotify_track_id'];
                    $historyItem['popularity'] = (int)$item['popularity'];
                    // TrackAeoundHistory登録
                    TrackAroundHistory::create($historyItem);
                }

                return $this->responseToClient('OK', $responseItems, $this->HTTP_OK);
            });
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
    public function store(TracksAroundRequest $request)
    {
        try {
            $items = $request->all();
            return DB::transaction(function () use ($items) {
                foreach ($items as $item) {
                    TrackAround::create($item);
                }
                return $this->responseToClient('OK', null, $this->HTTP_OK);
            });
        } catch (Exception $e) {
            return $this->responseToClient('ERROR', $e, $this->HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * 周辺曲情報の検索履歴を取得する
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function history(Request $request)
    {
        try {
            $userId = $request->input('userId');

            $histories = History::where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->get();

            foreach ($histories as $history) {
                $history->tracksAroundHistories;
            }

            return $this->responseToClient('OK', $histories, $this->HTTP_OK);
        } catch (Exception $e) {
            return $this->responseToClient('ERROR', $e, $this->HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * 周辺曲情報の検索履歴を削除する
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function deleteHistory(Request $request)
    {
        try {
            $historyId = $request->input('historyId');

            History::find($historyId)->delete();

            return $this->responseToClient('OK', null, $this->HTTP_OK);
        } catch (Exception $e) {
            return $this->responseToClient('ERROR', $e, $this->HTTP_INTERNAL_ERROR);
        }
    }
}
