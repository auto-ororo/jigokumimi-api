<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArtistCaptureLog extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'spotify_artist_id', 'spotify_user_id', 'longitude','latitude', 'popularity'
    ];

    protected $table = 'artist_capture_logs';

    /**
     * アーティストごとの人気度を集計する
     *
     * @param [type] $query
     * @return void
     */
    public function scopeSumPopularityByArtists($query)
    {
        // 曲ごとの人気度を集計するSELECT文
        $selectStr =<<<___SELECT_SQL___
        spotify_artist_id,
        sum(popularity) as popularity
        ___SELECT_SQL___;

        // 曲ごとに集計
        return $query->selectRaw($selectStr)
                     ->groupBy('spotify_artist_id');
    }
}
