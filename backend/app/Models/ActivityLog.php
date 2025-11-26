<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'log_name',
        'subject_type',
        'subject_id',
        'causer_type',
        'causer_id',
        'event',
        'description',
        'properties',
        'ip_address',
        'user_agent',
        'batch_uuid',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * 關聯：操作者
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 關聯：主體（多型關聯）
     */
    public function subject()
    {
        return $this->morphTo();
    }

    /**
     * 關聯：觸發者（多型關聯）
     */
    public function causer()
    {
        return $this->morphTo();
    }

    /**
     * Scope: 依事件類型篩選
     */
    public function scopeByEvent($query, $event)
    {
        if (!$event) {
            return $query;
        }

        return $query->where('event', $event);
    }

    /**
     * Scope: 依日誌名稱篩選
     */
    public function scopeByLogName($query, $logName)
    {
        if (!$logName) {
            return $query;
        }

        return $query->where('log_name', $logName);
    }

    /**
     * Scope: 依主體類型篩選
     */
    public function scopeForSubject($query, $subjectType, $subjectId = null)
    {
        $query->where('subject_type', $subjectType);

        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }

        return $query;
    }
}
