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
        Schema::create('client_media', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            //$table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->string('title');
            $table->string('des')->nullable();
            $table->string('url');
            $table->integer('duration')->nullable();
            $table->enum('type',['image','video'])->default('image');
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
        Schema::dropIfExists('client_media');
    }
};
