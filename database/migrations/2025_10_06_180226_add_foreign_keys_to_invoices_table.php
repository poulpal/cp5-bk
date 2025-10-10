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
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreign(['from_account_id'])->references(['id'])->on('accounting_details')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign(['building_id'])->references(['id'])->on('buildings');
            $table->foreign(['user_id'])->references(['id'])->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign('invoices_from_account_id_foreign');
            $table->dropForeign('invoices_building_id_foreign');
            $table->dropForeign('invoices_user_id_foreign');
        });
    }
};
