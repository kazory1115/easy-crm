<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StockLevel;
use Illuminate\Http\Request;

class StockLevelController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:100',
            'warehouse_id' => 'nullable|integer|exists:warehouses,id',
            'item_id' => 'nullable|integer|exists:items,id',
            'stock_status' => 'nullable|in:in_stock,low_stock,out_of_stock',
            'sort_by' => 'nullable|string|in:id,quantity,reserved,min_level,max_level,created_at,updated_at',
            'sort_order' => 'nullable|string|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = StockLevel::query()->with([
            'warehouse:id,name,code,status',
            'item:id,name,category,unit,status',
        ]);

        if (!empty($validated['warehouse_id'])) {
            $query->where('warehouse_id', $validated['warehouse_id']);
        }

        if (!empty($validated['item_id'])) {
            $query->where('item_id', $validated['item_id']);
        }

        if (!empty($validated['search'])) {
            $keyword = $validated['search'];
            $query->where(function ($builder) use ($keyword) {
                $builder->whereHas('warehouse', function ($warehouseQuery) use ($keyword) {
                    $warehouseQuery->where('name', 'like', "%{$keyword}%")
                        ->orWhere('code', 'like', "%{$keyword}%");
                })->orWhereHas('item', function ($itemQuery) use ($keyword) {
                    $itemQuery->where('name', 'like', "%{$keyword}%")
                        ->orWhere('category', 'like', "%{$keyword}%");
                });
            });
        }

        if (!empty($validated['stock_status'])) {
            match ($validated['stock_status']) {
                'out_of_stock' => $query->whereRaw('(quantity - reserved) <= 0'),
                'low_stock' => $query->whereRaw('(quantity - reserved) <= min_level')->whereRaw('(quantity - reserved) > 0'),
                'in_stock' => $query->whereRaw('(quantity - reserved) > 0'),
            };
        }

        $sortBy = $validated['sort_by'] ?? 'updated_at';
        $sortOrder = strtolower($validated['sort_order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        return response()->json(
            $query->orderBy($sortBy, $sortOrder)->paginate((int)($validated['per_page'] ?? 15))
        );
    }

    public function show(StockLevel $stockLevel)
    {
        return response()->json([
            'data' => $stockLevel->load([
                'warehouse:id,name,code,location,status',
                'item:id,name,description,unit,price,quantity,category,status',
            ]),
        ]);
    }
}
