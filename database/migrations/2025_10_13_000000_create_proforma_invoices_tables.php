<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('proforma_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // وابستگی اختیاری به ساختمان؛ اگر در پروژه‌ات جدول buildings از نوع bigIncrements است این را نگه دار
            $table->unsignedBigInteger('building_id')->nullable()->index();

            // نکته مهم: به جای FK سخت به packages، فقط ایندکس می‌گذاریم تا از mismatch نوع/ترتیب ساخت جلوگیری شود
            $table->unsignedBigInteger('package_id')->nullable()->index();

            $table->string('proforma_number')->unique(); // PF-YYYYMM-xxxxx
            $table->enum('period', ['monthly','yearly'])->default('monthly');
            $table->bigInteger('subtotal')->default(0);
            $table->bigInteger('discount')->default(0);
            $table->bigInteger('tax')->default(0);
            $table->bigInteger('total')->default(0);
            $table->string('currency', 8)->default('IRR');

            $table->string('status')->default('draft'); // draft|issued|canceled|expired
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->json('buyer_meta')->nullable();
            $table->json('seller_meta')->nullable();
            $table->json('meta')->nullable();

            $table->timestamps();
            $table->softDeletes();
            $table->index(['user_id','status']);
        });

        Schema::create('proforma_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proforma_invoice_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('qty')->default(1);
            $table->bigInteger('unit_price')->default(0);
            $table->bigInteger('line_total')->default(0); // qty * unit_price
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['proforma_invoice_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proforma_items');
        Schema::dropIfExists('proforma_invoices');
    }
};
