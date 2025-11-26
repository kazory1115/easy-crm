<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'quote_id',
        'item_id',
        'sort_order',
        'type',
        'name',
        'description',
        'quantity',
        'unit',
        'price',
        'amount',
        'fields',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'amount' => 'decimal:2',
        'fields' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot method - 自動計算金額
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($quoteItem) {
            $quoteItem->amount = $quoteItem->quantity * $quoteItem->price;
        });

        static::saved(function ($quoteItem) {
            // 更新報價單總金額
            if ($quoteItem->quote) {
                $quoteItem->quote->calculateTotal();
            }
        });

        static::deleted(function ($quoteItem) {
            // 更新報價單總金額
            if ($quoteItem->quote) {
                $quoteItem->quote->calculateTotal();
            }
        });
    }

    /**
     * 關聯：報價單
     */
    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    /**
     * 關聯：項目
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
