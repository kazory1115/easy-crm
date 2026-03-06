<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:100',
            'status' => 'nullable|in:active,inactive',
            'sort_by' => 'nullable|string|in:id,name,code,status,created_at,updated_at',
            'sort_order' => 'nullable|string|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = Warehouse::query()
            ->with(['creator:id,name', 'updater:id,name'])
            ->withCount(['stockLevels', 'stockMovements', 'stockAdjustments']);

        if (!empty($validated['search'])) {
            $keyword = $validated['search'];
            $query->where(function ($builder) use ($keyword) {
                $builder->where('name', 'like', "%{$keyword}%")
                    ->orWhere('code', 'like', "%{$keyword}%")
                    ->orWhere('location', 'like', "%{$keyword}%");
            });
        }

        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        $sortBy = $validated['sort_by'] ?? 'created_at';
        $sortOrder = strtolower($validated['sort_order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        return response()->json(
            $query->orderBy($sortBy, $sortOrder)->paginate((int)($validated['per_page'] ?? 15))
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100|unique:warehouses,code',
            'location' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,inactive',
        ]);

        try {
            $warehouse = Warehouse::create([
                ...$validated,
                'status' => $validated['status'] ?? 'active',
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            return response()->json([
                'message' => '倉庫建立成功',
                'data' => $warehouse->fresh(['creator:id,name', 'updater:id,name']),
            ], 201);
        } catch (Throwable $e) {
            return response()->json([
                'message' => '倉庫建立失敗',
                'error' => '系統發生未預期錯誤',
            ], 500);
        }
    }

    public function show(Warehouse $warehouse)
    {
        $warehouse->load([
            'creator:id,name,email',
            'updater:id,name,email',
        ])->loadCount(['stockLevels', 'stockMovements', 'stockAdjustments']);

        return response()->json([
            'data' => $warehouse,
        ]);
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('warehouses', 'code')->ignore($warehouse->id),
            ],
            'location' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,inactive',
        ]);

        try {
            $warehouse->fill($validated);
            $warehouse->updated_by = auth()->id();
            $warehouse->save();

            return response()->json([
                'message' => '倉庫更新成功',
                'data' => $warehouse->fresh(['creator:id,name', 'updater:id,name']),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => '倉庫更新失敗',
                'error' => '系統發生未預期錯誤',
            ], 500);
        }
    }

    public function destroy(Warehouse $warehouse)
    {
        $hasStock = $warehouse->stockLevels()
            ->where(function ($query) {
                $query->where('quantity', '>', 0)
                    ->orWhere('reserved', '>', 0);
            })
            ->exists();

        if ($hasStock) {
            return response()->json([
                'message' => '倉庫刪除失敗',
                'error' => '倉庫仍有庫存，無法刪除。',
            ], 409);
        }

        if ($warehouse->stockMovements()->exists() || $warehouse->stockAdjustments()->exists()) {
            return response()->json([
                'message' => '倉庫刪除失敗',
                'error' => '倉庫已有歷史異動紀錄，無法刪除。',
            ], 409);
        }

        $warehouse->delete();

        return response()->json([
            'message' => '倉庫刪除成功',
        ]);
    }
}
