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
       Schema::create('admin_media_categories', function (Blueprint $table) {
           $table->id();
            $table->unsignedBigInteger('media_type_id');
            $table->foreign('media_type_id')->references('id')->on('media_types')->onDelete('cascade');
            $table->string('category');
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
        Schema::dropIfExists('admin_media_categories');
    }
};
