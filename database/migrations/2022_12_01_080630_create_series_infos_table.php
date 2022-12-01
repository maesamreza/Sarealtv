<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('series_infos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_media_id');
            $table->foreign('admin_media_id')->references('id')->on('admin_media')->onDelete('cascade');
            $table->integer('season')->nullable();
            $table->integer('episode')->nullable();
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('series_infos');
    }
};
