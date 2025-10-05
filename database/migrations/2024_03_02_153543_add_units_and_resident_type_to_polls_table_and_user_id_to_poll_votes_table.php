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
        Schema::table('polls', function (Blueprint $table) {
            $table->json('units')->after('options');
            $table->string('resident_type')->default('all')->after('options');
        });
        Schema::table('poll_votes', function (Blueprint $table) {
            $table->foreignId('user_id')->after('building_unit_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('polls', function (Blueprint $table) {
            $table->dropColumn('units');
            $table->dropColumn('resident_type');
        });
        Schema::table('poll_votes', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }
};
