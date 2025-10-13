<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // مرحله اول: افزودن ستون 'is_core' به جدول 'modules' در صورتی که وجود نداشته باشد
        Schema::table('modules', function (Blueprint $table) {
            if (!Schema::hasColumn('modules', 'is_core')) {
                $table->boolean('is_core')->default(false)->after('price');
            }
        });

        // مرحله دوم: تعریف لیست ماژول‌های اصلی (هسته)
        $coreModules = ['accounting', 'stock', 'reserve', 'poll'];

        // مرحله سوم: استفاده از دستورات SQL خام برای اطمینان از اجرای صحیح
        // این روش مشکلات مربوط به Parameter Binding در محیط‌های خاص را دور می‌زند.
        foreach ($coreModules as $moduleName) {
            // به کوتیشن‌های تکی دور $moduleName دقت کنید.
            DB::statement("UPDATE `modules` SET `is_core` = 1 WHERE `name` = '{$moduleName}'");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // برگرداندن تغییرات اعمال شده در متد 'up'
        Schema::table('modules', function (Blueprint $table) {
            if (Schema::hasColumn('modules', 'is_core')) {
                $table->dropColumn('is_core');
            }
        });
    }
};