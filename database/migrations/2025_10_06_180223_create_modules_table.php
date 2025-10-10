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
        Schema::create('modules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->string('slug')->index();
            $table->integer('order')->default(0);
            $table->string('type')->nullable();
            $table->decimal('price', 30, 1)->default(0);
            $table->boolean('is_on_offer')->default(false);
            $table->string('offer_description')->nullable();
            $table->string('offer_before_price')->nullable();
            $table->text('description')->nullable();
            $table->longText('features')->nullable();
            $table->timestamps();

            $table->unique(['slug']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('modules');
    }
};
