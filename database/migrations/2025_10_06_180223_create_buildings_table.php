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
        Schema::create('buildings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('name_en')->unique();
            $table->string('image')->nullable();
            $table->bigInteger('unit_count')->default(20);
            $table->bigInteger('plan_duration')->default(14);
            $table->timestamp('plan_expires_at')->default('2024-02-04 11:20:19');
            $table->string('plan_slug')->default('free');
            $table->decimal('balance', 30, 1)->default(0);
            $table->decimal('toll_balance', 30, 1)->default(0);
            $table->bigInteger('sms_balance')->default(0);
            $table->integer('commission')->default(3900);
            $table->boolean('is_verified')->default(false);
            $table->string('terminal_id')->nullable();
            $table->date('start_charge_date')->default('2023-05-25');
            $table->string('signed_contract')->default('0');
            $table->string('contract_key')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('poulpal_business_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('buildings');
    }
};
