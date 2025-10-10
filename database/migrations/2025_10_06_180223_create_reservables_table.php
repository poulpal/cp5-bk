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
        Schema::create('reservables', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('building_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->bigInteger('cost_per_hour');
            $table->longText('available_hours');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_public')->default(true);
            $table->enum('resident_type', ['owner', 'resident', 'all'])->default('all');
            $table->integer('monthly_hour_limit')->nullable();
            $table->integer('cancel_hour_limit')->nullable();
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
        Schema::dropIfExists('reservables');
    }
};
