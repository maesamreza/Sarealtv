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
        Schema::create('media_bookmarks', function (Blueprint $table) {
            $table->unsignedBigInteger('client_id');
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->unsignedBigInteger('owner_id');
            $table->foreign('owner_id')->references('id')->on('clients')->onDelete('cascade');
            $table->unsignedBigInteger('client_media_id');
            $table->foreign('client_media_id')->references('id')->on('client_media')->onDelete('cascade');
            $table->unsignedBigInteger('bookmark_list_id');
            $table->foreign('bookmark_list_id')->references('id')->on('bookmark_lists')->onDelete('cascade');
            $table->unique(['client_media_id','bookmark_list_id']);
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
        Schema::dropIfExists('media_bookmarks');
    }
};
