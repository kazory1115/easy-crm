<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserPermissionService
{
    public function serializeUser(User $user): array
    {
        $user->loadMissing(['roles', 'permissions']);

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'department' => $user->department,
            'position' => $user->position,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'roles' => $user->getRoleNames()->values()->all(),
            'direct_permissions' => $user->permissions->pluck('name')->values()->all(),
            'permissions' => $user->getAllPermissions()->pluck('name')->values()->all(),
        ];
    }

    public function syncRoles(User $user, array $roles): void
    {
        $roleNames = collect($roles)
            ->filter(fn ($role) => is_string($role) && $role !== '')
            ->values()
            ->all();

        $this->assertRolesExist($roleNames);

        $user->syncRoles($roleNames);
    }

    public function syncDirectPermissions(User $user, array $permissions): void
    {
        $permissionNames = collect($permissions)
            ->filter(fn ($permission) => is_string($permission) && $permission !== '')
            ->values()
            ->all();

        $this->assertPermissionsExist($permissionNames);

        $user->syncPermissions($permissionNames);
    }

    public function accessControlOptions(): array
    {
        $roles = Role::query()
            ->orderBy('name')
            ->pluck('name')
            ->values()
            ->all();

        $allPermissions = Permission::query()
            ->orderBy('name')
            ->pluck('name')
            ->values()
            ->all();

        return [
            'roles' => $roles,
            'modules' => $this->moduleDefinitions(),
            'permissions' => $allPermissions,
        ];
    }

    public function moduleDefinitions(): array
    {
        return [
            [
                'id' => 'quote',
                'name' => '報價單',
                'permissions' => [
                    ['name' => 'quote.view', 'label' => '查看報價單'],
                    ['name' => 'quote.create', 'label' => '建立報價單'],
                    ['name' => 'quote.edit', 'label' => '編輯報價單'],
                    ['name' => 'quote.delete', 'label' => '刪除報價單'],
                    ['name' => 'quote.template.manage', 'label' => '管理報價模板'],
                    ['name' => 'quote.item.manage', 'label' => '管理報價項目'],
                ],
            ],
            [
                'id' => 'order',
                'name' => '訂單',
                'permissions' => [
                    ['name' => 'order.view', 'label' => '查看訂單'],
                    ['name' => 'order.create', 'label' => '建立訂單'],
                    ['name' => 'order.edit', 'label' => '編輯訂單'],
                    ['name' => 'order.delete', 'label' => '刪除訂單'],
                ],
            ],
            [
                'id' => 'crm',
                'name' => 'CRM',
                'permissions' => [
                    ['name' => 'crm.view', 'label' => '查看客戶與商機'],
                    ['name' => 'crm.create', 'label' => '建立客戶與商機'],
                    ['name' => 'crm.edit', 'label' => '編輯客戶與商機'],
                    ['name' => 'crm.delete', 'label' => '刪除客戶與商機'],
                ],
            ],
            [
                'id' => 'inventory',
                'name' => '庫存',
                'permissions' => [
                    ['name' => 'inventory.view', 'label' => '查看庫存'],
                    ['name' => 'inventory.create', 'label' => '建立庫存資料'],
                    ['name' => 'inventory.edit', 'label' => '異動與調整庫存'],
                    ['name' => 'inventory.delete', 'label' => '刪除庫存資料'],
                ],
            ],
            [
                'id' => 'staff',
                'name' => '員工',
                'permissions' => [
                    ['name' => 'staff.view', 'label' => '查看員工'],
                    ['name' => 'staff.create', 'label' => '建立員工'],
                    ['name' => 'staff.edit', 'label' => '編輯員工'],
                    ['name' => 'staff.delete', 'label' => '刪除員工'],
                    ['name' => 'role.manage', 'label' => '管理角色與權限'],
                ],
            ],
            [
                'id' => 'report',
                'name' => '報表',
                'permissions' => [
                    ['name' => 'report.view', 'label' => '查看報表'],
                    ['name' => 'report.export', 'label' => '匯出報表'],
                ],
            ],
        ];
    }

    private function assertRolesExist(array $roles): void
    {
        if ($roles === []) {
            return;
        }

        $available = Role::query()->pluck('name');
        $missing = collect($roles)->diff($available)->values();

        if ($missing->isNotEmpty()) {
            throw ValidationException::withMessages([
                'roles' => ['包含不存在的角色：' . $missing->implode(', ')],
            ]);
        }
    }

    private function assertPermissionsExist(array $permissions): void
    {
        if ($permissions === []) {
            return;
        }

        $available = Permission::query()->pluck('name');
        $missing = collect($permissions)->diff($available)->values();

        if ($missing->isNotEmpty()) {
            throw ValidationException::withMessages([
                'direct_permissions' => ['包含不存在的權限：' . $missing->implode(', ')],
            ]);
        }
    }
}
