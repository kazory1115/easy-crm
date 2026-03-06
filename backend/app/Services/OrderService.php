<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\OrderItem;
use App\Models\OrderLog;
use App\Models\Quote;
use App\Models\Order;
use DomainException;
use InvalidArgumentException;
use Illuminate\Support\Facades\DB;

class OrderService
{
    private const MAX_DECIMAL_10_2 = 99999999.99;

    private const ALLOWED_STATUS = [
        'pending',
        'confirmed',
        'processing',
        'shipped',
        'completed',
        'cancelled',
    ];

    private const ALLOWED_PAYMENT_STATUS = [
        'unpaid',
        'partially_paid',
        'paid',
        'refunded',
    ];

    /**
     * 將指定的報價單轉換為訂單。
     *
     * @param Quote $quote 要轉換的報價單實例
     * @param int $userId 執行轉換的使用者ID
     * @return Order
     */
    public function createOrderFromQuote(Quote $quote, int $userId): Order
    {
        if ($quote->status !== 'approved') {
            throw new DomainException('只有已核准的報價單才能轉換為訂單。');
        }

        if (Order::withTrashed()->where('quote_id', $quote->id)->exists()) {
            throw new DomainException('此報價單已被轉換為訂單。');
        }

        $quote->loadMissing('items');
        if ($quote->items->isEmpty()) {
            throw new InvalidArgumentException('報價單沒有可轉換的品項。');
        }

        $orderData = [
            'quote_id' => $quote->id,
            'customer_id' => $quote->customer_id,
            'customer_name' => $quote->customer_name,
            'contact_phone' => $quote->contact_phone,
            'contact_email' => $quote->contact_email,
            'project_name' => $quote->project_name,
            'order_date' => now()->toDateString(),
            'due_date' => $quote->valid_until,
            'tax_rate' => $quote->tax_rate ?? 0,
            'discount_amount' => $quote->discount ?? 0,
            'status' => 'confirmed',
            'payment_status' => 'unpaid',
            'notes' => $quote->notes,
        ];

        $itemsData = $quote->items
            ->map(function ($quoteItem) {
                return [
                    'item_id' => $quoteItem->item_id,
                    'sort_order' => $quoteItem->sort_order,
                    'type' => $quoteItem->type,
                    'name' => $quoteItem->name,
                    'description' => $quoteItem->description,
                    'quantity' => $quoteItem->quantity,
                    'unit' => $quoteItem->unit,
                    'unit_price' => $quoteItem->price,
                    'fields' => $quoteItem->fields,
                    'notes' => $quoteItem->notes,
                ];
            })
            ->values()
            ->all();

        return DB::transaction(function () use ($quote, $orderData, $itemsData, $userId) {
            $order = $this->createOrderInternal(
                $orderData,
                $itemsData,
                $userId,
                'created_from_quote',
                '由報價單轉換為訂單'
            );

            $this->deductStockForQuoteItems($quote, $order, $userId);

            $quote->updated_by = $userId;
            $quote->save();

            return $order;
        });
    }

    /**
     * 建立新的訂單及其品項。
     *
     * @param array $orderData
     * @param array $itemsData
     * @param int $userId
     * @return Order
     */
    public function createOrder(array $orderData, array $itemsData, int $userId): Order
    {
        return DB::transaction(function () use ($orderData, $itemsData, $userId) {
            return $this->createOrderInternal(
                $orderData,
                $itemsData,
                $userId,
                'created',
                '建立訂單'
            );
        });
    }

    /**
     * 更新訂單及其品項。
     *
     * @param Order $order
     * @param array $orderData
     * @param array $itemsData
     * @param int $userId
     * @return Order
     */
    public function updateOrder(Order $order, array $orderData, array $itemsData, int $userId): Order
    {
        return DB::transaction(function () use ($order, $orderData, $itemsData, $userId) {
            $order->loadMissing('items');
            $oldSnapshot = $this->snapshotOrder($order);

            $payload = $this->prepareOrderPayload(
                $orderData,
                $userId,
                false,
                $order->status,
                $order->payment_status
            );
            $payload = $this->hydrateCustomerSnapshot($payload);

            $order->fill($payload);
            $order->save();

            if (!empty($itemsData)) {
                $order->items()->delete();
                $this->createOrderItems($order, $itemsData);
            }

            $this->recalculateOrderAmounts($order);
            $order->load(['items', 'customer', 'quote']);

            $this->writeOrderLog(
                $order,
                $userId,
                'updated',
                '更新訂單',
                $oldSnapshot,
                $this->snapshotOrder($order)
            );

            return $order;
        });
    }

