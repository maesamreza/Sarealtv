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
        Schema::table('notifications', function (Blueprint $table) {
            
        $table->unsignedBigInteger('sender_id');
        $table->unsignedBigInteger('media_id')->nullable();
        $table->string('media_category')->nullable();
        $table->unsignedBigInteger('admin_media_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
            
            $table->dropColumn('sender_id');
            $table->dropColumn('media_id');
            $table->dropColumn('admin_media_id');
            $table->dropColumn('media_category');
        });
    }
};
