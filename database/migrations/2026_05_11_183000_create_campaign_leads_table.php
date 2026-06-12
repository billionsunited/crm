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
        Schema::create('campaign_leads', function (Blueprint $box) {
            $box->id();
            $box->string('customer_name')->nullable();
            $box->string('mobile')->nullable()->index();
            $box->string('mobile_1')->nullable()->index();
            $box->string('mobile_2')->nullable()->index();
            $box->string('email_id')->nullable()->index();
            $box->string('company_name')->nullable();
            $box->string('type_of_firm')->nullable();
            $box->string('place')->nullable();
            $box->string('product_interested')->nullable();
            $box->text('comment')->nullable();
            $box->string('rate')->nullable()->comment('Lead Type: Qualify, Only PAN & Aadhar, Didn’t sign MSA, No company');
            $box->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_leads');
    }
};
