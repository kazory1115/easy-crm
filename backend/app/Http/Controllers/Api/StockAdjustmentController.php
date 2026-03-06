<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StockAdjustment;
use App\Services\StockService;
use Illuminate\Http\Request;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class StockAdjustmentController extends Controller
{
    public function __construct(private readonly StockService $stockService)
    {
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:100',
            'warehouse_id' => 'nullable|integer|exists:warehouses,id',
            'item_id' => 'nullable|integer|exists:items,id',
            'reason' => 'nullable|string|max:100',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'sort_by' => 'nullable|string|in:id,before_qty,after_qty,created_at',
            'sort_order' => 'nullable|string|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = StockAdjustment::query()->with([
            'warehouse:id,name,code,status',
            'item:id,name,category,unit,status',
            'creator:id,name,email',
        ]);

        if (!empty($validated['warehouse_id'])) {
            $query->where('warehouse_id', $validated['warehouse_id']);
        }

        if (!empty($validated['item_id'])) {
            $query->where('item_id', $validated['item_id']);
        }

        if (!empty($validated['reason'])) {
            $query->where('reason', $validated['reason']);
        }

        if (!empty($validated['date_from'])) {
            $query->whereDate('created_at', '>=', $validated['date_from']);
        }

        if (!empty($validated['date_to'])) {
            $query->whereDate('created_at', '<=', $validated['date_to']);
        }

        if (!empty($validated['search'])) {
            $keyword = $validated['search'];
            $query->where(function ($builder) use ($keyword) {
                $builder->where('reason', 'like', "%{$keyword}%")
                    ->orWhere('note', 'like', "%{$keyword}%")
                    ->orWhereHas('warehouse', function ($warehouseQuery) use ($keyword) {
                        $warehouseQuery->where('name', 'like', "%{$keyword}%")
                            ->orWhere('code', 'like', "%{$keyword}%");
                    })
                    ->orWhereHas('item', function ($itemQuery) use ($keyword) {
                        $itemQuery->where('name', 'like', "%{$keyword}%")
                            ->orWhere('category', 'like', "%{$keyword}%");
                    });
            });
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
            'warehouse_id' => 'required|integer|exists:warehouses,id',
            'item_id' => 'required|integer|exists:items,id',
            'after_qty' => 'required|integer|min:0',
            'reason' => 'nullable|string|max:100',
            'note' => 'nullable|string',
        ]);

        try {
            $result = $this->stockService->adjustStock(
                (int) $validated['item_id'],
                (int) $validated['warehouse_id'],
                (int) $validated['after_qty'],
                (int) auth()->id(),
                $validated['reason'] ?? null,
                $validated['note'] ?? null
            );

            return response()->json([
                'message' => '庫存調整成功',
                'data' => $result,
            ], 201);
        } catch (InvalidArgumentException|RuntimeException $e) {
            return response()->json([
                'message' => '庫存調整失敗',
                'error' => $e->getMessage(),
            ], 422);
        } catch (Throwable $e) {
            return response()->json([
                'message' => '庫存調整失敗',
                'error' => '系統發生未預期錯誤',
            ], 500);
        }
    }

    public function show(StockAdjustment $stockAdjustment)
    {
        return response()->json([
            'data' => $stockAdjustment->load([
                'warehouse:id,name,code,location,status',
                'item:id,name,description,unit,price,quantity,category,status',
                'creator:id,name,email',
            ]),
        ]);
    }
}