    private function createOrderInternal(
        array $orderData,
        array $itemsData,
        int $userId,
        string $logAction,
        string $logDescription
    ): Order {
        if (empty($itemsData)) {
            throw new InvalidArgumentException('訂單至少需要一個品項。');
        }

        $payload = $this->prepareOrderPayload(
            $orderData,
            $userId,
            true,
            $orderData['status'] ?? 'pending',
            $orderData['payment_status'] ?? 'unpaid'
        );
        $payload = $this->hydrateCustomerSnapshot($payload);

        $order = Order::create($payload);
        $this->createOrderItems($order, $itemsData);
        $this->recalculateOrderAmounts($order);
        $order->load(['items', 'customer', 'quote']);

        $this->writeOrderLog(
            $order,
            $userId,
            $logAction,
            $logDescription,
            null,
            $this->snapshotOrder($order)
        );

        return $order;
    }

    private function prepareOrderPayload(
        array $orderData,
        int $userId,
        bool $creating,
        string $defaultStatus,
        string $defaultPaymentStatus
    ): array {
        $payload = [];
        $assignableFields = [
            'quote_id',
            'customer_id',
            'customer_name',
            'contact_phone',
            'contact_email',
            'project_name',
            'order_date',
            'due_date',
            'notes',
            'shipped_at',
            'completed_at',
        ];

        foreach ($assignableFields as $field) {
            if (array_key_exists($field, $orderData)) {
                $payload[$field] = $orderData[$field];
            }
        }

        if ($creating && !array_key_exists('order_date', $payload)) {
            $payload['order_date'] = now()->toDateString();
        }

        if ($creating && empty($payload['customer_id']) && empty($payload['customer_name'])) {
            throw new InvalidArgumentException('建立訂單時必須提供 customer_id 或 customer_name。');
        }

        if (array_key_exists('tax_rate', $orderData) || $creating) {
            $taxRate = (float)($orderData['tax_rate'] ?? 0);
            if ($taxRate < 0 || $taxRate > 1) {
                throw new InvalidArgumentException('tax_rate 必須介於 0 到 1。');
            }
            $payload['tax_rate'] = round($taxRate, 4);
        }

        if (array_key_exists('discount_amount', $orderData) || $creating) {
            $discountAmount = (float)($orderData['discount_amount'] ?? 0);
            if ($discountAmount < 0) {
                throw new InvalidArgumentException('discount_amount 不可小於 0。');
            }
            $this->assertMoneyWithinDecimal10_2($discountAmount, 'discount_amount');
            $payload['discount_amount'] = round($discountAmount, 2);
        }

        if (array_key_exists('status', $orderData) || $creating) {
            $payload['status'] = $this->normalizeStatus(
                $orderData['status'] ?? $defaultStatus,
                self::ALLOWED_STATUS,
                'status'
            );
        }

        if (array_key_exists('payment_status', $orderData) || $creating) {
            $payload['payment_status'] = $this->normalizeStatus(
                $orderData['payment_status'] ?? $defaultPaymentStatus,
                self::ALLOWED_PAYMENT_STATUS,
                'payment_status'
            );
        }

        $payload['updated_by'] = $userId;
        if ($creating) {
            $payload['created_by'] = $userId;
        }

        return $payload;
    }

    private function hydrateCustomerSnapshot(array $payload): array
    {
        if (!array_key_exists('customer_id', $payload) || !$payload['customer_id']) {
            return $payload;
        }

        $customer = Customer::find($payload['customer_id']);
        if (!$customer) {
            throw new InvalidArgumentException('指定的 customer_id 不存在。');
        }

        $payload['customer_name'] = $payload['customer_name'] ?? $customer->name;
        $payload['contact_phone'] = $payload['contact_phone']
            ?? $customer->phone
            ?? $customer->contact_phone
            ?? null;
        $payload['contact_email'] = $payload['contact_email']
            ?? $customer->email
            ?? $customer->contact_email
            ?? null;

        return $payload;
    }

    private function createOrderItems(Order $order, array $itemsData): void
    {
        foreach (array_values($itemsData) as $index => $itemData) {
            $order->items()->create($this->prepareOrderItemPayload($itemData, $index + 1));
        }
    }

