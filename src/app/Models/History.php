<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Webpatser\Uuid\Uuid;

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

    /**
     * ID連番を無効
     *
     * @var boolean
     */
    public $incrementing = false;

    /**
     * 主キーを文字列に変更
     *
     * @var boolean
     */
    protected $keyType = 'string';

    /**
     * モデル作成時にUUIDを生成
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Uuid::generate()->string;
        });
    }

    public function artistsAroundHistories()
    {
        return $this->hasMany('App\Models\ArtistAroundHistory')->orderBy('rank');
    }

    public function tracksAroundHistories()
    {
        return $this->hasMany('App\Models\TrackAroundHistory')->orderBy('rank');
    }
}
