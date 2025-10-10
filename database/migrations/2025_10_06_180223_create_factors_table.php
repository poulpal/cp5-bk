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
        Schema::create('factors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('factor_number')->nullable();
            $table->unsignedBigInteger('building_id');
            $table->string('customer_name')->nullable();
            $table->longText('items');
            $table->string('address')->nullable();
            $table->string('economic_code')->nullable();
            $table->string('customer_code')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('city')->nullable();
            $table->string('description')->nullable();
            $table->double('amount')->default(0);
            $table->string('status')->default('pending');
            $table->string('token')->nullable();
            $table->dateTime('due_date')->nullable();
            $table->boolean('has_vat')->default(true);
            $table->double('vat_percent')->default(10);
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
        Schema::dropIfExists('factors');
    }
};
