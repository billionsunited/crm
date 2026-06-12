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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            
            // Core Invoice Info
            $table->string('invoice_number')->unique();
            $table->date('invoice_date');
            
            // Client / Receiver Data
            $table->string('client_name')->nullable();
            $table->string('organisation_name')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('udyam_certificate')->nullable();
            $table->string('pan_no')->nullable();
            $table->string('gstin_unique_id')->nullable();
            
            // Tax and Accounting
            $table->enum('tax_type', ['local', 'outstation'])->default('local');
            $table->decimal('taxable_value', 12, 2)->default(0);
            
            $table->decimal('cgst_percent', 5, 2)->default(0);
            $table->decimal('cgst_amount', 12, 2)->default(0);
            
            $table->decimal('sgst_percent', 5, 2)->default(0);
            $table->decimal('sgst_amount', 12, 2)->default(0);
            
            $table->decimal('igst_percent', 5, 2)->default(0);
            $table->decimal('igst_amount', 12, 2)->default(0);
            
            $table->decimal('total_invoice_value', 12, 2)->default(0);
            $table->text('total_invoice_value_words')->nullable();
            
            $table->string('pdf_file_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
