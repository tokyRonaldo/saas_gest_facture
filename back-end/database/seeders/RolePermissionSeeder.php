<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'clients.view', 'clients.create', 'clients.edit', 'clients.delete',
            'products.view', 'products.create', 'products.edit', 'products.delete',
            'invoices.view', 'invoices.create', 'invoices.edit', 'invoices.delete',
            'invoices.download', 'invoices.cancel',
             'stock.manage', // ← nouvelle permission
            'payments.view', 'payments.create', 'payments.edit', 'payments.delete',
            'dashboard.view', 'reports.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::all());

        $user = Role::firstOrCreate(['name' => 'user']);
        $user->syncPermissions([
            'clients.view', 'clients.create', 'clients.edit', 'clients.delete',
            'products.view', 'products.create', 'products.edit', 'products.delete',
            'invoices.view', 'invoices.create', 'invoices.edit',
            'invoices.download',
            'payments.view', 'payments.create', 'payments.edit',
            'dashboard.view',
        ]);

        $commercial = Role::firstOrCreate(['name' => 'commercial']);
        $commercial->syncPermissions($user->permissions); // hérite de USER
        $commercial->givePermissionTo([
             'stock.manage',
            // extras propres au commercial, à ajuster selon ce qu'on avait défini
        ]);
    }
}