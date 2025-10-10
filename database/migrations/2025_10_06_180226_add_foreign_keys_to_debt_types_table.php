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
        Schema::table('debt_types', function (Blueprint $table) {
            $table->foreign(['income_accounting_account_id'])->references(['id'])->on('accounting_accounts');
            $table->foreign(['building_id'])->references(['id'])->on('buildings');
            $table->foreign(['receivable_accounting_account_id'])->references(['id'])->on('accounting_accounts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('debt_types', function (Blueprint $table) {
            $table->dropForeign('debt_types_income_accounting_account_id_foreign');
            $table->dropForeign('debt_types_building_id_foreign');
            $table->dropForeign('debt_types_receivable_accounting_account_id_foreign');
        });
    }
};
