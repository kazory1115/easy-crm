<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemLog extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'item_id',
        'user_id',
        'action',
        'old_data',
        'new_data',
        'description',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * 關聯：項目
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * 關聯：操作者
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
