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
        Schema::table('building_options', function (Blueprint $table) {
            $table->boolean('show_stocks_to_units')->default(false);
            $table->boolean('show_costs_to_units')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('building_options', function (Blueprint $table) {
            $table->dropColumn('show_stocks_to_units');
            $table->dropColumn('show_costs_to_units');
        });
    }
};
