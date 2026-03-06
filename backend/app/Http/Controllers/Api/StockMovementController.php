<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use App\Services\StockService;
use Illuminate\Http\Request;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class StockMovementController extends Controller
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
            'type' => 'nullable|in:inbound,outbound,transfer,adjustment',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'sort_by' => 'nullable|string|in:id,type,quantity,occurred_at,created_at',
            'sort_order' => 'nullable|string|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = StockMovement::query()->with([
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

        if (!empty($validated['type'])) {
            $query->where('type', $validated['type']);
        }

        if (!empty($validated['date_from'])) {
            $query->whereDate('occurred_at', '>=', $validated['date_from']);
        }

        if (!empty($validated['date_to'])) {
            $query->whereDate('occurred_at', '<=', $validated['date_to']);
        }

        if (!empty($validated['search'])) {
            $keyword = $validated['search'];
            $query->where(function ($builder) use ($keyword) {
                $builder->where('note', 'like', "%{$keyword}%")
                    ->orWhere('reference_type', 'like', "%{$keyword}%")
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

        $sortBy = $validated['sort_by'] ?? 'occurred_at';
        $sortOrder = strtolower($validated['sort_order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        return response()->json(
            $query->orderBy($sortBy, $sortOrder)->paginate((int)($validated['per_page'] ?? 15))
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:inbound,outbound,transfer',
            'item_id' => 'required|integer|exists:items,id',
            'warehouse_id' => 'required_unless:type,transfer|nullable|integer|exists:warehouses,id',
            'source_warehouse_id' => 'required_if:type,transfer|nullable|integer|exists:warehouses,id',
            'target_warehouse_id' => 'required_if:type,transfer|nullable|integer|exists:warehouses,id',
            'quantity' => 'required|integer|min:1',
            'reference_type' => 'nullable|string|max:255',
            'reference_id' => 'nullable|integer|min:1',
            'note' => 'nullable|string',
        ]);

        try {
            if ($validated['type'] === 'inbound') {
                $movement = $this->stockService->addStock(
                    (int) $validated['item_id'],
                    (int) $validated['warehouse_id'],
                    (int) $validated['quantity'],
                    (int) auth()->id(),
                    $validated['note'] ?? null,
                    $validated['reference_type'] ?? null,
                    $validated['reference_id'] ?? null,
                    'inbound'
                );

                return response()->json([
                    'message' => '入庫成功',
                    'data' => $movement->fresh(['warehouse', 'item', 'creator']),
                ], 201);
            }

            if ($validated['type'] === 'outbound') {
                $movement = $this->stockService->deductStock(
                    (int) $validated['item_id'],
                    (int) $validated['quantity'],
                    $validated['reference_type'] ?? 'manual',
                    (int) ($validated['reference_id'] ?? 0),
                    (int) auth()->id(),
                    $validated['note'] ?? '手動出庫',
                    (int) $validated['warehouse_id']
                );

                return response()->json([
                    'message' => '出庫成功',
                    'data' => $movement->fresh(['warehouse', 'item', 'creator']),
                ], 201);
            }

            $result = $this->stockService->transferStock(
                (int) $validated['item_id'],
                (int) $validated['source_warehouse_id'],
                (int) $validated['target_warehouse_id'],
                (int) $validated['quantity'],
                (int) auth()->id(),
                $validated['note'] ?? null,
                $validated['reference_type'] ?? null,
                $validated['reference_id'] ?? null
            );

            return response()->json([
                'message' => '庫存調撥成功',
                'data' => $result,
            ], 201);
        } catch (InvalidArgumentException|RuntimeException $e) {
            return response()->json([
                'message' => '庫存異動失敗',
                'error' => $e->getMessage(),
            ], 422);
        } catch (Throwable $e) {
            return response()->json([
                'message' => '庫存異動失敗',
                'error' => '系統發生未預期錯誤',
            ], 500);
        }
    }

    public function show(StockMovement $stockMovement)
    {
        return response()->json([
            'data' => $stockMovement->load([
                'warehouse:id,name,code,location,status',
                'item:id,name,description,unit,price,quantity,category,status',
                'creator:id,name,email',
            ]),
        ]);
    }
}
