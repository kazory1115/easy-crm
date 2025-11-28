<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    /**
     * 取得項目列表
     */
    public function index(Request $request)
    {
        $query = Item::with(['creator']);

        // 搜尋
        if ($request->has('search')) {
            $query->search($request->search);
        }

        // 分類篩選
        if ($request->has('category')) {
            $query->byCategory($request->category);
        }

        // 只取得啟用的
        if ($request->get('status') === 'active') {
            $query->active();
        }

        // 排序
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // 判斷是否需要分頁
        if ($request->get('paginate', false)) {
            $perPage = $request->get('per_page', 15);
            $items = $query->paginate($perPage);
        } else {
            $items = $query->get();
        }

        return response()->json($items);
    }

    /**
     * 取得單一項目
     */
    public function show($id)
    {
        $item = Item::with(['creator', 'updater'])
            ->findOrFail($id);

        return response()->json(['data' => $item]);
    }

    /**
     * 建立項目
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit' => 'required|string|max:50',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'category' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,inactive',
        ]);

        try {
            $item = Item::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'unit' => $validated['unit'],
                'price' => $validated['price'],
                'quantity' => $validated['quantity'],
                'category' => $validated['category'] ?? null,
                'status' => $validated['status'] ?? 'active',
                'created_by' => auth()->id(),
            ]);

            return response()->json([
                'message' => '項目建立成功',
                'data' => $item,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => '項目建立失敗',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 更新項目
     */
    public function update(Request $request, $id)
    {
        $item = Item::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'unit' => 'sometimes|required|string|max:50',
            'price' => 'sometimes|required|numeric|min:0',
            'quantity' => 'sometimes|required|integer|min:1',
            'category' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,inactive',
        ]);

        try {
            $item->fill($validated);
            $item->updated_by = auth()->id();
            $item->save();

            return response()->json([
                'message' => '項目更新成功',
                'data' => $item,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => '項目更新失敗',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * 刪除項目
     */
    public function destroy($id)
    {
        $item = Item::findOrFail($id);
        $item->delete();

        return response()->json([
            'message' => '項目刪除成功',
        ]);
    }

    /**
     * 批次刪除項目
     */
    public function batchDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer|exists:items,id',
        ]);

        try {
            Item::whereIn('id', $validated['ids'])->delete();

            return response()->json([
                'message' => '批次刪除成功',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => '批次刪除失敗',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
