<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Quote;
use App\Models\ReportExport;
use App\Models\StockAdjustment;
use App\Models\StockLevel;
use App\Models\StockMovement;
use App\Models\Warehouse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    private const EXPORT_SORTABLE_COLUMNS = [
        'id',
        'report_key',
        'format',
        'status',
        'created_at',
        'completed_at',
    ];

    public function getDashboard(array $filters = []): array
    {
        [$startDate, $endDate, $rangeDays] = $this->resolveDateRange($filters);

        return [
            'generated_at' => now()->toISOString(),
            'filters' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'range_days' => $rangeDays,
            ],
            'quote' => $this->buildQuoteStats($startDate, $endDate),
            'order' => $this->buildOrderStats($startDate, $endDate),
            'inventory' => $this->buildInventoryStats($startDate, $endDate),
            'exports' => $this->buildExportStats($startDate, $endDate),
        ];
    }

    public function paginateExportRecords(array $filters = []): LengthAwarePaginator
    {
        $query = ReportExport::query()->with(['user:id,name,email']);

        if (!empty($filters['search'])) {
            $keyword = trim((string) $filters['search']);
            $query->where(function ($builder) use ($keyword) {
                $builder->where('report_key', 'like', "%{$keyword}%")
                    ->orWhere('file_path', 'like', "%{$keyword}%")
                    ->orWhereHas('user', function ($userQuery) use ($keyword) {
                        $userQuery->where('name', 'like', "%{$keyword}%")
                            ->orWhere('email', 'like', "%{$keyword}%");
                    });
            });
        }

        if (!empty($filters['report_key'])) {
            $query->where('report_key', $filters['report_key']);
        }

        if (!empty($filters['format'])) {
            $query->where('format', $filters['format']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        $sortBy = in_array($filters['sort_by'] ?? 'created_at', self::EXPORT_SORTABLE_COLUMNS, true)
            ? $filters['sort_by']
            : 'created_at';
        $sortOrder = strtolower((string) ($filters['sort_order'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';
        $perPage = max(1, min((int) ($filters['per_page'] ?? 15), 100));

        return $query
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage);
    }

    public function createExportRecord(array $payload, ?int $userId): ReportExport
    {
        return DB::transaction(function () use ($payload, $userId) {
            $status = $payload['status'] ?? 'queued';
            $completedAt = $payload['completed_at'] ?? null;

            if (in_array($status, ['done', 'failed'], true) && empty($completedAt)) {
                $completedAt = now();
            }

            return ReportExport::create([
                'user_id' => $userId,
                'report_key' => $payload['report_key'],
                'format' => $payload['format'] ?? 'xlsx',
                'filters' => $payload['filters'] ?? null,
                'file_path' => $payload['file_path'] ?? null,
                'status' => $status,
                'completed_at' => $completedAt,
            ])->fresh(['user:id,name,email']);
        });
    }

    private function buildQuoteStats(Carbon $startDate, Carbon $endDate): array
    {
        $query = Quote::query()->whereBetween('quote_date', [
            $startDate->toDateString(),
            $endDate->toDateString(),
        ]);

        $summary = (clone $query)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft_count,
                SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent_count,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_count,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_count,
                SUM(CASE WHEN status = 'expired' THEN 1 ELSE 0 END) as expired_count,
                COALESCE(SUM(total), 0) as total_amount,
                COALESCE(SUM(CASE WHEN status = 'approved' THEN total ELSE 0 END), 0) as approved_amount
            ")
            ->first();

        $trend = (clone $query)
            ->selectRaw('DATE(quote_date) as period, COUNT(*) as count, COALESCE(SUM(total), 0) as amount')
            ->groupBy(DB::raw('DATE(quote_date)'))
            ->orderByDesc('period')
            ->limit(7)
            ->get()
            ->sortBy('period')
            ->values()
            ->map(fn ($row) => [
                'period' => $row->period,
                'count' => (int) $row->count,
                'amount' => round((float) $row->amount, 2),
            ])
            ->all();

        $approvedCount = (int) ($summary->approved_count ?? 0);
        $totalCount = (int) ($summary->total ?? 0);

        return [
            'summary' => [
                'total' => $totalCount,
                'draft' => (int) ($summary->draft_count ?? 0),
                'sent' => (int) ($summary->sent_count ?? 0),
                'approved' => $approvedCount,
                'rejected' => (int) ($summary->rejected_count ?? 0),
                'expired' => (int) ($summary->expired_count ?? 0),
                'total_amount' => round((float) ($summary->total_amount ?? 0), 2),
                'approved_amount' => round((float) ($summary->approved_amount ?? 0), 2),
                'approval_rate' => $totalCount > 0 ? round(($approvedCount / $totalCount) * 100, 2) : 0,
            ],
            'trend' => $trend,
        ];
    }

    private function buildOrderStats(Carbon $startDate, Carbon $endDate): array
    {
        $query = Order::query()->whereBetween('order_date', [
            $startDate->toDateString(),
            $endDate->toDateString(),
        ]);

        $summary = (clone $query)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_count,
                SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing_count,
                SUM(CASE WHEN status = 'shipped' THEN 1 ELSE 0 END) as shipped_count,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_count,
                SUM(CASE WHEN payment_status = 'unpaid' THEN 1 ELSE 0 END) as unpaid_count,
                SUM(CASE WHEN payment_status = 'partially_paid' THEN 1 ELSE 0 END) as partially_paid_count,
                SUM(CASE WHEN payment_status = 'paid' THEN 1 ELSE 0 END) as paid_count,
                SUM(CASE WHEN payment_status = 'refunded' THEN 1 ELSE 0 END) as refunded_count,
                COALESCE(SUM(total_amount), 0) as total_amount,
                COALESCE(SUM(CASE WHEN payment_status = 'paid' THEN total_amount ELSE 0 END), 0) as paid_amount
            ")
            ->first();

        $trend = (clone $query)
            ->selectRaw('DATE(order_date) as period, COUNT(*) as count, COALESCE(SUM(total_amount), 0) as amount')
            ->groupBy(DB::raw('DATE(order_date)'))
            ->orderByDesc('period')
            ->limit(7)
            ->get()
            ->sortBy('period')
            ->values()
            ->map(fn ($row) => [
                'period' => $row->period,
                'count' => (int) $row->count,
                'amount' => round((float) $row->amount, 2),
            ])
            ->all();

        $completedCount = (int) ($summary->completed_count ?? 0);
        $totalCount = (int) ($summary->total ?? 0);

        return [
            'summary' => [
                'total' => $totalCount,
                'pending' => (int) ($summary->pending_count ?? 0),
                'confirmed' => (int) ($summary->confirmed_count ?? 0),
                'processing' => (int) ($summary->processing_count ?? 0),
                'shipped' => (int) ($summary->shipped_count ?? 0),
                'completed' => $completedCount,
                'cancelled' => (int) ($summary->cancelled_count ?? 0),
                'unpaid' => (int) ($summary->unpaid_count ?? 0),
                'partially_paid' => (int) ($summary->partially_paid_count ?? 0),
                'paid' => (int) ($summary->paid_count ?? 0),
                'refunded' => (int) ($summary->refunded_count ?? 0),
                'total_amount' => round((float) ($summary->total_amount ?? 0), 2),
                'paid_amount' => round((float) ($summary->paid_amount ?? 0), 2),
                'completion_rate' => $totalCount > 0 ? round(($completedCount / $totalCount) * 100, 2) : 0,
            ],
            'trend' => $trend,
        ];
    }

    private function buildInventoryStats(Carbon $startDate, Carbon $endDate): array
    {
        $stockSummary = StockLevel::query()
            ->selectRaw("
                COUNT(*) as total_stock_levels,
                COUNT(DISTINCT item_id) as tracked_items,
                COALESCE(SUM(quantity), 0) as total_units,
                COALESCE(SUM(reserved), 0) as total_reserved,
                SUM(CASE WHEN (quantity - reserved) <= 0 THEN 1 ELSE 0 END) as out_of_stock_count,
                SUM(CASE WHEN (quantity - reserved) > 0 AND (quantity - reserved) <= min_level THEN 1 ELSE 0 END) as low_stock_count
            ")
            ->first();

        $movementSummary = StockMovement::query()
            ->whereBetween('occurred_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
            ->selectRaw("
                COUNT(*) as total_movements,
                SUM(CASE WHEN type = 'inbound' THEN 1 ELSE 0 END) as inbound_count,
                SUM(CASE WHEN type = 'outbound' THEN 1 ELSE 0 END) as outbound_count,
                SUM(CASE WHEN type = 'transfer' THEN 1 ELSE 0 END) as transfer_count,
                SUM(CASE WHEN type = 'adjustment' THEN 1 ELSE 0 END) as adjustment_count
            ")
            ->first();

        $adjustmentCount = StockAdjustment::query()
            ->whereBetween('created_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
            ->count();

        $lowStockItems = StockLevel::query()
            ->with(['warehouse:id,name,code', 'item:id,name,unit,category'])
            ->whereRaw('(quantity - reserved) <= min_level')
            ->orderByRaw('(quantity - reserved) asc')
            ->limit(8)
            ->get()
            ->map(fn (StockLevel $stockLevel) => [
                'id' => $stockLevel->id,
                'item_id' => $stockLevel->item_id,
                'item_name' => $stockLevel->item?->name,
                'item_unit' => $stockLevel->item?->unit,
                'item_category' => $stockLevel->item?->category,
                'warehouse_id' => $stockLevel->warehouse_id,
                'warehouse_name' => $stockLevel->warehouse?->name,
                'warehouse_code' => $stockLevel->warehouse?->code,
                'quantity' => (int) $stockLevel->quantity,
                'reserved' => (int) $stockLevel->reserved,
                'available_quantity' => (int) $stockLevel->available_quantity,
                'min_level' => (int) $stockLevel->min_level,
            ])
            ->all();

        $recentMovements = StockMovement::query()
            ->with(['warehouse:id,name,code', 'item:id,name,unit'])
            ->whereBetween('occurred_at', [$startDate->copy()->startOfDay(), $endDate->copy()->endOfDay()])
            ->orderByDesc('occurred_at')
            ->limit(5)
            ->get()
            ->map(fn (StockMovement $movement) => [
                'id' => $movement->id,
                'type' => $movement->type,
                'quantity' => (int) $movement->quantity,
                'warehouse_name' => $movement->warehouse?->name,
                'warehouse_code' => $movement->warehouse?->code,
                'item_name' => $movement->item?->name,
                'item_unit' => $movement->item?->unit,
                'occurred_at' => optional($movement->occurred_at)->toISOString(),
            ])
            ->all();

        return [
            'summary' => [
                'warehouses' => Warehouse::query()->count(),
                'tracked_items' => (int) ($stockSummary->tracked_items ?? 0),
                'stock_levels' => (int) ($stockSummary->total_stock_levels ?? 0),
                'total_units' => (int) ($stockSummary->total_units ?? 0),
                'total_reserved' => (int) ($stockSummary->total_reserved ?? 0),
                'low_stock_count' => (int) ($stockSummary->low_stock_count ?? 0),
                'out_of_stock_count' => (int) ($stockSummary->out_of_stock_count ?? 0),
                'movement_count' => (int) ($movementSummary->total_movements ?? 0),
                'inbound_count' => (int) ($movementSummary->inbound_count ?? 0),
                'outbound_count' => (int) ($movementSummary->outbound_count ?? 0),
                'transfer_count' => (int) ($movementSummary->transfer_count ?? 0),
                'adjustment_count' => (int) $adjustmentCount,
            ],
            'low_stock_items' => $lowStockItems,
            'recent_movements' => $recentMovements,
        ];
    }

    private function buildExportStats(Carbon $startDate, Carbon $endDate): array
    {
        $query = ReportExport::query()->whereBetween('created_at', [
            $startDate->copy()->startOfDay(),
            $endDate->copy()->endOfDay(),
        ]);

        $summary = (clone $query)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'queued' THEN 1 ELSE 0 END) as queued_count,
                SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing_count,
                SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) as done_count,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_count
            ")
            ->first();

        $recentRecords = ReportExport::query()
            ->with(['user:id,name,email'])
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(fn (ReportExport $record) => $this->transformExportRecord($record))
            ->all();

        return [
            'summary' => [
                'total' => (int) ($summary->total ?? 0),
                'queued' => (int) ($summary->queued_count ?? 0),
                'processing' => (int) ($summary->processing_count ?? 0),
                'done' => (int) ($summary->done_count ?? 0),
                'failed' => (int) ($summary->failed_count ?? 0),
            ],
            'recent_records' => $recentRecords,
        ];
    }

    private function resolveDateRange(array $filters): array
    {
        $rangeDays = max(7, min((int) ($filters['range_days'] ?? 30), 365));

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $startDate = Carbon::parse($filters['start_date'])->startOfDay();
            $endDate = Carbon::parse($filters['end_date'])->endOfDay();

            if ($startDate->gt($endDate)) {
                [$startDate, $endDate] = [$endDate->copy()->startOfDay(), $startDate->copy()->endOfDay()];
            }

            $rangeDays = $startDate->diffInDays($endDate) + 1;

            return [$startDate, $endDate, $rangeDays];
        }

        $endDate = now()->endOfDay();
        $startDate = $endDate->copy()->subDays($rangeDays - 1)->startOfDay();

        return [$startDate, $endDate, $rangeDays];
    }

    public function transformExportRecord(ReportExport $record): array
    {
        return [
            'id' => $record->id,
            'report_key' => $record->report_key,
            'format' => $record->format,
            'status' => $record->status,
            'filters' => $record->filters,
            'file_path' => $record->file_path,
            'created_at' => optional($record->created_at)->toISOString(),
            'completed_at' => optional($record->completed_at)->toISOString(),
            'user' => $record->user ? [
                'id' => $record->user->id,
                'name' => $record->user->name,
                'email' => $record->user->email,
            ] : null,
        ];
    }
}
