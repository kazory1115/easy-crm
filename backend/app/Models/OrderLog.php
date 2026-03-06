<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'action',
        'description',
        'old_data',
        'new_data',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 關聯：所屬訂單
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * 關聯：操作者
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
