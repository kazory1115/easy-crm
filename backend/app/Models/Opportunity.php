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
    ];
}
