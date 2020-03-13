<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSongsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('song_capture_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('spotify_song_id');
            $table->string('spotify_user_id');
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
        Schema::dropIfExists('songs');
    }
}
