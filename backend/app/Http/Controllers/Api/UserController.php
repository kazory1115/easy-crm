<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserPermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct(private readonly UserPermissionService $userPermissionService)
    {
    }

    public function index(Request $request)
    {
        $query = User::query()->with(['roles', 'permissions']);

        if ($request->has('search')) {
            $keyword = $request->search;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%");
            });
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = (int) $request->get('per_page', 15);

        if ($request->boolean('paginate', true)) {
            $users = $query->paginate($perPage);
            $users->setCollection(
                $users->getCollection()->map(fn (User $user) => $this->userPermissionService->serializeUser($user))
            );

            return response()->json($users);
        }

        return response()->json([
            'data' => $query->get()->map(
                fn (User $user) => $this->userPermissionService->serializeUser($user)
            ),
        ]);
    }

    public function show($id)
    {
        $user = User::with(['roles', 'permissions'])->findOrFail($id);

        return response()->json([
            'data' => $this->userPermissionService->serializeUser($user),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatePayload($request, null, true);
        $this->guardAccessControlMutation($validated);

        $user = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'phone' => $validated['phone'] ?? null,
                'department' => $validated['department'] ?? null,
                'position' => $validated['position'] ?? null,
            ]);

            $roles = $validated['roles'] ?? ['staff'];
            $directPermissions = $validated['direct_permissions'] ?? [];

            $this->userPermissionService->syncRoles($user, $roles);
            $this->userPermissionService->syncDirectPermissions($user, $directPermissions);

            return $user->fresh(['roles', 'permissions']);
        });

        return response()->json([
            'message' => '員工建立成功',
            'data' => $this->userPermissionService->serializeUser($user),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $validated = $this->validatePayload($request, $id, false);
        $this->guardAccessControlMutation($validated);

        $user = DB::transaction(function () use ($user, $validated) {
            $payload = collect($validated)
                ->only(['name', 'email', 'phone', 'department', 'position'])
                ->toArray();

            if (!empty($validated['password'])) {
                $payload['password'] = Hash::make($validated['password']);
            }

            $user->update($payload);

            if (array_key_exists('roles', $validated)) {
                $this->guardSelfRoleMutation($user, $validated['roles']);
                $this->userPermissionService->syncRoles($user, $validated['roles']);
            }

            if (array_key_exists('direct_permissions', $validated)) {
                $this->guardSelfPermissionMutation($user, $validated['direct_permissions']);
                $this->userPermissionService->syncDirectPermissions($user, $validated['direct_permissions']);
            }

            return $user->fresh(['roles', 'permissions']);
        });

        return response()->json([
            'message' => '員工資料更新成功',
            'data' => $this->userPermissionService->serializeUser($user),
        ]);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return response()->json([
                'message' => '不可刪除自己的帳號',
            ], 403);
        }

        $user->delete();

        return response()->json([
            'message' => '員工刪除成功',
        ]);
    }

    public function roles($id)
    {
        $user = User::with('roles')->findOrFail($id);

        return response()->json([
            'data' => [
                'roles' => $user->getRoleNames()->values()->all(),
            ],
        ]);
    }

    public function updateRoles(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $validated = $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'string',
        ]);

        $this->guardSelfRoleMutation($user, $validated['roles']);
        $this->userPermissionService->syncRoles($user, $validated['roles']);

        return response()->json([
            'message' => '角色更新成功',
            'data' => [
                'roles' => $user->fresh()->getRoleNames()->values()->all(),
            ],
        ]);
    }

    public function permissions($id)
    {
        $user = User::with('permissions')->findOrFail($id);

        return response()->json([
            'data' => [
                'direct_permissions' => $user->permissions->pluck('name')->values()->all(),
                'permissions' => $user->getAllPermissions()->pluck('name')->values()->all(),
            ],
        ]);
    }

    public function updatePermissions(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $validated = $request->validate([
            'direct_permissions' => 'required|array',
            'direct_permissions.*' => 'string',
        ]);

        $this->guardSelfPermissionMutation($user, $validated['direct_permissions']);
        $this->userPermissionService->syncDirectPermissions($user, $validated['direct_permissions']);

        $user->refresh();

        return response()->json([
            'message' => '權限更新成功',
            'data' => [
                'direct_permissions' => $user->permissions->pluck('name')->values()->all(),
                'permissions' => $user->getAllPermissions()->pluck('name')->values()->all(),
            ],
        ]);
    }

    public function stats()
    {
        $userId = auth()->id();

        $stats = [
            'total_quotes_created' => \App\Models\Quote::where('created_by', $userId)->count(),
            'total_customers' => \App\Models\Customer::where('created_by', $userId)->count(),
            'recent_activities' => \App\Models\ActivityLog::where('user_id', $userId)
                ->latest()
                ->take(10)
                ->get(),
        ];

        return response()->json(['data' => $stats]);
    }

    private function validatePayload(Request $request, ?int $userId, bool $isCreate): array
    {
        $passwordRules = $isCreate ? 'required|string|min:8' : 'nullable|string|min:8';

        return $request->validate([
            'name' => ($isCreate ? 'required' : 'sometimes|required') . '|string|max:255',
            'email' => [
                $isCreate ? 'required' : 'sometimes',
                'email',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'password' => $passwordRules,
            'phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
            'roles' => 'sometimes|array',
            'roles.*' => 'string',
            'direct_permissions' => 'sometimes|array',
            'direct_permissions.*' => 'string',
        ]);
    }

    private function guardSelfRoleMutation(User $targetUser, array $roles): void
    {
        if ($targetUser->id !== auth()->id()) {
            return;
        }

        if (!in_array('admin', $roles, true) && !in_array('super-admin', $roles, true)) {
            abort(422, '不可移除自己最後的管理角色');
        }
    }

    private function guardSelfPermissionMutation(User $targetUser, array $permissions): void
    {
        if ($targetUser->id !== auth()->id()) {
            return;
        }

        if (!in_array('role.manage', $permissions, true) && !$targetUser->hasRole(['admin', 'super-admin'])) {
            abort(422, '不可移除自己最後的權限管理能力');
        }
    }

    private function guardAccessControlMutation(array $validated): void
    {
        $touchingRoles = array_key_exists('roles', $validated);
        $touchingPermissions = array_key_exists('direct_permissions', $validated);

        if (($touchingRoles || $touchingPermissions) && !auth()->user()?->can('role.manage')) {
            abort(403, '沒有管理角色與權限的權限');
        }
    }
}
