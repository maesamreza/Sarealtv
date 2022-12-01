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
        Schema::create('admin_media', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            //$table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->string('title');
            $table->string('des')->nullable();
            $table->string('url');
            $table->integer('duration')->nullable();
            $table->enum('type',['images','videos','movies','trailers','series']);
            $table->string('subDes')->nullable();
            $table->string('thumbs')->nullable(); 
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
        //
    }
};
