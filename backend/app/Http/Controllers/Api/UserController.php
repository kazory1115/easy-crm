<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * 取得用戶列表 (員工列表)
     */
    public function index(Request $request)
    {
        $query = User::query();

        // 搜尋
        if ($request->has('search')) {
            $keyword = $request->search;
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%");
            });
        }

        // 排序
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // 分頁
        $perPage = $request->get('per_page', 15);

        if ($request->get('paginate', true)) {
            $users = $query->paginate($perPage);
        } else {
            $users = $query->get();
        }

        return response()->json($users);
    }

    /**
     * 取得單一用戶
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json(['data' => $user]);
    }

    /**
     * 建立用戶 (新增員工)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'department' => $validated['department'] ?? null,
            'position' => $validated['position'] ?? null,
        ]);

        return response()->json([
            'message' => '員工建立成功',
            'data' => $user,
        ], 201);
    }

    /**
     * 更新用戶
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8',
            'phone' => 'nullable|string|max:20',
            'department' => 'nullable|string|max:100',
            'position' => 'nullable|string|max:100',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'message' => '員工資料更新成功',
            'data' => $user,
        ]);
    }

    /**
     * 刪除用戶
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // 防止刪除自己
        if ($user->id === auth()->id()) {
            return response()->json([
                'message' => '無法刪除自己的帳號',
            ], 403);
        }

        $user->delete();

        return response()->json([
            'message' => '員工刪除成功',
        ]);
    }

    /**
     * 取得當前登入用戶的統計資料
     */
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
}
