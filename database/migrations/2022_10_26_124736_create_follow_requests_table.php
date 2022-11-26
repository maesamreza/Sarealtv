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
        Schema::create('follow_requests', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('client_id');
      //$table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
      $table->unsignedBigInteger('follower_id');
      $table->foreign('follower_id')->references('id')->on('clients')->onDelete('cascade');
      $table->unique(['client_id','follower_id']);
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
        Schema::dropIfExists('follow_requests');
    }
};
