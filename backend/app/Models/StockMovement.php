<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_id',
        'item_id',
        'type',
        'quantity',
        'reference_type',
        'reference_id',
        'note',
        'occurred_at',
        'created_by',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
    ];
}
