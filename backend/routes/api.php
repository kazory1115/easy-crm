<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\QuoteController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\TemplateController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\OrderController; // Added OrderController

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| 這裡是 API 專用路由，預設會加上 /api 前綴。
| 你可以定義 GET、POST、PUT、DELETE 等請求。
|
*/

// 測試 API
Route::get('/ping', function () {
    return response()->json(['message' => 'pong']);
});

// === 認證相關路由（不需要驗證） ===
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// === 需要認證的路由 ===
Route::middleware('auth:sanctum')->group(function () {
    // 認證相關
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
    });

    // ==========================================
    // 報價單相關路由
    // ==========================================
    Route::prefix('quotes')->group(function () {
        // 統計資料
        Route::get('/stats', [QuoteController::class, 'stats']);

        // 批次操作
        Route::post('/batch-delete', [QuoteController::class, 'batchDelete']);
        Route::post('/batch-export', [QuoteController::class, 'batchExport']);

        // CRUD
        Route::get('/', [QuoteController::class, 'index']);
        Route::post('/', [QuoteController::class, 'store']);
        Route::get('/{id}', [QuoteController::class, 'show']);
        Route::put('/{id}', [QuoteController::class, 'update']);
        Route::delete('/{id}', [QuoteController::class, 'destroy']);

        // 狀態管理
        Route::patch('/{id}/status', [QuoteController::class, 'updateStatus']);
        Route::post('/{id}/send', [QuoteController::class, 'send']);

        // 匯出
        Route::get('/{id}/pdf', [QuoteController::class, 'exportPdf']);
        Route::get('/{id}/excel', [QuoteController::class, 'exportExcel']);

        // 轉換為訂單
        Route::post('/{id}/convert-to-order', [QuoteController::class, 'convertToOrder']);
    });

    // ==========================================
    // 一般項目相關路由
    // ==========================================
    Route::prefix('quote-items')->group(function () {
        // 批次操作
        Route::post('/batch-delete', [ItemController::class, 'batchDelete']);

        // CRUD
        Route::get('/', [ItemController::class, 'index']);
        Route::post('/', [ItemController::class, 'store']);
        Route::get('/{id}', [ItemController::class, 'show']);
        Route::put('/{id}', [ItemController::class, 'update']);
        Route::delete('/{id}', [ItemController::class, 'destroy']);
    });

    // ==========================================
    // 範本相關路由
    // ==========================================
    Route::prefix('templates')->group(function () {
        Route::get('/', [TemplateController::class, 'index']);
        Route::post('/', [TemplateController::class, 'store']);
        Route::get('/{id}', [TemplateController::class, 'show']);
        Route::put('/{id}', [TemplateController::class, 'update']);
        Route::delete('/{id}', [TemplateController::class, 'destroy']);
    });

    // ==========================================
    // 訂單相關路由
    // ==========================================
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index']);
        Route::post('/', [OrderController::class, 'store']);
        Route::get('/{id}', [OrderController::class, 'show']);
        Route::put('/{id}', [OrderController::class, 'update']);
        Route::delete('/{id}', [OrderController::class, 'destroy']);
    });

    // ==========================================
    // 用戶/員工管理路由
    // ==========================================
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store']);
        Route::get('/stats', [UserController::class, 'stats']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::put('/{id}', [UserController::class, 'update']);
        Route::delete('/{id}', [UserController::class, 'destroy']);
    });

    // ==========================================
    // 操作紀錄路由
    // ==========================================
    Route::prefix('activity-logs')->group(function () {
        Route::get('/', [ActivityLogController::class, 'index']);
        Route::get('/my-logs', [ActivityLogController::class, 'myLogs']);
        Route::get('/stats', [ActivityLogController::class, 'stats']);
        Route::get('/module/{module}', [ActivityLogController::class, 'moduleLogs']);
        Route::get('/{id}', [ActivityLogController::class, 'show']);
    });
});
