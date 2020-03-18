<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SongCaptureLog extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'spotify_song_id', 'spotify_user_id', 'longitude','latitude', 'popularity'
    ];

    protected $table = 'song_capture_logs';

    public function scopeWithinDistance($query, $latitude, $longitude, $distance)
    {
        // 赤道半径
        $equatorRadius = 6378137.0;

        // 全カラム(*)､及び引数で渡された位置情報との距離(distance)を取得するSELECT文
        $selectStr =<<<___SELECT_SQL___
        *,
        (? * acos(
            cos(radians(?))
                *
            cos(radians(latitude))
                *
            cos(radians(longitude) - radians(?))
                +
            sin(radians(?))
                *
            sin(radians(latitude)))
        ) AS distance
        ___SELECT_SQL___;

        return $query->selectRaw($selectStr, [$equatorRadius, $latitude, $longitude, $latitude])
                     ->having('distance', '<=', $distance);
    }
}
