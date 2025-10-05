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
        Schema::create('building_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id');
            $table->boolean('custom_payment')->default(true);
            $table->boolean('late_fine')->default(false);
            $table->double('late_fine_percent')->default(0.0);
            $table->integer('late_fine_days')->default(0);
            $table->boolean('early_payment')->default(false);
            $table->double('early_payment_percent')->default(0.0);
            $table->integer('early_payment_days')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('building_options');
    }
};
