<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'manage profiles',
            'manage appointments',
            'manage memberships',
            'manage plans',
            'view dashboard',
            'book appointments',
            'write reviews',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign created permissions
        $roleAdmin = Role::create(['name' => 'admin']);
        $roleAdmin->givePermissionTo(Permission::all());

        $roleDoctor = Role::create(['name' => 'doctor']);
        $roleDoctor->givePermissionTo([
            'view dashboard',
            'manage profiles',
            'manage appointments',
            'manage memberships',
        ]);

        $rolePatient = Role::create(['name' => 'patient']);
        $rolePatient->givePermissionTo([
            'view dashboard',
            'book appointments',
            'write reviews',
        ]);
    }
}
