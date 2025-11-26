<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Template extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'category',
        'type',
        'status',
        'usage_count',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'usage_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * 關聯：範本欄位
     */
    public function fields()
    {
        return $this->hasMany(TemplateField::class)->orderBy('sort_order');
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
     * 關聯：操作紀錄
     */
    public function logs()
    {
        return $this->hasMany(TemplateLog::class);
    }

    /**
     * Scope: 只取得啟用的範本
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: 依類型篩選
     */
    public function scopeByType($query, $type)
    {
        if (!$type) {
            return $query;
        }

        return $query->where('type', $type);
    }

    /**
     * Scope: 依分類篩選
     */
    public function scopeByCategory($query, $category)
    {
        if (!$category) {
            return $query;
        }

        return $query->where('category', $category);
    }

    /**
     * Scope: 搜尋範本
     */
    public function scopeSearch($query, $keyword)
    {
        if (!$keyword) {
            return $query;
        }

        return $query->where(function ($q) use ($keyword) {
            $q->where('name', 'like', "%{$keyword}%")
                ->orWhere('description', 'like', "%{$keyword}%");
        });
    }

    /**
     * 增加使用次數
     */
    public function incrementUsage()
    {
        $this->increment('usage_count');
    }
}
