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
        Schema::table('client_media', function (Blueprint $table) {
        

            $table->string('subDes')->nullable();
            $table->string('thumbs')->nullable(); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_media', function (Blueprint $table) {
            $table->dropColumn('subDes');
            $table->dropColumn('thumbs'); 
        });
    }
};
