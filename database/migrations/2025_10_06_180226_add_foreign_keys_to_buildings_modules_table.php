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
        Schema::table('buildings_modules', function (Blueprint $table) {
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
        Schema::table('buildings_modules', function (Blueprint $table) {
            $table->dropForeign('buildings_modules_building_id_foreign');
        });
    }
};
