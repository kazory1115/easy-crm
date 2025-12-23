<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpportunityLog extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'opportunity_id',
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
}
