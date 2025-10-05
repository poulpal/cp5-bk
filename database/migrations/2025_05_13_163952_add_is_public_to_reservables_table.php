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
        Schema::table('reservables', function (Blueprint $table) {
            $table->boolean('is_public')->default(true)->after('is_active');
            $table->enum('resident_type', ['owner', 'resident', 'all'])->default('all')->after('is_public');
            $table->integer('monthly_hour_limit')->nullable()->after('resident_type');
            $table->integer('cancel_hour_limit')->nullable()->after('monthly_hour_limit');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reservables', function (Blueprint $table) {
            $table->dropColumn(['is_public', 'resident_type', 'monthly_hour_limit', 'cancel_hour_limit']);
        });
    }
};
