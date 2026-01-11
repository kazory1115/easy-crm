<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait LogsActivity
{
    /**
     * Boot the trait
     */
    protected static function bootLogsActivity()
    {
        static::created(function ($model) {
            $model->logActivity('created');
        });

        static::updated(function ($model) {
            $model->logActivity('updated');
        });

        static::deleted(function ($model) {
            $model->logActivity('deleted');
        });

        if (method_exists(static::class, 'restored')) {
            static::restored(function ($model) {
                $model->logActivity('restored');
            });
        }
    }

    /**
     * 記錄活動
     */
    public function logActivity($action, $description = null, $oldData = null, $newData = null)
    {
        $logModelClass = $this->getLogModelClass();

        if (!$logModelClass || !class_exists($logModelClass)) {
            return;
        }

        // 取得 IP 位址和 User Agent
        $request = request();
        $ipAddress = $request ? $request->ip() : null;
        $userAgent = $request ? $request->userAgent() : null;

        // 取得當前用戶
        $userId = auth()->id();

        // 準備資料
        $logData = [
            $this->getForeignKey() => $this->id,
            'user_id' => $userId,
            'action' => $action,
            'description' => $description ?? $this->getDefaultDescription($action),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ];

        // 如果是更新，記錄變更前後的資料
        if ($action === 'updated' && method_exists($this, 'getOriginal')) {
            $changes = $this->getChanges();
            $original = collect($this->getOriginal())
                ->only(array_keys($changes))
                ->toArray();

            $logData['old_data'] = $original;
            $logData['new_data'] = $changes;
        } else {
            $logData['old_data'] = $oldData;
            $logData['new_data'] = $newData ?? $this->attributesToArray();
        }

        // 建立紀錄
        $logModelClass::create($logData);

        // 同時記錄到全域活動紀錄
        $this->logToActivityLog($action, $logData);
    }

    /**
     * 記錄到全域活動紀錄
     */
    protected function logToActivityLog($action, $logData)
    {
        \App\Models\ActivityLog::create([
            'user_id' => $logData['user_id'],
            'log_name' => $this->getLogName(),
            'subject_type' => get_class($this),
            'subject_id' => $this->id,
            'causer_type' => 'App\Models\User',
            'causer_id' => $logData['user_id'],
            'event' => $action,
            'description' => $logData['description'],
            'properties' => [
                'old' => $logData['old_data'] ?? null,
                'attributes' => $logData['new_data'] ?? null,
            ],
            'ip_address' => $logData['ip_address'],
            'user_agent' => $logData['user_agent'],
        ]);
    }

    /**
     * 取得 Log Model 類別名稱
     */
    protected function getLogModelClass()
    {
        $modelName = class_basename($this);
        return "App\\Models\\{$modelName}Log";
    }

    /**
     * 取得外鍵名稱
     */
    public function getForeignKey()
    {
        return Str::snake(class_basename($this)) . '_id';
    }

    /**
     * 取得 Log 名稱
     */
    protected function getLogName()
    {
        return Str::snake(class_basename($this));
    }

    /**
     * 取得預設描述
     */
    protected function getDefaultDescription($action)
    {
        $modelName = class_basename($this);
        $actionMap = [
            'created' => '建立',
            'updated' => '更新',
            'deleted' => '刪除',
            'restored' => '恢復',
        ];

        $actionText = $actionMap[$action] ?? $action;
        return "{$actionText} {$modelName}";
    }

    /**
     * 取得操作紀錄
     */
    public function logs()
    {
        $logModelClass = $this->getLogModelClass();

        if (!class_exists($logModelClass)) {
            return collect();
        }

        return $this->hasMany($logModelClass);
    }
}
