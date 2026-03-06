<?php

namespace App\Services;

use App\Models\Opportunity;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class OpportunityService
{
    private const STAGE_OPTIONS = ['new', 'qualified', 'proposal', 'negotiation', 'won', 'lost'];
    private const STATUS_OPTIONS = ['open', 'won', 'lost'];

    private const SORTABLE_COLUMNS = [
        'id',
        'name',
        'stage',
        'status',
        'amount',
        'expected_close_date',
        'created_at',
        'updated_at',
    ];

    public function paginateOpportunities(array $filters = []): LengthAwarePaginator
    {
        $query = Opportunity::query()
            ->with([
                'customer:id,name,industry,status',
                'creator:id,name',
                'updater:id,name',
            ]);

        if (!empty($filters['search'])) {
            $keyword = $filters['search'];
            $query->where(function (Builder $builder) use ($keyword) {
                $builder->where('name', 'like', "%{$keyword}%")
                    ->orWhereHas('customer', function (Builder $customerQuery) use ($keyword) {
                        $customerQuery->where('name', 'like', "%{$keyword}%");
                    });
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['stage'])) {
            $query->where('stage', $filters['stage']);
        }

        if (!empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (!empty($filters['close_date_from'])) {
            $query->whereDate('expected_close_date', '>=', $filters['close_date_from']);
        }

        if (!empty($filters['close_date_to'])) {
            $query->whereDate('expected_close_date', '<=', $filters['close_date_to']);
        }

        $this->applySorting(
            $query,
            $filters['sort_by'] ?? 'created_at',
            $filters['sort_order'] ?? 'desc'
        );

        return $query->paginate($this->normalizePerPage($filters['per_page'] ?? 15));
    }

    public function createOpportunity(array $payload, int $userId): Opportunity
    {
        return DB::transaction(function () use ($payload, $userId) {
            $data = $this->normalizeOpportunityPayload($payload);
            [$data['stage'], $data['status']] = $this->resolveStageAndStatus(
                $data['stage'] ?? 'new',
                $data['status'] ?? 'open'
            );

            $data['created_by'] = $userId;
            $data['updated_by'] = $userId;

            return Opportunity::create($data)->fresh(['customer', 'creator:id,name', 'updater:id,name']);
        });
    }

    public function updateOpportunity(Opportunity $opportunity, array $payload, int $userId): Opportunity
    {
        return DB::transaction(function () use ($opportunity, $payload, $userId) {
            $data = $this->normalizeOpportunityPayload($payload);
            [$stage, $status] = $this->resolveStageAndStatus(
                $data['stage'] ?? $opportunity->stage,
                $data['status'] ?? $opportunity->status
            );

            $data['stage'] = $stage;
            $data['status'] = $status;
            $data['updated_by'] = $userId;

            $opportunity->fill($data);
            $opportunity->save();

            return $opportunity->fresh(['customer', 'creator:id,name', 'updater:id,name']);
        });
    }

    public function updateStatus(Opportunity $opportunity, string $status, int $userId): Opportunity
    {
        return DB::transaction(function () use ($opportunity, $status, $userId) {
            [$stage, $resolvedStatus] = $this->resolveStageAndStatus($opportunity->stage, $status);

            $opportunity->status = $resolvedStatus;
            $opportunity->stage = $stage;
            $opportunity->updated_by = $userId;
            $opportunity->save();

            return $opportunity->fresh(['customer', 'creator:id,name', 'updater:id,name']);
        });
    }

    public function deleteOpportunity(Opportunity $opportunity): void
    {
        $opportunity->delete();
    }

    private function normalizeOpportunityPayload(array $payload): array
    {
        $data = [];
        $fields = [
            'customer_id',
            'name',
            'stage',
            'status',
            'expected_close_date',
            'notes',
        ];

        foreach ($fields as $field) {
            if (array_key_exists($field, $payload)) {
                $data[$field] = $payload[$field];
            }
        }

        if (array_key_exists('amount', $payload)) {
            $amount = (float)$payload['amount'];
            $data['amount'] = round(max($amount, 0), 2);
        }

        return $data;
    }

    private function resolveStageAndStatus(string $stage, string $status): array
    {
        $normalizedStage = in_array($stage, self::STAGE_OPTIONS, true) ? $stage : 'new';
        $normalizedStatus = in_array($status, self::STATUS_OPTIONS, true) ? $status : 'open';

        if (in_array($normalizedStage, ['won', 'lost'], true)) {
            return [$normalizedStage, $normalizedStage];
        }

        if (in_array($normalizedStatus, ['won', 'lost'], true)) {
            return [$normalizedStatus, $normalizedStatus];
        }

        return [$normalizedStage, 'open'];
    }

    private function applySorting(Builder $query, string $sortBy, string $sortOrder): void
    {
        $column = in_array($sortBy, self::SORTABLE_COLUMNS, true) ? $sortBy : 'created_at';
        $order = strtolower($sortOrder) === 'asc' ? 'asc' : 'desc';
        $query->orderBy($column, $order);
    }

    private function normalizePerPage($perPage): int
    {
        $value = (int)$perPage;
        if ($value <= 0) {
            return 15;
        }

        return min($value, 100);
    }
}
