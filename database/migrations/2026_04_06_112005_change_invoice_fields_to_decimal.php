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
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('taxable_value', 12, 2)->default(0)->change();
            $table->decimal('cgst_amount', 12, 2)->default(0)->change();
            $table->decimal('sgst_amount', 12, 2)->default(0)->change();
            $table->decimal('igst_amount', 12, 2)->default(0)->change();
            $table->decimal('total_invoice_value', 12, 2)->default(0)->change();
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->decimal('qty', 8, 2)->default(1)->change();
            $table->decimal('rate', 12, 2)->default(0)->change();
            $table->decimal('total', 12, 2)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('taxable_value')->nullable()->change();
            $table->string('cgst_amount')->nullable()->change();
            $table->string('sgst_amount')->nullable()->change();
            $table->string('igst_amount')->nullable()->change();
            $table->string('total_invoice_value')->nullable()->change();
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->string('qty')->nullable()->change();
            $table->string('rate')->nullable()->change();
            $table->string('total')->nullable()->change();
        });
    }
};
