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
        Schema::table('accounting_transactions', function (Blueprint $table) {
            $table->foreign(['accounting_detail_id'])->references(['id'])->on('accounting_details');
            $table->foreign(['accounting_account_id'])->references(['id'])->on('accounting_accounts');
            $table->foreign(['accounting_document_id'])->references(['id'])->on('accounting_documents');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accounting_transactions', function (Blueprint $table) {
            $table->dropForeign('accounting_transactions_accounting_detail_id_foreign');
            $table->dropForeign('accounting_transactions_accounting_account_id_foreign');
            $table->dropForeign('accounting_transactions_accounting_document_id_foreign');
        });
    }
};
