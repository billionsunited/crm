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
        Schema::create('followup_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lead_id');
            $table->unsignedBigInteger('user_id');
            $table->string('title');
            $table->text('message');
            $table->string('redirect_url');
            $table->string('customer_name')->nullable();
            $table->string('company_name')->nullable();
            $table->date('follow_up_date');
            $table->boolean('is_triggered')->default(0);
            $table->boolean('is_read')->default(0);
            $table->timestamps();

            // Indexes for performance
            $table->index('follow_up_date');
            $table->index('is_triggered');
            $table->index('is_read');
            $table->index('user_id');
            $table->index('lead_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('followup_notifications');
    }
};
