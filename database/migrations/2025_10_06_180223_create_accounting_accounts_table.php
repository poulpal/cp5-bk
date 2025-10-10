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
        Schema::create('accounting_accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('building_id')->index();
            $table->string('name');
            $table->string('code');
            $table->string('type');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
            $table->boolean('is_locked')->default(false);
            $table->string('accountable_type')->nullable();
            $table->unsignedBigInteger('accountable_id')->nullable();

            $table->index(['accountable_type', 'accountable_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accounting_accounts');
    }
};
