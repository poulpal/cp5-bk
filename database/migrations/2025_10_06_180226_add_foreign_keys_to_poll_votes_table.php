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
        Schema::table('poll_votes', function (Blueprint $table) {
            $table->foreign(['poll_id'])->references(['id'])->on('polls');
            $table->foreign(['building_unit_id'])->references(['id'])->on('building_units');
            $table->foreign(['user_id'])->references(['id'])->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('poll_votes', function (Blueprint $table) {
            $table->dropForeign('poll_votes_poll_id_foreign');
            $table->dropForeign('poll_votes_building_unit_id_foreign');
            $table->dropForeign('poll_votes_user_id_foreign');
        });
    }
};
