<?php

namespace App\Services;

use App\Models\Item;
use App\Models\StockLevel;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Exception;

class StockService
{
    /**
     * 扣減商品庫存，並記錄庫存移動。
     *
     * @param int $itemId 商品ID
     * @param int $quantity 扣減數量
     * @param string $referenceType 參考類型 (例如 Order::class)
     * @param int $referenceId 參考ID
     * @param int $userId 執行操作的使用者ID
     * @param string $description 庫存移動描述
     * @param int|null $warehouseId 指定倉庫ID (如果為null，則從預設倉庫扣減或選取有庫存的倉庫)
     * @throws Exception
     */
    public function deductStock(
        int $itemId,
        int $quantity,
        string $referenceType,
        int $referenceId,
        int $userId,
        string $description = '出庫',
        ?int $warehouseId = null
    ): void {
        DB::transaction(function () use (
            $itemId,
            $quantity,
            $referenceType,
            $referenceId,
            $userId,
            $description,
            $warehouseId
        ) {
            $item = Item::find($itemId);
            if (!$item) {
                throw new Exception("商品 [ID: {$itemId}] 不存在。");
            }

            // 如果沒有指定倉庫，則嘗試從第一個找到的、有足夠庫存的倉庫扣減
            // 更複雜的邏輯可能需要依據業務規則選擇倉庫
            $stockLevelQuery = StockLevel::where('item_id', $itemId);
            if ($warehouseId) {
                $stockLevelQuery->where('warehouse_id', $warehouseId);
            }

            $stockLevel = $stockLevelQuery->first();

            if (!$stockLevel) {
                throw new Exception("商品 [{$item->name}] 庫存級別未找到。");
            }

            if ($stockLevel->quantity < $quantity) {
                throw new Exception("商品 [{$item->name}] 庫存不足。目前庫存: {$stockLevel->quantity}, 需求: {$quantity}。");
            }

            $stockLevel->decrement('quantity', $quantity);

            StockMovement::create([
                'item_id'        => $itemId,
                'warehouse_id'   => $stockLevel->warehouse_id,
                'type'           => 'out', // 出庫
                'quantity'       => $quantity,
                'reference_type' => $referenceType,
                'reference_id'   => $referenceId,
                'description'    => $description,
                'created_by'     => $userId,
            ]);
        });
    }

    // TODO: 可以添加 addStock, transferStock, adjustStock 等方法
}
