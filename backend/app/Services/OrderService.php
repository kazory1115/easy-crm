<?php

namespace App\Services;

use App\Models\Quote;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * 將指定的報價單轉換為訂單。
     *
     * @param Quote $quote 要轉換的報價單實例
     * @param int $userId 執行轉換的使用者ID
     * @return Order
     * @throws \Exception
     */
    public function createOrderFromQuote(Quote $quote, int $userId): Order
    {
        return $quote->convertToOrder($userId);
    }

    /**
     * 建立新的訂單及其品項。
     * (此方法待未來實作)
     *
     * @param array $orderData
     * @param array $itemsData
     * @param int $userId
     * @return Order
     */
    public function createOrder(array $orderData, array $itemsData, int $userId): Order
    {
        // TODO: 實作直接建立訂單的邏輯，而不是從報價單轉換
        throw new \Exception('直接建立訂單功能待實作。');
    }

    /**
     * 更新訂單及其品項。
     * (此方法待未來實作)
     *
     * @param Order $order
     * @param array $orderData
     * @param array $itemsData
     * @param int $userId
     * @return Order
     */
    public function updateOrder(Order $order, array $orderData, array $itemsData, int $userId): Order
    {
        // TODO: 實作更新訂單的邏輯
        throw new \Exception('更新訂單功能待實作。');
    }
}
