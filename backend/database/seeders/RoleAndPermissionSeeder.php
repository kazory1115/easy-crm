<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 清除快取的角色/權限
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 以模組為單位的權限定義（與前端一致）
        $permissionNames = [
            // Quote / Template / Items
            'quote.view',
            'quote.create',
            'quote.edit',
            'quote.delete',
            'quote.template.manage',
            'quote.item.manage',

            // Staff / Role
            'staff.view',
            'staff.create',
            'staff.edit',
            'staff.delete',
            'role.manage',

            // CRM
            'crm.view',
            'crm.create',
            'crm.edit',
            'crm.delete',

            // Inventory
            'inventory.view',
            'inventory.create',
            'inventory.edit',
            'inventory.delete',

            // Report
            'report.view',
            'report.export',
        ];

        foreach ($permissionNames as $name) {
            Permission::firstOrCreate(['name' => $name]);
        }

        // 角色
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $staff = Role::firstOrCreate(['name' => 'staff']);

        // 權限綁定
        $superAdmin->syncPermissions(Permission::all());

        $admin->syncPermissions($permissionNames);

        $manager->syncPermissions([
            'quote.view', 'quote.create', 'quote.edit', 'quote.template.manage', 'quote.item.manage',
            'crm.view', 'crm.create', 'crm.edit',
            'inventory.view', 'inventory.create', 'inventory.edit',
            'report.view',
            'staff.view'
        ]);

        $staff->syncPermissions([
            'quote.view', 'quote.create',
            'crm.view',
            'inventory.view',
            'report.view'
        ]);
    }
}
