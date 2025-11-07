<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        // 重置快取的角色和權限
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // === 建立權限 ===
        // 用戶管理權限
        Permission::create(['name' => 'view users']);
        Permission::create(['name' => 'create users']);
        Permission::create(['name' => 'edit users']);
        Permission::create(['name' => 'delete users']);

        // 客戶管理權限
        Permission::create(['name' => 'view customers']);
        Permission::create(['name' => 'create customers']);
        Permission::create(['name' => 'edit customers']);
        Permission::create(['name' => 'delete customers']);

        // 聯絡人管理權限
        Permission::create(['name' => 'view contacts']);
        Permission::create(['name' => 'create contacts']);
        Permission::create(['name' => 'edit contacts']);
        Permission::create(['name' => 'delete contacts']);

        // 商機管理權限
        Permission::create(['name' => 'view opportunities']);
        Permission::create(['name' => 'create opportunities']);
        Permission::create(['name' => 'edit opportunities']);
        Permission::create(['name' => 'delete opportunities']);

        // 產品管理權限
        Permission::create(['name' => 'view products']);
        Permission::create(['name' => 'create products']);
        Permission::create(['name' => 'edit products']);
        Permission::create(['name' => 'delete products']);

        // 報表權限
        Permission::create(['name' => 'view reports']);
        Permission::create(['name' => 'export reports']);

        // 系統設定權限
        Permission::create(['name' => 'manage settings']);
        Permission::create(['name' => 'manage roles']);

        // === 建立角色並分配權限 ===

        // 超級管理員 - 擁有所有權限
        $superAdmin = Role::create(['name' => 'super-admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // 管理員 - 擁有大部分權限，但不能管理系統設定
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo([
            'view users', 'create users', 'edit users',
            'view customers', 'create customers', 'edit customers', 'delete customers',
            'view contacts', 'create contacts', 'edit contacts', 'delete contacts',
            'view opportunities', 'create opportunities', 'edit opportunities', 'delete opportunities',
            'view products', 'create products', 'edit products', 'delete products',
            'view reports', 'export reports',
        ]);

        // 經理 - 可以查看和管理客戶、商機、報表
        $manager = Role::create(['name' => 'manager']);
        $manager->givePermissionTo([
            'view users',
            'view customers', 'create customers', 'edit customers',
            'view contacts', 'create contacts', 'edit contacts',
            'view opportunities', 'create opportunities', 'edit opportunities',
            'view products',
            'view reports', 'export reports',
        ]);

        // 業務 - 主要負責客戶和商機
        $sales = Role::create(['name' => 'sales']);
        $sales->givePermissionTo([
            'view customers', 'create customers', 'edit customers',
            'view contacts', 'create contacts', 'edit contacts',
            'view opportunities', 'create opportunities', 'edit opportunities',
            'view products',
            'view reports',
        ]);

        // 客服 - 主要負責客戶和聯絡人
        $customerService = Role::create(['name' => 'customer-service']);
        $customerService->givePermissionTo([
            'view customers', 'edit customers',
            'view contacts', 'create contacts', 'edit contacts',
            'view products',
        ]);
    }
}
