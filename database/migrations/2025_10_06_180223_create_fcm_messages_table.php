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
        Schema::create('fcm_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('building_id')->index('fcm_messages_building_id_foreign');
            $table->text('pattern');
            $table->longText('units');
            $table->integer('length')->default(1);
            $table->bigInteger('count')->default(0);
            $table->string('resident_type')->default('all');
            $table->string('status')->default('pending');
            $table->dateTime('scheduled_at')->nullable();
            $table->string('batch_id')->nullable();
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
        Schema::dropIfExists('fcm_messages');
    }
};
