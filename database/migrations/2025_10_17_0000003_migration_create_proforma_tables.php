<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create proforma_invoices table
        Schema::create('proforma_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('building_id')->nullable();
            $table->string('package_slug');
            $table->enum('period', ['monthly', 'quarterly', 'yearly'])->default('monthly');
            $table->bigInteger('subtotal')->default(0);
            $table->bigInteger('discount')->default(0);
            $table->integer('tax_percent')->default(10);
            $table->bigInteger('tax')->default(0);
            $table->bigInteger('total')->default(0);
            $table->string('currency', 10)->default('IRR');
            $table->enum('status', ['draft', 'pending', 'sent', 'paid', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('building_id')
                ->references('id')
                ->on('buildings')
                ->onDelete('set null');

            $table->index('building_id');
            $table->index('package_slug');
            $table->index('period');
            $table->index('status');
            $table->index('created_at');
        });

        // Create proforma_invoice_items table
        Schema::create('proforma_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('proforma_invoice_id');
            $table->string('title');
            $table->integer('qty')->default(1);
            $table->bigInteger('unit_price')->default(0);
            $table->bigInteger('line_total')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('proforma_invoice_id')
                ->references('id')
                ->on('proforma_invoices')
                ->onDelete('cascade');

            $table->index('proforma_invoice_id');
        });

        // Create unit_pricing_rates table (optional)
        Schema::create('unit_pricing_rates', function (Blueprint $table) {
            $table->id();
            $table->string('package_slug')->unique();
            $table->bigInteger('rate_monthly');
            $table->bigInteger('rate_quarterly')->nullable();
            $table->bigInteger('rate_yearly')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('package_slug');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proforma_invoice_items');
        Schema::dropIfExists('proforma_invoices');
        Schema::dropIfExists('unit_pricing_rates');
    }
};
