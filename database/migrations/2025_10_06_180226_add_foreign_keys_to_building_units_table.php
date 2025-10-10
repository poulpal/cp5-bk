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
        Schema::table('building_units', function (Blueprint $table) {
            $table->foreign(['building_id'])->references(['id'])->on('buildings')->onDelete('CASCADE');
            $table->foreign(['balance_id'])->references(['id'])->on('balances')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('building_units', function (Blueprint $table) {
            $table->dropForeign('building_units_building_id_foreign');
            $table->dropForeign('building_units_balance_id_foreign');
        });
    }
};
