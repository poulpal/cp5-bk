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
            $table->boolean('separate_resident_and_owner_invoices')->default(0);
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
            $table->dropColumn('separate_resident_and_owner_invoices');
        });
    }
};
