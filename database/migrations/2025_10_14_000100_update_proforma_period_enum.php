<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // اگر ستون ENUM است: تبدیل به VARCHAR (ایمن‌ترین روش)، سپس داده را اعتبارسنجی می‌کنیم
        Schema::table('proforma_invoices', function (Blueprint $table) {
            $table->string('period', 16)->default('monthly')->change();
        });
    }

    public function down(): void
    {
        // بازگردانی (درصورت نیاز): دوباره به monthly/yearly برگردان
        // توجه: اگر دیتای quarterly وجود داشته باشد، down ممکن است خطا بدهد. احتیاط لازم است.
        Schema::table('proforma_invoices', function (Blueprint $table) {
            $table->enum('period', ['monthly','yearly'])->default('monthly')->change();
        });
    }
};