    private function prepareOrderItemPayload(array $itemData, int $defaultSortOrder): array
    {
        $name = trim((string)($itemData['name'] ?? ''));
        if ($name === '') {
            throw new InvalidArgumentException('訂單品項 name 不可為空。');
        }

        $quantity = (int)($itemData['quantity'] ?? 0);
        if ($quantity <= 0) {
            throw new InvalidArgumentException('訂單品項 quantity 必須大於 0。');
        }

        $unitPrice = (float)($itemData['unit_price'] ?? 0);
        if ($unitPrice < 0) {
            throw new InvalidArgumentException('訂單品項 unit_price 不可小於 0。');
        }
        $this->assertMoneyWithinDecimal10_2($unitPrice, 'items.unit_price');

        $subtotal = round($quantity * $unitPrice, 2);
        $this->assertMoneyWithinDecimal10_2($subtotal, 'items.subtotal');

        return [
            'item_id' => $itemData['item_id'] ?? null,
            'sort_order' => (int)($itemData['sort_order'] ?? $defaultSortOrder),
            'type' => $itemData['type'] ?? 'input',
            'name' => $name,
            'description' => $itemData['description'] ?? null,
            'quantity' => $quantity,
            'unit' => $itemData['unit'] ?? null,
            'unit_price' => round($unitPrice, 2),
            'subtotal' => $subtotal,
            'fields' => $itemData['fields'] ?? null,
            'notes' => $itemData['notes'] ?? null,
        ];
    }

    private function recalculateOrderAmounts(Order $order): void
    {
        $order->load('items');

        $subtotal = round((float)$order->items->sum(function (OrderItem $item) {
            return (float)$item->quantity * (float)$item->unit_price;
        }), 2);
        $this->assertMoneyWithinDecimal10_2($subtotal, 'subtotal');

        $taxRate = (float)($order->tax_rate ?? 0);
        if ($taxRate < 0 || $taxRate > 1) {
            throw new InvalidArgumentException('tax_rate 必須介於 0 到 1。');
        }
        $taxAmount = round($subtotal * $taxRate, 2);
        $this->assertMoneyWithinDecimal10_2($taxAmount, 'tax_amount');

        $discountAmount = round((float)($order->discount_amount ?? 0), 2);
        if ($discountAmount < 0) {
            throw new InvalidArgumentException('discount_amount 不可小於 0。');
        }
        if ($discountAmount > ($subtotal + $taxAmount)) {
            throw new InvalidArgumentException('discount_amount 不可大於 subtotal + tax_amount。');
        }
        $this->assertMoneyWithinDecimal10_2($discountAmount, 'discount_amount');

        $totalAmount = round($subtotal + $taxAmount - $discountAmount, 2);
        $this->assertMoneyWithinDecimal10_2($totalAmount, 'total_amount');

        $order->fill([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'total_amount' => $totalAmount,
        ]);
        $order->save();
    }

    private function normalizeStatus(string $value, array $allowed, string $fieldName): string
    {
        if (!in_array($value, $allowed, true)) {
            throw new InvalidArgumentException("{$fieldName} 值無效。");
        }

        return $value;
    }

    private function assertMoneyWithinDecimal10_2(float $value, string $fieldName): void
    {
        if (abs($value) > self::MAX_DECIMAL_10_2) {
            throw new InvalidArgumentException("{$fieldName} 超過資料庫可儲存範圍。");
        }
    }

    private function snapshotOrder(Order $order): array
    {
        $order->loadMissing('items');

        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'quote_id' => $order->quote_id,
            'customer_id' => $order->customer_id,
            'customer_name' => $order->customer_name,
            'status' => $order->status,
            'payment_status' => $order->payment_status,
            'subtotal' => (float)$order->subtotal,
            'tax_amount' => (float)$order->tax_amount,
            'discount_amount' => (float)$order->discount_amount,
            'total_amount' => (float)$order->total_amount,
            'tax_rate' => (float)$order->tax_rate,
            'items' => $order->items->map(function (OrderItem $item) {
                return [
                    'id' => $item->id,
                    'item_id' => $item->item_id,
                    'sort_order' => $item->sort_order,
                    'type' => $item->type,
                    'name' => $item->name,
                    'quantity' => (int)$item->quantity,
                    'unit' => $item->unit,
                    'unit_price' => (float)$item->unit_price,
                    'subtotal' => (float)$item->subtotal,
                ];
            })->values()->all(),
        ];
    }

    private function writeOrderLog(
        Order $order,
        int $userId,
        string $action,
        string $description,
        ?array $oldData,
        array $newData
    ): void {
        $request = app()->bound('request') ? request() : null;

        OrderLog::create([
            'order_id' => $order->id,
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'old_data' => $oldData,
            'new_data' => $newData,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }

    private function deductStockForQuoteItems(Quote $quote, Order $order, int $userId): void
    {
        $quote->loadMissing('items');
        $stockService = app(StockService::class);

        foreach ($quote->items as $quoteItem) {
            if (!$quoteItem->item_id) {
                continue;
            }

            $stockService->deductStock(
                (int)$quoteItem->item_id,
                (int)$quoteItem->quantity,
                Order::class,
                $order->id,
                $userId,
                '由報價單轉換為訂單出庫'
            );
        }
    }
}
