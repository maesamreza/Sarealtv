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
        Schema::create('series_media', function (Blueprint $table) {
            //$table->id();
            $table->unsignedBigInteger('series_id');
            $table->foreign('series_id')->references('id')->on('series');
            $table->unsignedBigInteger('media_type_id');
            $table->unsignedBigInteger('admin_media_category_id');
            $table->unsignedBigInteger('admin_media_id');
            $table->foreign('admin_media_id')->references('id')->on('admin_media')->onDelete('cascade');
            $table->integer('season');
            $table->integer('episode');
            $table->unique(['series_id','season','episode']);
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
        Schema::dropIfExists('series_media');
    }
};
