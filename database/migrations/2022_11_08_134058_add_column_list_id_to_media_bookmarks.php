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
        Schema::table('media_bookmarks', function (Blueprint $table) {
            $table->unsignedBigInteger('bookmark_list_id');
            $table->foreign('bookmark_list_id')->references('id')->on('bookmark_lists')->onDelete('cascade');
        
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('media_bookmarks', function (Blueprint $table) {
            $table->dropForeign(['bookmark_list_id']);
            $table->dropColumn('bookmark_list_id');
        });
    }
};
