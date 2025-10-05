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
        Schema::table('modules', function (Blueprint $table) {
            $table->boolean('is_on_offer')->default(false)->after('price');
            $table->string('offer_description')->nullable()->after('is_on_offer');
            $table->string('offer_before_price')->nullable()->after('offer_description');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn('is_on_offer');
            $table->dropColumn('offer_description');
            $table->dropColumn('offer_before_price');
        });
    }
};
