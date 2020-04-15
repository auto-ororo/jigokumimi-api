<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTracksAroundHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tracks_around_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('history_id');
            $table->integer('rank');
            $table->string('spotify_track_id');
            $table->integer('popularity');
            $table->timestamps();
            $table->foreign('history_id')->references('id')->on('histories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tracks_around_histories');
    }
}
