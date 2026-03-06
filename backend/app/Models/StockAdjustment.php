<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'warehouse_id',
        'item_id',
        'before_qty',
        'after_qty',
        'reason',
        'note',
        'created_by',
    ];

    protected $casts = [
        'before_qty' => 'integer',
        'after_qty' => 'integer',
        'created_at' => 'datetime',
    ];

    protected $appends = [
        'difference',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getDifferenceAttribute(): int
    {
        return (int)$this->after_qty - (int)$this->before_qty;
    }
}
