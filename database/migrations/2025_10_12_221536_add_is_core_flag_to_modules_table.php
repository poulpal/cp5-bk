<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // افزودن ستون is_core در صورتی که وجود نداشته باشد
        Schema::table('modules', function (Blueprint $table) {
            if (!Schema::hasColumn('modules', 'is_core')) {
                $table->boolean('is_core')->default(false)->after('price');
            }
        });

        // لیست slug های ماژول‌های اصلی
        // این لیست باید با مقادیر slug در seeder شما مطابقت داشته باشد
        $coreModuleSlugs = [
            'accounting-basic',
            'accounting-general',
            'stocks',
            'reserve-and-poll',
            'fine-and-reward'
        ];

        // آپدیت کردن ماژول‌های اصلی با استفاده از ستون slug
        foreach ($coreModuleSlugs as $slug) {
            DB::table('modules')->where('slug', $slug)->update(['is_core' => true]);
        }
    }

    public function down()
    {
        Schema::table('modules', function (Blueprint $table) {
            if (Schema::hasColumn('modules', 'is_core')) {
                $table->dropColumn('is_core');
            }
        });
    }
};