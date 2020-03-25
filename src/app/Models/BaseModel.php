<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{

    /**
     * 指定ユーザーを除外する
     *
     * @param [type] $query
     * @param [type] $spotify_user_id
     * @return void
     */
    public function scopeExcludeUser($query, $spotify_user_id)
    {
        return $query->where('spotify_user_id', '<>', $spotify_user_id);
    }

    /**
     * 指定位置情報を基準に、指定距離(m)内の周辺曲情報を取得する
     *
     * @param [type] $query
     * @param [type] $latitude
     * @param [type] $longitude
     * @param [type] $distance
     * @return void
     */
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