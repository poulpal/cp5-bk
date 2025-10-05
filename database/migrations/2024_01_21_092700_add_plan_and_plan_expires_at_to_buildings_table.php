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
        Schema::table('buildings', function (Blueprint $table) {
            $table->string('plan_slug')->default('free')->after('unit_count');
            $table->timestamp('plan_expires_at')->default(now()->addDays(14))->after('unit_count');
            $table->bigInteger('plan_duration')->default(14)->after('unit_count');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('buildings', function (Blueprint $table) {
            $table->dropColumn('plan_slug');
            $table->dropColumn('plan_expires_at');
            $table->dropColumn('plan_duration');
        });
    }
};
