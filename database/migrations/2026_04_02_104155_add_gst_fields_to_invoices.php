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
            $table->string('state')->nullable()->after('city');
            $table->string('state_code')->nullable()->after('state');
            $table->string('purchase_order')->nullable()->after('state_code');
            $table->string('service_description_meta')->nullable()->after('purchase_order');
            $table->date('due_date')->nullable()->after('invoice_date');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->string('hsn_sac')->nullable()->after('service_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['state', 'state_code', 'purchase_order', 'service_description_meta', 'due_date']);
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn('hsn_sac');
        });
    }
};
