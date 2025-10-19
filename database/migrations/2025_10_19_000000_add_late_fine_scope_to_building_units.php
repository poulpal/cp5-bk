<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        // 1) proforma_invoices
        if (!Schema::hasTable('proforma_invoices')) {
            Schema::create('proforma_invoices', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('building_id')->nullable()->index();

                $table->string('package_slug', 191);
                $table->enum('period', ['monthly','quarterly','yearly'])->default('monthly');

                $table->unsignedBigInteger('subtotal')->default(0);
                $table->unsignedBigInteger('discount')->default(0);

                $table->integer('tax_percent')->default(10);
                $table->unsignedBigInteger('tax')->default(0);
                $table->unsignedBigInteger('total')->default(0);

                $table->string('currency', 10)->default('IRR');
                $table->enum('status', ['draft','pending','sent','paid','cancelled'])->default('draft');

                $table->text('notes')->nullable();

                $table->timestamps();
                $table->softDeletes();

                // اگر نیاز دارید FK بسازید، پس از اطمینان از وجود جدول buildings اضافه کنید:
                // $table->foreign('building_id')->references('id')->on('buildings')->nullOnDelete();
            });
        } else {
            // اگر جدول موجود است، ستون‌های جاافتاده را اضافه کن (بدون شکستن داده‌های موجود)
            Schema::table('proforma_invoices', function (Blueprint $table) {
                if (!Schema::hasColumn('proforma_invoices', 'package_slug')) {
                    $table->string('package_slug', 191)->after('building_id');
                }
                if (!Schema::hasColumn('proforma_invoices', 'period')) {
                    $table->enum('period', ['monthly','quarterly','yearly'])->default('monthly')->after('package_slug');
                }
                if (!Schema::hasColumn('proforma_invoices', 'subtotal')) {
                    $table->unsignedBigInteger('subtotal')->default(0)->after('period');
                }
                if (!Schema::hasColumn('proforma_invoices', 'discount')) {
                    $table->unsignedBigInteger('discount')->default(0)->after('subtotal');
                }
                if (!Schema::hasColumn('proforma_invoices', 'tax_percent')) {
                    $table->integer('tax_percent')->default(10)->after('discount');
                }
                if (!Schema::hasColumn('proforma_invoices', 'tax')) {
                    $table->unsignedBigInteger('tax')->default(0)->after('tax_percent');
                }
                if (!Schema::hasColumn('proforma_invoices', 'total')) {
                    $table->unsignedBigInteger('total')->default(0)->after('tax');
                }
                if (!Schema::hasColumn('proforma_invoices', 'currency')) {
                    $table->string('currency', 10)->default('IRR')->after('total');
                }
                if (!Schema::hasColumn('proforma_invoices', 'status')) {
                    $table->enum('status', ['draft','pending','sent','paid','cancelled'])->default('draft')->after('currency');
                }
                if (!Schema::hasColumn('proforma_invoices', 'notes')) {
                    $table->text('notes')->nullable()->after('status');
                }
                if (!Schema::hasColumn('proforma_invoices', 'created_at')) {
                    $table->timestamps();
                }
                if (!Schema::hasColumn('proforma_invoices', 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }

        // 2) proforma_invoice_items  (اگر در مایگریشن شما وجود دارد)
        if (!Schema::hasTable('proforma_invoice_items')) {
            Schema::create('proforma_invoice_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('proforma_invoice_id')->index();

                $table->string('title', 191);
                $table->unsignedInteger('qty')->default(1);
                $table->unsignedBigInteger('unit_price')->default(0);
                $table->unsignedBigInteger('total')->default(0);
                $table->unsignedSmallInteger('sort')->default(0);

                $table->timestamps();

                // FK اختیاری:
                // $table->foreign('proforma_invoice_id')->references('id')->on('proforma_invoices')->cascadeOnDelete();
            });
        } else {
            Schema::table('proforma_invoice_items', function (Blueprint $table) {
                if (!Schema::hasColumn('proforma_invoice_items', 'title')) {
                    $table->string('title', 191)->after('proforma_invoice_id');
                }
                if (!Schema::hasColumn('proforma_invoice_items', 'qty')) {
                    $table->unsignedInteger('qty')->default(1)->after('title');
                }
                if (!Schema::hasColumn('proforma_invoice_items', 'unit_price')) {
                    $table->unsignedBigInteger('unit_price')->default(0)->after('qty');
                }
                if (!Schema::hasColumn('proforma_invoice_items', 'total')) {
                    $table->unsignedBigInteger('total')->default(0)->after('unit_price');
                }
                if (!Schema::hasColumn('proforma_invoice_items', 'sort')) {
                    $table->unsignedSmallInteger('sort')->default(0)->after('total');
                }
                if (!Schema::hasColumn('proforma_invoice_items', 'created_at')) {
                    $table->timestamps();
                }
            });
        }
    }

    public function down(): void
    {
        // در Down فقط اگر وجود دارد حذف کن
        if (Schema::hasTable('proforma_invoice_items')) {
            Schema::dropIfExists('proforma_invoice_items');
        }
        if (Schema::hasTable('proforma_invoices')) {
            Schema::dropIfExists('proforma_invoices');
        }
    }
};
