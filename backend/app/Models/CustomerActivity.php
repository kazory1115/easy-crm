<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'user_id',
        'type',
        'subject',
        'content',
        'activity_at',
        'next_action_at',
    ];

    protected $casts = [
        'activity_at' => 'datetime',
        'next_action_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 關聯：所屬客戶
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * 關聯：負責使用者
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
