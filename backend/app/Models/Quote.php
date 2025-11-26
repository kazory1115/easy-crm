<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quote extends Model
{
    use HasFactory, SoftDeletes;

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
     * 關聯：操作紀錄
     */
    public function logs()
    {
        return $this->hasMany(QuoteLog::class);
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
     * 計算總金額
     */
    public function calculateTotal()
    {
        $this->subtotal = $this->items->sum('amount');
        $this->total = $this->subtotal + $this->tax - $this->discount;
        $this->save();
    }
}
