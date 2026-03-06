<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity; // Added LogsActivity trait

class Opportunity extends Model
{
    use HasFactory, SoftDeletes, LogsActivity; // Added LogsActivity trait

    protected $fillable = [
        'customer_id',
        'name',
        'stage',
        'amount',
        'expected_close_date',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'expected_close_date' => 'date',
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * 關聯：客戶
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
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
     * 關聯：商機日誌
     */
    public function logs()
    {
        return $this->hasMany(OpportunityLog::class)->latest();
    }
}
