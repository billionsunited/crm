<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ensure the permission exists
        $permission = Permission::firstOrCreate(['name' => 'invoice-or-section', 'guard_name' => 'web']);

        // Assign to admin role if it exists
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($permission);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permission = Permission::where('name', 'invoice-or-section')->first();
        if ($permission) {
            $permission->delete();
        }
    }
};
