<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity; // Added LogsActivity trait

class Warehouse extends Model
{
    use HasFactory, SoftDeletes, LogsActivity; // Added LogsActivity trait

    protected $fillable = [
        'name',
        'code',
        'location',
        'status',
        'created_by',
        'updated_by',
    ];
}
