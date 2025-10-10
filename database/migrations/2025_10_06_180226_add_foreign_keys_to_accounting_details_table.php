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
        Schema::table('accounting_details', function (Blueprint $table) {
            $table->foreign(['parent_id'])->references(['id'])->on('accounting_details');
            $table->foreign(['building_id'])->references(['id'])->on('buildings');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accounting_details', function (Blueprint $table) {
            $table->dropForeign('accounting_details_parent_id_foreign');
            $table->dropForeign('accounting_details_building_id_foreign');
        });
    }
};
