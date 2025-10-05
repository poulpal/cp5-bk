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
        Schema::create('accounting_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id');
            $table->string('name');
            $table->string('code');
            $table->string('type');
            $table->boolean('is_locked')->default(false);
            $table->foreignId('parent_id')->nullable();
            $table->nullableMorphs('accountable');
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
        Schema::dropIfExists('accounting_details');
    }
};
