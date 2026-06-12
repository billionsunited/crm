<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Clear existing permissions and roles to start fresh
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \DB::table('role_has_permissions')->truncate();
        \DB::table('model_has_permissions')->truncate();
        \DB::table('model_has_roles')->truncate();
        \DB::table('permissions')->truncate();
        \DB::table('roles')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Define permissions
        $permissions = [
            'dashboard-access',
            'lead-view',
            'lead-edit',
            'lead-add',
            'lead-delete',
            'lead-import',
            'lead-export',
            'invoice-section',
            'invoice-export',
            'invoice-or-section',
            'invoice-or-export',
            'vendor-section',
            'campaign-send',
            'document-section',
            'company-info-section',
            'email-section',
            'lead-send-document',
            'campaign-view',
            'campaign-add',
            'campaign-edit',
            'campaign-delete',
            'campaign-import',
            'campaign-export',
            'client-po-access',
            'vendor-po-access',
            'email-template-view',
            'email-template-add',
            'email-template-edit',
            'email-template-delete',
            'email-template-send',
            'whatsapp-icon',
            'lead-contact-view',
            'enquiry-vendor-contact-view',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create Admin role and assign all permissions
        $adminRole = Role::findOrCreate('admin');
        $adminRole->syncPermissions(Permission::all());

        // Assign Admin role to existing admin users
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->assignRole($adminRole);
        }
        
        // Create a default User role with limited permissions
        $userRole = Role::findOrCreate('user');
        $userRole->syncPermissions([
            'lead-view',
            'lead-add',
            'document-section',
        ]);

        // Assign User role to existing user users
        $users = User::where('role', 'user')->get();
        foreach ($users as $user) {
            $user->assignRole($userRole);
        }
    }
}
