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
        Schema::table('accounting_accounts', function (Blueprint $table) {
            $table->text('description')->change();
        });
        Schema::table('accounting_documents', function (Blueprint $table) {
            $table->text('description')->change();
        });
        Schema::table('accounting_transactions', function (Blueprint $table) {
            $table->text('description')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('accounting_accounts', function (Blueprint $table) {
            $table->string('description')->change();
        });
        Schema::table('accounting_accounts', function (Blueprint $table) {
            $table->string('description')->change();
        });
        Schema::table('accounting_transactions', function (Blueprint $table) {
            $table->string('description')->change();
        });
    }
};
