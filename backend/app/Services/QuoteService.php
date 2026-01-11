<?php

namespace App\Services;

use App\Models\Quote;
use App\Models\QuoteItem;
use Illuminate\Support\Facades\DB;

class QuoteService
{
    /**
     * 建立新的報價單及其品項。
     *
     * @param array $quoteData 報價單資料
     * @param array $itemsData 品項資料
     * @param int $userId 建立者 ID
     * @return Quote
     * @throws \Exception
     */
    public function createQuote(array $quoteData, array $itemsData, int $userId): Quote
    {
        return DB::transaction(function () use ($quoteData, $itemsData, $userId) {
            // 建立報價單
            $quote = Quote::create(array_merge($quoteData, [
                'created_by' => $userId,
                'updated_by' => $userId,
            ]));

            // 建立報價單品項
            $quoteItems = [];
            foreach ($itemsData as $itemData) {
                $quoteItems[] = new QuoteItem(array_merge($itemData, [
                    'quote_id' => $quote->id, // 確保關聯到新建立的報價單
                ]));
            }
            $quote->items()->saveMany($quoteItems);

            // 重新計算報價單總價
            $quote->recalculateTotal();

            return $quote;
        });
    }

    /**
     * 更新報價單及其品項。
     *
     * @param Quote $quote 要更新的報價單實例
     * @param array $quoteData 報價單資料
     * @param array $itemsData 品項資料
     * @param int $userId 更新者 ID
     * @return Quote
     * @throws \Exception
     */
    public function updateQuote(Quote $quote, array $quoteData, array $itemsData, int $userId): Quote
    {
        return DB::transaction(function () use ($quote, $quoteData, $itemsData, $userId) {
            // 更新報價單
            $quote->update(array_merge($quoteData, [
                'updated_by' => $userId,
            ]));

            // 同步報價單品項 (先刪除舊的，再建立新的，或根據 ID 更新)
            // 這裡採用簡單的刪除再新建方式，如果需要更精細的更新則需調整
            $quote->items()->delete(); // 刪除所有舊品項
            $quoteItems = [];
            foreach ($itemsData as $itemData) {
                $quoteItems[] = new QuoteItem(array_merge($itemData, [
                    'quote_id' => $quote->id,
                ]));
            }
            $quote->items()->saveMany($quoteItems);

            // 重新計算報價單總價
            $quote->recalculateTotal();

            return $quote;
        });
    }
}
