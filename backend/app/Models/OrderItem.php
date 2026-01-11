<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'item_id',
        'sort_order',
        'type',
        'name',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'subtotal',
        'fields',
        'notes',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'fields' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 關聯：訂單
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * 關聯：原始項目 (如果有的話)
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}