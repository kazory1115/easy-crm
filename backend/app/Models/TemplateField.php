<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateField extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'field_key',
        'field_label',
        'field_type',
        'field_value',
        'field_options',
        'sort_order',
        'is_required',
    ];

    protected $casts = [
        'field_options' => 'array',
        'is_required' => 'boolean',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 關聯：範本
     */
    public function template()
    {
        return $this->belongsTo(Template::class);
    }
}
