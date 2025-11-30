<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ShieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Super Admin role
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);

        // Create Editor role
        $editor = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);

        // Create EO (Event Organizer) role for frontend submitters (no panel permissions by default)
        Role::firstOrCreate(['name' => 'EO', 'guard_name' => 'web']);

        // Get all permissions
        $permissions = Permission::all();

        if ($permissions->isEmpty()) {
            $this->command->warn('No permissions found. Generating permissions...');
            $this->command->call('shield:generate', [
                '--all' => true,
                '--option' => 'policies_and_permissions',
                '--panel' => 'admin',
            ]);
            $permissions = Permission::all();
        }

        // Assign all permissions to Super Admin
        $superAdmin->syncPermissions($permissions);

        // Assign limited permissions to Editor
        // Editor can manage: Events, Blog, Direktori (Vendors, Running Communities)
        $editorPermissions = $permissions->filter(function ($permission) {
            $name = $permission->name;
            
            // Allow all Event permissions
            if (str_contains($name, 'Event')) {
                return true;
            }
            
            // Allow all Blog permissions (Post, Category, Author, Tag)
            if (str_contains($name, 'Post') || str_contains($name, 'Category') || str_contains($name, 'Author') || str_contains($name, 'Tag')) {
                return true;
            }
            
            // Allow Vendor and Running Community permissions
            if (str_contains($name, 'Vendor') || str_contains($name, 'RunningCommunity')) {
                return true;
            }
            
            // Allow Dashboard view
            if ($name === 'view_Dashboard') {
                return true;
            }
            
            return false;
        });

        $editor->syncPermissions($editorPermissions);

        // Create Super Admin user
        $superAdminUser = User::firstOrCreate(
            ['email' => 'admin@marathon.id'],
            [
                'name' => 'Super Admin',
                'phone_number' => '081234567890',
                'password' => bcrypt('password'), // Change this in production!
                'email_verified_at' => now(),
            ]
        );

        // Assign super_admin role
        if (!$superAdminUser->hasRole('super_admin')) {
            $superAdminUser->assignRole('super_admin');
        }

        $this->command->info('Roles created successfully!');
        $this->command->info('Super Admin: Full access');
        $this->command->info('Editor: Limited access (Events, Blog, Direktori only)');
        $this->command->newLine();
        $this->command->warn('Super Admin User Created:');
        $this->command->info('Email: admin@marathon.id');
        $this->command->info('Password: password');
        $this->command->warn('⚠️  Please change the password after first login!');
    }
}