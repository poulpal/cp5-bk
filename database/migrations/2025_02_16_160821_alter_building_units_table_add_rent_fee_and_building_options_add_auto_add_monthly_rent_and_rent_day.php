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
            $table->decimal('rent_fee', 30, 1)->default(0)->after('charge_fee');
        });
        Schema::table('building_options', function (Blueprint $table) {
            $table->boolean('has_rent')->default(false)->after('multi_balance');
            $table->boolean('auto_add_monthly_rent')->default(false)->after('auto_add_monthly_charge');
            $table->unsignedTinyInteger('rent_day')->default(1)->after('charge_day');
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
            $table->dropColumn('rent_fee');
        });
        Schema::table('building_options', function (Blueprint $table) {
            $table->dropColumn('has_rent');
            $table->dropColumn('auto_add_monthly_rent');
            $table->dropColumn('rent_day');
        });
    }
};
