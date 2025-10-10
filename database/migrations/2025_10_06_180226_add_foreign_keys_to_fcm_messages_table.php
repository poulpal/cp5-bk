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
        Schema::table('fcm_messages', function (Blueprint $table) {
            $table->foreign(['building_id'])->references(['id'])->on('buildings')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fcm_messages', function (Blueprint $table) {
            $table->dropForeign('fcm_messages_building_id_foreign');
        });
    }
};
