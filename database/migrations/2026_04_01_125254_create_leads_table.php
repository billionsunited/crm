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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null');
            $table->string('service_type')->nullable();
            $table->string('record_id')->unique()->nullable();
            $table->enum('kyc', ['Done', 'Not Done'])->default('Not Done');
            $table->boolean('master_service_agreement_signed')->default(false);
            $table->enum('customer_type', ['1st Time', 'Loyal', 'Discount/Bargain Hunter', 'Need Base', 'Impulse', 'Unqualified'])->nullable();
            $table->enum('lead_status', ['Active', 'Non Active'])->default('Active');
            $table->string('customer_name')->nullable();
            $table->string('reference')->nullable();
            $table->string('mobile')->nullable();
            $table->string('alternate_mobile')->nullable();
            $table->string('email_id')->nullable();
            $table->string('alternate_email_id')->nullable();
            $table->string('designation')->nullable();
            $table->string('city')->nullable();
            $table->string('nature_of_industry')->nullable();
            $table->string('company_name')->nullable();
            $table->text('company_address')->nullable();
            $table->string('gst_no')->nullable();
            $table->string('pan_number')->nullable();
            $table->string('udyam_registration_certificate')->nullable();
            $table->string('website')->nullable();
            $table->string('initial_product_interest')->nullable();
            $table->text('product_demand')->nullable();
            $table->integer('quantity')->nullable();
            $table->decimal('rate', 10, 2)->nullable();
            $table->date('previous_deals_and_date')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->string('records_owner')->nullable();
            $table->text('comment')->nullable();
            // Document uploads
            $table->string('doc_pan')->nullable();
            $table->string('doc_aadhar')->nullable();
            $table->string('doc_gst')->nullable();
            $table->string('doc_certificate_incorporation_udyam')->nullable();
            $table->string('doc_trai_dlt')->nullable();
            $table->string('doc_dsa_license')->nullable();
            $table->string('doc_company_id_card')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
