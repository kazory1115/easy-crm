<?php

use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerActivityController;
use App\Http\Controllers\Api\CustomerContactController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OpportunityController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\QuoteController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\StockAdjustmentController;
use App\Http\Controllers\Api\StockLevelController;
use App\Http\Controllers\Api\StockMovementController;
use App\Http\Controllers\Api\TemplateController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::get('/ping', function () {
    return response()->json(['message' => 'pong']);
});

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
    });

    Route::prefix('quotes')->group(function () {
        Route::get('/stats', [QuoteController::class, 'stats'])->middleware('permission:quote.view');
        Route::post('/batch-delete', [QuoteController::class, 'batchDelete'])->middleware('permission:quote.delete');
        Route::post('/batch-export', [QuoteController::class, 'batchExport'])->middleware('permission:quote.view');
        Route::get('/', [QuoteController::class, 'index'])->middleware('permission:quote.view');
        Route::post('/', [QuoteController::class, 'store'])->middleware('permission:quote.create');
        Route::get('/{id}', [QuoteController::class, 'show'])->middleware('permission:quote.view');
        Route::put('/{id}', [QuoteController::class, 'update'])->middleware('permission:quote.edit');
        Route::delete('/{id}', [QuoteController::class, 'destroy'])->middleware('permission:quote.delete');
        Route::patch('/{id}/status', [QuoteController::class, 'updateStatus'])->middleware('permission:quote.edit');
        Route::post('/{id}/send', [QuoteController::class, 'send'])->middleware('permission:quote.edit');
        Route::get('/{id}/pdf', [QuoteController::class, 'exportPdf'])->middleware('permission:quote.view');
        Route::get('/{id}/excel', [QuoteController::class, 'exportExcel'])->middleware('permission:quote.view');
        Route::post('/{id}/convert-to-order', [QuoteController::class, 'convertToOrder'])->middleware('permission:order.create');
    });

    Route::prefix('quote-items')->group(function () {
        Route::post('/batch-delete', [ItemController::class, 'batchDelete'])->middleware('permission:quote.item.manage');
        Route::get('/', [ItemController::class, 'index'])->middleware('permission:quote.item.manage');
        Route::post('/', [ItemController::class, 'store'])->middleware('permission:quote.item.manage');
        Route::get('/{id}', [ItemController::class, 'show'])->middleware('permission:quote.item.manage');
        Route::put('/{id}', [ItemController::class, 'update'])->middleware('permission:quote.item.manage');
        Route::delete('/{id}', [ItemController::class, 'destroy'])->middleware('permission:quote.item.manage');
    });

    Route::prefix('templates')->group(function () {
        Route::get('/', [TemplateController::class, 'index'])->middleware('permission:quote.template.manage');
        Route::post('/', [TemplateController::class, 'store'])->middleware('permission:quote.template.manage');
        Route::get('/{id}', [TemplateController::class, 'show'])->middleware('permission:quote.template.manage');
        Route::put('/{id}', [TemplateController::class, 'update'])->middleware('permission:quote.template.manage');
        Route::delete('/{id}', [TemplateController::class, 'destroy'])->middleware('permission:quote.template.manage');
    });

    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->middleware('permission:order.view');
        Route::post('/', [OrderController::class, 'store'])->middleware('permission:order.create');
        Route::get('/{id}', [OrderController::class, 'show'])->middleware('permission:order.view');
        Route::put('/{id}', [OrderController::class, 'update'])->middleware('permission:order.edit');
        Route::delete('/{id}', [OrderController::class, 'destroy'])->middleware('permission:order.delete');
    });

    Route::prefix('customers')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->middleware('permission:crm.view');
        Route::post('/', [CustomerController::class, 'store'])->middleware('permission:crm.create');
        Route::get('/{customer}', [CustomerController::class, 'show'])->middleware('permission:crm.view');
        Route::put('/{customer}', [CustomerController::class, 'update'])->middleware('permission:crm.edit');
        Route::delete('/{customer}', [CustomerController::class, 'destroy'])->middleware('permission:crm.delete');

        Route::get('/{customer}/contacts', [CustomerContactController::class, 'index'])->middleware('permission:crm.view');
        Route::post('/{customer}/contacts', [CustomerContactController::class, 'store'])->middleware('permission:crm.create');
        Route::get('/{customer}/contacts/{contact}', [CustomerContactController::class, 'show'])->middleware('permission:crm.view');
        Route::put('/{customer}/contacts/{contact}', [CustomerContactController::class, 'update'])->middleware('permission:crm.edit');
        Route::delete('/{customer}/contacts/{contact}', [CustomerContactController::class, 'destroy'])->middleware('permission:crm.delete');

        Route::get('/{customer}/activities', [CustomerActivityController::class, 'index'])->middleware('permission:crm.view');
        Route::post('/{customer}/activities', [CustomerActivityController::class, 'store'])->middleware('permission:crm.create');
        Route::get('/{customer}/activities/{activity}', [CustomerActivityController::class, 'show'])->middleware('permission:crm.view');
    });

    Route::prefix('opportunities')->group(function () {
        Route::get('/', [OpportunityController::class, 'index'])->middleware('permission:crm.view');
        Route::post('/', [OpportunityController::class, 'store'])->middleware('permission:crm.create');
        Route::patch('/{opportunity}/status', [OpportunityController::class, 'updateStatus'])->middleware('permission:crm.edit');
        Route::get('/{opportunity}', [OpportunityController::class, 'show'])->middleware('permission:crm.view');
        Route::put('/{opportunity}', [OpportunityController::class, 'update'])->middleware('permission:crm.edit');
        Route::delete('/{opportunity}', [OpportunityController::class, 'destroy'])->middleware('permission:crm.delete');
    });

    Route::prefix('warehouses')->group(function () {
        Route::get('/', [WarehouseController::class, 'index'])->middleware('permission:inventory.view');
        Route::post('/', [WarehouseController::class, 'store'])->middleware('permission:inventory.create');
        Route::get('/{warehouse}', [WarehouseController::class, 'show'])->middleware('permission:inventory.view');
        Route::put('/{warehouse}', [WarehouseController::class, 'update'])->middleware('permission:inventory.edit');
        Route::delete('/{warehouse}', [WarehouseController::class, 'destroy'])->middleware('permission:inventory.delete');
    });

    Route::prefix('stock-levels')->group(function () {
        Route::get('/', [StockLevelController::class, 'index'])->middleware('permission:inventory.view');
        Route::get('/{stockLevel}', [StockLevelController::class, 'show'])->middleware('permission:inventory.view');
    });

    Route::prefix('stock-movements')->group(function () {
        Route::get('/', [StockMovementController::class, 'index'])->middleware('permission:inventory.view');
        Route::post('/', [StockMovementController::class, 'store'])->middleware('permission:inventory.edit');
        Route::get('/{stockMovement}', [StockMovementController::class, 'show'])->middleware('permission:inventory.view');
    });

    Route::prefix('stock-adjustments')->group(function () {
        Route::get('/', [StockAdjustmentController::class, 'index'])->middleware('permission:inventory.view');
        Route::post('/', [StockAdjustmentController::class, 'store'])->middleware('permission:inventory.edit');
        Route::get('/{stockAdjustment}', [StockAdjustmentController::class, 'show'])->middleware('permission:inventory.view');
    });

    Route::prefix('reports')->group(function () {
        Route::get('/dashboard', [ReportController::class, 'dashboard'])->middleware('permission:report.view');
        Route::get('/exports', [ReportController::class, 'exports'])->middleware('permission:report.view');
        Route::post('/exports', [ReportController::class, 'storeExport'])->middleware('permission:report.export');
    });

    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->middleware('permission:staff.view');
        Route::post('/', [UserController::class, 'store'])->middleware('permission:staff.create');
        Route::get('/stats', [UserController::class, 'stats'])->middleware('permission:staff.view');
        Route::get('/{id}/roles', [UserController::class, 'roles'])->middleware('permission:role.manage');
        Route::put('/{id}/roles', [UserController::class, 'updateRoles'])->middleware('permission:role.manage');
        Route::get('/{id}/permissions', [UserController::class, 'permissions'])->middleware('permission:role.manage');
        Route::put('/{id}/permissions', [UserController::class, 'updatePermissions'])->middleware('permission:role.manage');
        Route::get('/{id}', [UserController::class, 'show'])->middleware('permission:staff.view');
        Route::put('/{id}', [UserController::class, 'update'])->middleware('permission:staff.edit');
        Route::delete('/{id}', [UserController::class, 'destroy'])->middleware('permission:staff.delete');
    });

    Route::prefix('permissions')->middleware('permission:role.manage')->group(function () {
        Route::get('/modules', [PermissionController::class, 'modules']);
    });

    Route::prefix('activity-logs')->group(function () {
        Route::get('/', [ActivityLogController::class, 'index'])->middleware('permission:staff.view');
        Route::get('/my-logs', [ActivityLogController::class, 'myLogs'])->middleware('permission:staff.view');
        Route::get('/stats', [ActivityLogController::class, 'stats'])->middleware('permission:staff.view');
        Route::get('/module/{module}', [ActivityLogController::class, 'moduleLogs'])->middleware('permission:staff.view');
        Route::get('/{id}', [ActivityLogController::class, 'show'])->middleware('permission:staff.view');
    });
});
