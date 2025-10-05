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
        Schema::create('buildings_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('building_id');
            $table->string('module_slug');
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->decimal('price', 30, 1)->default(0);
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
        Schema::dropIfExists('buildings_modules');
    }
};
