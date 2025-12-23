<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportExport extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'report_key',
        'format',
        'filters',
        'file_path',
        'status',
        'completed_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'created_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
}
