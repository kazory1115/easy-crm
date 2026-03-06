<?php

namespace App\Services;

use App\Models\Item;
use App\Models\StockAdjustment;
use App\Models\StockLevel;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class StockService
{
    public function addStock(
        int $itemId,
        int $warehouseId,
        int $quantity,
        int $userId,
        ?string $note = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $movementType = 'inbound'
    ): StockMovement {
        $this->assertPositiveQuantity($quantity);

        return DB::transaction(function () use (
            $itemId,
            $warehouseId,
            $quantity,
            $userId,
            $note,
            $referenceType,
            $referenceId,
            $movementType
        ) {
            $item = $this->getItem($itemId);
            $warehouse = $this->getWarehouse($warehouseId, true);
            $stockLevel = $this->getLockedStockLevel($warehouse->id, $item->id);

            $stockLevel->quantity += $quantity;
            $stockLevel->save();

            $movement = $this->createMovement([
                'warehouse_id' => $warehouse->id,
                'item_id' => $item->id,
                'type' => $movementType ?: 'inbound',
                'quantity' => $quantity,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'note' => $note ?: '手動入庫',
                'occurred_at' => now(),
                'created_by' => $userId,
            ]);

            $this->syncItemQuantity($item->id, $userId);

            return $movement;
        });
    }

    public function deductStock(
        int $itemId,
        int $quantity,
        string $referenceType,
        int $referenceId,
        int $userId,
        string $description = '出庫',
        ?int $warehouseId = null
    ): StockMovement {
        $this->assertPositiveQuantity($quantity);

        return DB::transaction(function () use (
            $itemId,
            $quantity,
            $referenceType,
            $referenceId,
            $userId,
            $description,
            $warehouseId
        ) {
            $item = $this->getItem($itemId);
            $stockLevel = $this->resolveStockLevelForOutbound($item->id, $quantity, $warehouseId);

            $stockLevel->quantity -= $quantity;
            $stockLevel->save();

            $movement = $this->createMovement([
                'warehouse_id' => $stockLevel->warehouse_id,
                'item_id' => $item->id,
                'type' => 'outbound',
                'quantity' => -$quantity,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'note' => $description,
                'occurred_at' => now(),
                'created_by' => $userId,
            ]);

            $this->syncItemQuantity($item->id, $userId);

            return $movement;
        });
    }

    public function transferStock(
        int $itemId,
        int $sourceWarehouseId,
        int $targetWarehouseId,
        int $quantity,
        int $userId,
        ?string $note = null,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): array {
        $this->assertPositiveQuantity($quantity);

        if ($sourceWarehouseId === $targetWarehouseId) {
            throw new InvalidArgumentException('來源倉庫與目標倉庫不可相同。');
        }

        return DB::transaction(function () use (
            $itemId,
            $sourceWarehouseId,
            $targetWarehouseId,
            $quantity,
            $userId,
            $note,
            $referenceType,
            $referenceId
        ) {
            $item = $this->getItem($itemId);
            $sourceWarehouse = $this->getWarehouse($sourceWarehouseId, true);
            $targetWarehouse = $this->getWarehouse($targetWarehouseId, true);
            $sourceStockLevel = $this->resolveStockLevelForOutbound($item->id, $quantity, $sourceWarehouse->id);
            $targetStockLevel = $this->getLockedStockLevel($targetWarehouse->id, $item->id);

            $sourceStockLevel->quantity -= $quantity;
            $sourceStockLevel->save();

            $targetStockLevel->quantity += $quantity;
            $targetStockLevel->save();

            $baseNote = $note ?: sprintf(
                '庫存調撥：%s -> %s',
                $sourceWarehouse->name,
                $targetWarehouse->name
            );

            $outboundMovement = $this->createMovement([
                'warehouse_id' => $sourceWarehouse->id,
                'item_id' => $item->id,
                'type' => 'transfer',
                'quantity' => -$quantity,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'note' => $baseNote,
                'occurred_at' => now(),
                'created_by' => $userId,
            ]);

            $inboundMovement = $this->createMovement([
                'warehouse_id' => $targetWarehouse->id,
                'item_id' => $item->id,
                'type' => 'transfer',
                'quantity' => $quantity,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'note' => $baseNote,
                'occurred_at' => now(),
                'created_by' => $userId,
            ]);

            $this->syncItemQuantity($item->id, $userId);

            return [
                'item' => $item->fresh(),
                'source_stock_level' => $sourceStockLevel->fresh(['warehouse', 'item']),
                'target_stock_level' => $targetStockLevel->fresh(['warehouse', 'item']),
                'movements' => [
                    $outboundMovement->fresh(['warehouse', 'item', 'creator']),
                    $inboundMovement->fresh(['warehouse', 'item', 'creator']),
                ],
            ];
        });
    }

    public function adjustStock(
        int $itemId,
        int $warehouseId,
        int $afterQuantity,
        int $userId,
        ?string $reason = null,
        ?string $note = null
    ): array {
        if ($afterQuantity < 0) {
            throw new InvalidArgumentException('調整後庫存不可小於 0。');
        }

        return DB::transaction(function () use (
            $itemId,
            $warehouseId,
            $afterQuantity,
            $userId,
            $reason,
            $note
        ) {
            $item = $this->getItem($itemId);
            $warehouse = $this->getWarehouse($warehouseId, true);
            $stockLevel = $this->getLockedStockLevel($warehouse->id, $item->id);

            $beforeQuantity = (int) $stockLevel->quantity;
            $difference = $afterQuantity - $beforeQuantity;

            $stockLevel->quantity = $afterQuantity;
            $stockLevel->save();

            $adjustment = StockAdjustment::create([
                'warehouse_id' => $warehouse->id,
                'item_id' => $item->id,
                'before_qty' => $beforeQuantity,
                'after_qty' => $afterQuantity,
                'reason' => $reason ?: 'manual_adjustment',
                'note' => $note,
                'created_by' => $userId,
            ]);

            $movement = $this->createMovement([
                'warehouse_id' => $warehouse->id,
                'item_id' => $item->id,
                'type' => 'adjustment',
                'quantity' => $difference,
                'reference_type' => StockAdjustment::class,
                'reference_id' => $adjustment->id,
                'note' => $note ?: '手動庫存調整',
                'occurred_at' => now(),
                'created_by' => $userId,
            ]);

            $this->syncItemQuantity($item->id, $userId);

            return [
                'adjustment' => $adjustment->fresh(['warehouse', 'item', 'creator']),
                'movement' => $movement->fresh(['warehouse', 'item', 'creator']),
                'stock_level' => $stockLevel->fresh(['warehouse', 'item']),
            ];
        });
    }

    private function resolveStockLevelForOutbound(
        int $itemId,
        int $quantity,
        ?int $warehouseId = null
    ): StockLevel {
        if ($warehouseId !== null) {
            $warehouse = $this->getWarehouse($warehouseId, true);
            $stockLevel = $this->getLockedStockLevel($warehouse->id, $itemId);
            $availableQuantity = (int) $stockLevel->quantity - (int) $stockLevel->reserved;

            if ($availableQuantity < $quantity) {
                throw new RuntimeException(sprintf(
                    '商品庫存不足。倉庫 [%s] 可用庫存 %d，需求 %d。',
                    $warehouse->name,
                    $availableQuantity,
                    $quantity
                ));
            }

            return $stockLevel;
        }

        $stockLevels = StockLevel::query()
            ->where('item_id', $itemId)
            ->whereHas('warehouse', fn (Builder $query) => $query->where('status', 'active'))
            ->orderByDesc('quantity')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        foreach ($stockLevels as $stockLevel) {
            $availableQuantity = (int) $stockLevel->quantity - (int) $stockLevel->reserved;
            if ($availableQuantity >= $quantity) {
                return $stockLevel;
            }
        }

        throw new RuntimeException('商品庫存不足，找不到可出庫的倉庫。');
    }

    private function getItem(int $itemId): Item
    {
        $item = Item::query()->find($itemId);
        if (!$item) {
            throw new InvalidArgumentException("商品 [ID: {$itemId}] 不存在。");
        }

        return $item;
    }

    private function getWarehouse(int $warehouseId, bool $requireActive = false): Warehouse
    {
        $warehouse = Warehouse::query()->find($warehouseId);
        if (!$warehouse) {
            throw new InvalidArgumentException("倉庫 [ID: {$warehouseId}] 不存在。");
        }

        if ($requireActive && $warehouse->status !== 'active') {
            throw new InvalidArgumentException("倉庫 [{$warehouse->name}] 已停用，無法進行庫存異動。");
        }

        return $warehouse;
    }

    private function getLockedStockLevel(int $warehouseId, int $itemId): StockLevel
    {
        $stockLevel = StockLevel::query()
            ->where('warehouse_id', $warehouseId)
            ->where('item_id', $itemId)
            ->lockForUpdate()
            ->first();

        if ($stockLevel) {
            return $stockLevel;
        }

        StockLevel::create([
            'warehouse_id' => $warehouseId,
            'item_id' => $itemId,
            'quantity' => 0,
            'reserved' => 0,
            'min_level' => 0,
            'max_level' => null,
        ]);

        return StockLevel::query()
            ->where('warehouse_id', $warehouseId)
            ->where('item_id', $itemId)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function createMovement(array $payload): StockMovement
    {
        return StockMovement::create([
            'warehouse_id' => $payload['warehouse_id'],
            'item_id' => $payload['item_id'],
            'type' => $payload['type'],
            'quantity' => $payload['quantity'],
            'reference_type' => $payload['reference_type'] ?? null,
            'reference_id' => $payload['reference_id'] ?? null,
            'note' => $payload['note'] ?? null,
            'occurred_at' => $payload['occurred_at'] ?? now(),
            'created_by' => $payload['created_by'] ?? null,
        ]);
    }

    private function syncItemQuantity(int $itemId, int $userId): void
    {
        $totalQuantity = (int) StockLevel::query()
            ->where('item_id', $itemId)
            ->sum('quantity');

        Item::query()
            ->whereKey($itemId)
            ->update([
                'quantity' => $totalQuantity,
                'updated_by' => $userId,
                'updated_at' => now(),
            ]);
    }

    private function assertPositiveQuantity(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('數量必須大於 0。');
        }
    }
}
