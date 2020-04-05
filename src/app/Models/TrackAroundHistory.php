<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrackAroundHistory extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'history_id', 'spotify_track_id', 'rank','popularity'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'id', 'history_id','created_at','updated_at'
    ];

    protected $table = 'tracks_around_histories';

    public function history()
    {
        return $this->belongsTo('App\Models\History');
    }
}
