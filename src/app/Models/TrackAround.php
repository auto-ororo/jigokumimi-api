<?php

namespace App\Models;

class TrackAround extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'spotify_track_id', 'user_id', 'longitude','latitude', 'popularity'
    ];

    protected $table = 'tracks_around';

    /**
     * 曲ごとの人気度を集計する
     *
     * @param [type] $query
     * @return void
     */
    public function scopeSumPopularityByTracks($query)
    {
        // 曲ごとの人気度を集計するSELECT文
        $selectStr =<<<___SELECT_SQL___
        spotify_track_id,
        sum(popularity) as popularity
        ___SELECT_SQL___;

        // 曲ごとに集計
        return $query->selectRaw($selectStr)
                     ->groupBy('spotify_track_id');
    }
}
