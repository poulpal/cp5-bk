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
        Schema::create('deposit_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('building_id')->index('deposit_requests_building_id_foreign');
            $table->decimal('amount', 30, 1);
            $table->string('status')->default('pending');
            $table->string('sheba')->nullable();
            $table->enum('deposit_to', ['me', 'other'])->default('me');
            $table->longText('description')->nullable();
            $table->longText('data')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('balance_id')->nullable()->index('deposit_requests_balance_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('deposit_requests');
    }
};
