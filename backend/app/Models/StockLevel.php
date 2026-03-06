<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_id',
        'item_id',
        'quantity',
        'reserved',
        'min_level',
        'max_level',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'reserved' => 'integer',
        'min_level' => 'integer',
        'max_level' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'available_quantity',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function getAvailableQuantityAttribute(): int
    {
        return (int)$this->quantity - (int)$this->reserved;
    }
}
