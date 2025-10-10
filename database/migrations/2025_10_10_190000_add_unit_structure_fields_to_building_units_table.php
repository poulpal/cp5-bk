<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // افزودن فیلدهای جدید به جدول building_units (همه غیراجباری)
        Schema::table('building_units', function (Blueprint $table) {
            // طبقه (فقط عددی – nullable)
            if (!Schema::hasColumn('building_units', 'floor')) {
                $table->integer('floor')->nullable()->after('area');
            }

            // بلوک (عدد – nullable)
            if (!Schema::hasColumn('building_units', 'block')) {
                $table->integer('block')->nullable()->after('floor');
            }

            // تلفن ثابت ایران (nullable)
            if (!Schema::hasColumn('building_units', 'landline_phone')) {
                $table->string('landline_phone', 20)->nullable()->after('resident_count');
            }

            // جای درج شماره‌های پارکینگ (CSV تمیز – nullable)
            if (!Schema::hasColumn('building_units', 'parking_numbers')) {
                $table->string('parking_numbers')->nullable()->after('parking_count');
            }

            // جای درج شماره‌های انباری (CSV تمیز – nullable)
            if (!Schema::hasColumn('building_units', 'storage_numbers')) {
                $table->string('storage_numbers')->nullable()->after('storage_count');
            }
        });
    }

    public function down(): void
    {
        Schema::table('building_units', function (Blueprint $table) {
            if (Schema::hasColumn('building_units', 'storage_numbers')) {
                $table->dropColumn('storage_numbers');
            }
            if (Schema::hasColumn('building_units', 'parking_numbers')) {
                $table->dropColumn('parking_numbers');
            }
            if (Schema::hasColumn('building_units', 'landline_phone')) {
                $table->dropColumn('landline_phone');
            }
            if (Schema::hasColumn('building_units', 'block')) {
                $table->dropColumn('block');
            }
            if (Schema::hasColumn('building_units', 'floor')) {
                $table->dropColumn('floor');
            }
        });
    }
};
