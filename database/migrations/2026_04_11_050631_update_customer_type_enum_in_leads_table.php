<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE leads MODIFY COLUMN customer_type ENUM('Enquiry', '1st Time', 'Loyal', 'Premium', 'Discount/Bargain Hunter', 'Need Base', 'Impulse', 'Unqualified') DEFAULT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE leads MODIFY COLUMN customer_type ENUM('1st Time', 'Loyal', 'Discount/Bargain Hunter', 'Need Base', 'Impulse', 'Unqualified') DEFAULT NULL");
        }
    }
};
