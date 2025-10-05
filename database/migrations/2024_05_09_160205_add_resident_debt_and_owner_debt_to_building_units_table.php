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
            $table->decimal('resident_debt', 30, 1)->default(0)->after('charge_debt');
            $table->decimal('owner_debt', 30, 1)->default(0)->after('resident_debt');
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
            $table->dropColumn('resident_debt');
            $table->dropColumn('owner_debt');
        });
    }
};
