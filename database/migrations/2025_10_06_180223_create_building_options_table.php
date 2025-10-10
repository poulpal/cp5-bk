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
            $table->bigIncrements('id');
            $table->unsignedBigInteger('building_id')->index('building_id');
            $table->integer('charge_day')->default(1);
            $table->unsignedTinyInteger('rent_day')->default(1);
            $table->boolean('custom_payment')->default(true);
            $table->boolean('late_fine')->default(false);
            $table->double('late_fine_percent', 8, 2)->default(0);
            $table->integer('late_fine_days')->default(1);
            $table->boolean('early_payment')->default(false);
            $table->double('early_payment_percent', 8, 2)->default(0);
            $table->integer('early_payment_days')->default(10);
            $table->timestamps();
            $table->boolean('manual_payment')->default(true);
            $table->boolean('auto_add_monthly_charge')->default(true);
            $table->boolean('auto_add_monthly_rent')->default(false);
            $table->boolean('separate_owner_payment_balance')->default(false);
            $table->boolean('polls')->default(true);
            $table->boolean('send_building_manager_payment_notification')->default(true);
            $table->enum('currency', ['rial', 'toman'])->default('rial');
            $table->longText('accounting_options')->nullable();
            $table->boolean('separate_resident_and_owner_invoices')->default(false);
            $table->boolean('multi_balance')->default(false);
            $table->boolean('has_rent')->default(false);
            $table->boolean('show_stocks_to_units')->default(true);
            $table->boolean('show_costs_to_units')->default(true);
            $table->boolean('show_balances_to_units')->default(true);
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
