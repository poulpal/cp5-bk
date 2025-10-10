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
        Schema::create('building_units', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('building_id')->index('building_units_building_id_foreign');
            $table->string('unit_number');
            $table->decimal('area', 10)->default(0);
            $table->integer('resident_count')->default(0);
            $table->integer('parking_count')->default(0);
            $table->integer('storage_count')->default(0);
            $table->decimal('charge_fee', 30, 1)->default(0);
            $table->decimal('rent_fee', 30, 1)->default(0);
            $table->decimal('charge_debt', 30, 1)->default(0);
            $table->decimal('resident_debt', 30, 1)->default(0);
            $table->decimal('owner_debt', 30, 1)->default(0);
            $table->decimal('late_fine', 30, 1)->default(0);
            $table->string('token')->index();
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('balance_id')->nullable()->index('building_units_balance_id_foreign');

            $table->index(['building_id']);
            $table->unique(['token']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('building_units');
    }
};
