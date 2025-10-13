<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('building_units', function (Blueprint $table) {
            // طبقه
            if (!Schema::hasColumn('building_units', 'floor')) {
                $table->integer('floor')->nullable()->after('area');
            }
            // بلوک (عدد اختیاری؛ اگر حروفی لازم شد بعداً string می‌شود)
            if (!Schema::hasColumn('building_units', 'block')) {
                $table->integer('block')->nullable()->after('floor');
            }
            // تعداد پارکینگ/انباری (اگر نبودند اضافه می‌کنیم)
            if (!Schema::hasColumn('building_units', 'parking_count')) {
                $table->integer('parking_count')->default(0)->after('resident_count');
            }
            if (!Schema::hasColumn('building_units', 'storage_count')) {
                $table->integer('storage_count')->default(0)->after('parking_count');
            }
            // تلفن ثابت
            if (!Schema::hasColumn('building_units', 'landline_phone')) {
                $table->string('landline_phone', 20)->nullable()->after('storage_count');
            }
            // لیست شماره‌های پارکینگ/انباری (CSV)
            if (!Schema::hasColumn('building_units', 'parking_numbers')) {
                $table->text('parking_numbers')->nullable()->after('landline_phone');
            }
            if (!Schema::hasColumn('building_units', 'storage_numbers')) {
                $table->text('storage_numbers')->nullable()->after('parking_numbers');
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
            if (Schema::hasColumn('building_units', 'storage_count')) {
                $table->dropColumn('storage_count');
            }
            if (Schema::hasColumn('building_units', 'parking_count')) {
                $table->dropColumn('parking_count');
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
