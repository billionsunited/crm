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
            $table->string('invoice_type')->default('proforma')->after('invoice_number');
            $table->boolean('is_paid')->default(0)->after('invoice_type');
            $table->integer('invoice_sequence')->nullable()->after('is_paid');
            $table->string('financial_year')->nullable()->after('invoice_sequence');
            $table->timestamp('paid_at')->nullable()->after('financial_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['invoice_type', 'is_paid', 'invoice_sequence', 'financial_year', 'paid_at']);
        });
    }
};
