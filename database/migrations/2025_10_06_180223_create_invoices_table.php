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
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable()->index('invoices_user_id_foreign');
            $table->unsignedBigInteger('building_id')->nullable()->index('invoices_building_id_foreign');
            $table->unsignedBigInteger('debt_type_id')->nullable();
            $table->decimal('amount', 30, 1)->nullable();
            $table->unsignedBigInteger('discount_code_id')->nullable();
            $table->string('status')->default('pending');
            $table->string('payment_method')->nullable();
            $table->string('payment_id')->nullable();
            $table->string('payment_card_number')->nullable();
            $table->string('payment_tracenumber')->nullable();
            $table->longText('description')->nullable();
            $table->enum('resident_type', ['owner', 'resident'])->default('resident');
            $table->unsignedBigInteger('serviceable_id')->nullable();
            $table->string('serviceable_type');
            $table->boolean('is_verified')->default(true);
            $table->boolean('fine_exception')->default(false);
            $table->boolean('show_units')->default(true);
            $table->timestamps();
            $table->longText('payment_response')->nullable();
            $table->longText('data')->nullable();
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->unsignedBigInteger('from_account_id')->nullable()->index('invoices_from_account_id_foreign');
            $table->softDeletes();
            $table->dateTime('early_discount_until')->nullable();
            $table->decimal('early_discount_amount', 30, 1)->nullable();
            $table->boolean('is_paid')->default(false);
            $table->decimal('paid_amount', 30, 1)->default(0);
            $table->longText('paid_data')->nullable();
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
