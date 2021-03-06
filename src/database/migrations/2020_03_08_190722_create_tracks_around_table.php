<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTracksAroundTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tracks_around', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('spotify_track_id');
            $table->uuid('user_id');
            $table->double('longitude');
            $table->double('latitude');
            $table->integer('popularity');
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tracks_around');
    }
}
