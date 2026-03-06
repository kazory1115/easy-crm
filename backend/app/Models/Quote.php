<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity; // Added LogsActivity trait
use App\Services\OrderService;

class Quote extends Model
{
    use HasFactory, SoftDeletes, LogsActivity; // Added LogsActivity trait

    protected $fillable = [
        'quote_number',
        'customer_id',
        'customer_name',
        'contact_phone',
        'contact_email',
        'project_name',
        'quote_date',
        'valid_until',
        'subtotal',
        'tax',
        'discount',
        'total',
        'notes',
        'status',
        'sent_at',
        'approved_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'quote_date' => 'date',
        'valid_until' => 'date',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'sent_at' => 'datetime',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Boot method - 自動生成報價單號
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($quote) {
            if (!$quote->quote_number) {
                $quote->quote_number = self::generateQuoteNumber();
            }
        });
    }

    /**
     * 生成報價單號
     */
    public static function generateQuoteNumber()
    {
        $prefix = 'Q';
        $date = now()->format('Ymd');

        // 查詢今天日期格式的最後一筆報價單（使用 quote_number 比對，不使用 created_at）
        $lastQuote = self::where('quote_number', 'like', $prefix . $date . '%')
            ->orderBy('quote_number', 'desc')
            ->first();

        $sequence = $lastQuote ? (intval(substr($lastQuote->quote_number, -4)) + 1) : 1;

        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * 關聯：客戶
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * 關聯：報價單項目
     */
    public function items()
    {
        return $this->hasMany(QuoteItem::class)->orderBy('sort_order');
    }

    /**
     * 關聯：建立者
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 關聯：更新者
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * 關聯：轉換後的訂單
     */
    public function order()
    {
        return $this->hasOne(Order::class);
    }



    /**
     * Scope: 依狀態篩選
     */
    public function scopeByStatus($query, $status)
    {
        if (!$status) {
            return $query;
        }

        return $query->where('status', $status);
    }

    /**
     * Scope: 搜尋報價單
     */
    public function scopeSearch($query, $keyword)
    {
        if (!$keyword) {
            return $query;
        }

        return $query->where(function ($q) use ($keyword) {
            $q->where('quote_number', 'like', "%{$keyword}%")
                ->orWhere('customer_name', 'like', "%{$keyword}%")
                ->orWhere('project_name', 'like', "%{$keyword}%")
                ->orWhere('contact_phone', 'like', "%{$keyword}%");
        });
    }

    /**
     * Scope: 日期範圍篩選
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        if ($startDate) {
            $query->whereDate('quote_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('quote_date', '<=', $endDate);
        }

        return $query;
    }

    /**
     * 從已有的金額欄位更新最終總計
     */
    public function updateFinalTotalFromPrecalculatedValues()
    {
        $this->total = $this->subtotal + $this->tax - $this->discount;
        $this->save();
    }

    /**
     * 根據報價單品項重新計算所有金額（小計、稅金、總計）
     */
    public function recalculateTotal()
    {
        // 確保 items 關聯已載入
        $this->load('items');

        // 計算小計
        $subtotal = $this->items->sum(function ($item) {
            return $item->quantity * $item->price;
        });

        // 取得稅率，如果 Quote 沒有 tax_rate，則預設為 0
        $taxRate = $this->tax_rate ?? 0;

        // 計算稅金
        $tax = $subtotal * $taxRate;

        // 計算總計 (假設 discount 已經是個定值，或從其他地方設定)
        // 如果 discount 欄位在資料庫中不存在，需要處理預設值或調整邏輯
        $discount = $this->discount ?? 0;
        $total = $subtotal + $tax - $discount;

        // 更新 Quote 模型
        $this->subtotal = round($subtotal, 2);
        $this->tax = round($tax, 2);
        $this->total = round($total, 2);

        $this->save(); // 儲存變更
    }

    /**
     * 將已核准的報價單轉換為訂單
     *
     * @param int $userId 執行轉換的使用者ID
     * @return Order
     * @throws \Exception
     */
    public function convertToOrder(int $userId): Order
    {
        return app(OrderService::class)->createOrderFromQuote($this, $userId);
    }
}
