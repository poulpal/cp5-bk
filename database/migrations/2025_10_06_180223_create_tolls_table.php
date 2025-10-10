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
        Schema::create('tolls', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable()->index('tolls_user_id_foreign');
            $table->unsignedBigInteger('building_id')->index('tolls_building_id_foreign');
            $table->decimal('amount', 30, 1)->nullable();
            $table->string('status')->default('pending');
            $table->string('payment_method')->default('gateway');
            $table->text('description')->nullable();
            $table->enum('resident_type', ['owner', 'resident'])->default('resident');
            $table->unsignedBigInteger('serviceable_id')->nullable();
            $table->string('serviceable_type');
            $table->timestamps();
            $table->softDeletes();
            $table->string('token')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tolls');
    }
};
