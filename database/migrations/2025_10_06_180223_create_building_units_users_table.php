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
        Schema::create('building_units_users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('building_unit_id')->index('building_units_users_building_unit_id_foreign');
            $table->unsignedBigInteger('user_id')->index('building_units_users_user_id_foreign');
            $table->string('ownership')->default('owner');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('building_units_users');
    }
};
