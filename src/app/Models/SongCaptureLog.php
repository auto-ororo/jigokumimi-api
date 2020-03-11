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

    /**
     * The attributes that are define validation
     *
     * @var array
     */
    public $rules = [
        'spotify_song_id' => 'required',
        'spotify_user_id' => 'required',
        'longitude'    => 'required',
        'latitude' => 'required',
        'popularity' => 'required'
    ];
}
