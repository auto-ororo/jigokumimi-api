<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class History extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'user_id',
        'longitude',
        'latitude',
        'distance',
        'created_at',
        'artists_around_histories',
        'tracks_around_histories'
    ];

    protected $table = 'histories';

    public function artistsAroundHistories()
    {
        return $this->hasMany('App\Models\ArtistAroundHistory')->orderBy('rank');
    }

    public function tracksAroundHistories()
    {
        return $this->hasMany('App\Models\TrackAroundHistory')->orderBy('rank');
    }
}
