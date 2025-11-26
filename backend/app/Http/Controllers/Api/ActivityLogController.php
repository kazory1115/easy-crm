<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * 取得操作紀錄列表
     */
    public function index(Request $request)
    {
        $query = ActivityLog::with(['user']);

        // 依用戶篩選
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // 依事件類型篩選
        if ($request->has('event')) {
            $query->byEvent($request->event);
        }

        // 依日誌名稱篩選
        if ($request->has('log_name')) {
            $query->byLogName($request->log_name);
        }

        // 依主體類型篩選
        if ($request->has('subject_type')) {
            $query->forSubject($request->subject_type, $request->subject_id ?? null);
        }

        // 日期範圍篩選
        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // 排序
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // 分頁
        $perPage = $request->get('per_page', 20);
        $logs = $query->paginate($perPage);

        return response()->json($logs);
    }

    /**
     * 取得單一操作紀錄
     */
    public function show($id)
    {
        $log = ActivityLog::with(['user', 'subject', 'causer'])->findOrFail($id);
        return response()->json(['data' => $log]);
    }

    /**
     * 取得當前用戶的操作紀錄
     */
    public function myLogs(Request $request)
    {
        $query = ActivityLog::with(['subject'])
            ->where('user_id', auth()->id());

        // 日期範圍篩選
        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // 排序
        $query->orderBy('created_at', 'desc');

        // 分頁
        $perPage = $request->get('per_page', 20);
        $logs = $query->paginate($perPage);

        return response()->json($logs);
    }

    /**
     * 取得特定模組的操作紀錄
     */
    public function moduleLogss(Request $request, $module)
    {
        // 模組對應的 Model 類別
        $moduleMap = [
            'quote' => 'App\Models\Quote',
            'template' => 'App\Models\Template',
            'customer' => 'App\Models\Customer',
            'item' => 'App\Models\Item',
            'user' => 'App\Models\User',
        ];

        if (!isset($moduleMap[$module])) {
            return response()->json([
                'message' => '無效的模組名稱',
            ], 400);
        }

        $query = ActivityLog::with(['user'])
            ->forSubject($moduleMap[$module]);

        // 如果有指定 ID，只取得該筆資料的紀錄
        if ($request->has('id')) {
            $query->where('subject_id', $request->id);
        }

        // 排序
        $query->orderBy('created_at', 'desc');

        // 分頁
        $perPage = $request->get('per_page', 20);
        $logs = $query->paginate($perPage);

        return response()->json($logs);
    }

    /**
     * 取得操作紀錄統計
     */
    public function stats(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = ActivityLog::query();

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $stats = [
            'total' => $query->count(),
            'by_event' => (clone $query)->groupBy('event')
                ->selectRaw('event, count(*) as count')
                ->pluck('count', 'event'),
            'by_module' => (clone $query)->groupBy('log_name')
                ->selectRaw('log_name, count(*) as count')
                ->pluck('count', 'log_name'),
            'recent_users' => (clone $query)->with('user')
                ->select('user_id')
                ->groupBy('user_id')
                ->selectRaw('user_id, count(*) as count')
                ->orderBy('count', 'desc')
                ->take(10)
                ->get(),
        ];

        return response()->json(['data' => $stats]);
    }
}
