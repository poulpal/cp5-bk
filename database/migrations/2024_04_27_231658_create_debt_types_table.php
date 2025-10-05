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
        Schema::create('debt_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id');
            $table->string('name');
            $table->foreignId('receivable_accounting_account_id');
            $table->foreignId('income_accounting_account_id');
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
        Schema::dropIfExists('debt_types');
    }
};
