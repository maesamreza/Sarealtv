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
    

        Schema::create('media_filter', function (Blueprint $table) {
           
             $table->unsignedBigInteger('media_type_id');
             $table->unsignedBigInteger('admin_media_category_id');
        
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
    
    }
};
