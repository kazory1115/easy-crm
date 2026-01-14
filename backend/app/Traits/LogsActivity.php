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

        // --- 核心修正 ---
        // 取得當前用戶 (操作者)
        $causerId = auth()->id();
        // 動態判斷操作者欄位名稱
        // UserLog 資料表使用 'operator_id' 作為操作者欄位，以避免與 'user_id' (被操作者) 衝突
        // 其他 Log 資料表 (如 CustomerLog) 則使用 'user_id' 作為操作者欄位
        $causerColumn = (get_class($this) === 'App\Models\User') ? 'operator_id' : 'user_id';

        // 準備資料
        $logData = [
            $this->getForeignKey() => $this->id, // 被操作物件的外鍵 (例如: user_id, customer_id)
            $causerColumn => $causerId,          // 操作者的外鍵 (例如: operator_id, user_id)
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

        // 建立特定模型的紀錄 (例如 UserLog, CustomerLog)
        $logModelClass::create($logData);

        // 同時記錄到全域活動紀錄，並明確傳遞 causer ID
        $this->logToActivityLog($action, $logData, $causerId);
    }

    /**
     * 記錄到全域活動紀錄
     */
    protected function logToActivityLog($action, $logData, $causerId)
    {
        \App\Models\ActivityLog::create([
            'log_name' => $this->getLogName(),
            'subject_type' => get_class($this),
            'subject_id' => $this->id,
            'causer_type' => $causerId ? 'App\Models\User' : null,
            'causer_id' => $causerId,
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
