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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id()->startingValue(1000);
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->foreignId('building_id')->constrained('buildings');
            $table->decimal('amount', 30, 1)->nullable();
            $table->string('status')->default('pending');
            $table->string('payment_method')->nullable();
            $table->string('payment_id')->nullable();
            $table->string('payment_card_number')->nullable();
            $table->string('payment_tracenumber')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('serviceable_id')->nullable();
            $table->string('serviceable_type');
            $table->boolean('is_verified')->default(true);
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
        Schema::dropIfExists('invoices');
    }
};
