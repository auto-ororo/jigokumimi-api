<?php

namespace App\Http\Controllers;

use App\Http\Requests\ArtistsAroundRequest;
use Illuminate\Http\Request;
use App\Models\ArtistAround;
use App\Models\ArtistAroundHistory;
use App\Models\History;
use Exception;
use Illuminate\Support\Facades\DB;

class ArtistsAroundController extends Controller
{
    /**
     * 周辺アーティスト情報を取得する
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
            $items = ArtistAround::excludeUser($userId)
                        ->withinDistance(
                            $latitude,
                            $longitude,
                            $distance
                        )
                        ->sumPopularityByArtists()
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
                    $responseItem['spotify_artist_id'] = $item['spotify_artist_id'];
                    $responseItem['popularity'] = (int)$item['popularity'];
                    $responseItems[] = $responseItem;

                    $historyItem = [];
                    $historyItem['history_id'] = $historyId;
                    $historyItem['rank'] = $rankIndex;
                    $historyItem['spotify_artist_id'] = $item['spotify_artist_id'];
                    $historyItem['popularity'] = (int)$item['popularity'];
                    // ArtistAeoundHistory登録
                    ArtistAroundHistory::create($historyItem);

                    $rankIndex++;
                }


                return $this->responseToClient('OK', $responseItems, $this->HTTP_OK);
            });
        } catch (Exception $e) {
            return $this->responseToClient('ERROR', $e, $this->HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * 周辺アーティスト情報を登録する
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
                    ArtistAround::create($item);
                }
                return $this->responseToClient('OK', null, $this->HTTP_OK);
            });
        } catch (Exception $e) {
            return $this->responseToClient('ERROR', $e, $this->HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * 周辺アーティスト情報の検索履歴を取得する
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function history(Request $request)
    {
        try {
            $userId = $request->input('userId');

            // ArtistAroundHistoryを子データとして持つHistoryデータのみを取得
            $histories = History::where('user_id', $userId)
                ->with('artistsAroundHistories')
                ->whereHas('artistsAroundHistories', function ($query) {
                    $query->whereExists(function ($query) {
                        return $query;
                    });
                })
                ->orderBy('created_at', 'desc')->get();

            foreach ($histories as $history) {
                $history->artistsAroundHistories;
            }

            return $this->responseToClient('OK', $histories, $this->HTTP_OK);
        } catch (Exception $e) {
            return $this->responseToClient('ERROR', $e, $this->HTTP_INTERNAL_ERROR);
        }
    }

    /**
     * 周辺アーティスト情報の検索履歴を削除する
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
