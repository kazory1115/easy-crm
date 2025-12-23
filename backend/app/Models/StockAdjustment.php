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
        'created_at' => 'datetime',
    ];
}
