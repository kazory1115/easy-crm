<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerActivity;
use App\Models\CustomerContact;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CustomerService
{
    private const SORTABLE_CUSTOMER_COLUMNS = [
        'id',
        'name',
        'status',
        'industry',
        'created_at',
        'updated_at',
    ];

    private const SORTABLE_CONTACT_COLUMNS = [
        'id',
        'name',
        'email',
        'is_primary',
        'created_at',
    ];

    private const SORTABLE_ACTIVITY_COLUMNS = [
        'id',
        'type',
        'activity_at',
        'created_at',
    ];

    public function paginateCustomers(array $filters = []): LengthAwarePaginator
    {
        $query = Customer::query()
            ->with(['creator:id,name', 'updater:id,name'])
            ->withCount(['contacts', 'activities', 'opportunities']);

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['industry'])) {
            $query->where('industry', $filters['industry']);
        }

        $this->applySorting(
            $query,
            $filters['sort_by'] ?? 'created_at',
            $filters['sort_order'] ?? 'desc',
            self::SORTABLE_CUSTOMER_COLUMNS
        );

        return $query->paginate($this->normalizePerPage($filters['per_page'] ?? 15));
    }

    public function createCustomer(array $payload, int $userId): Customer
    {
        return DB::transaction(function () use ($payload, $userId) {
            $data = $this->normalizeCustomerPayload($payload);
            if (!array_key_exists('status', $data)) {
                $data['status'] = 'active';
            }
            $data['created_by'] = $userId;
            $data['updated_by'] = $userId;

            return Customer::create($data)->fresh(['creator:id,name', 'updater:id,name']);
        });
    }

    public function updateCustomer(Customer $customer, array $payload, int $userId): Customer
    {
        return DB::transaction(function () use ($customer, $payload, $userId) {
            $data = $this->normalizeCustomerPayload($payload);
            $data['updated_by'] = $userId;

            $customer->fill($data);
            $customer->save();

            return $customer->fresh(['creator:id,name', 'updater:id,name']);
        });
    }

    public function deleteCustomer(Customer $customer): void
    {
        $customer->delete();
    }

    public function paginateContacts(Customer $customer, array $filters = []): LengthAwarePaginator
    {
        $query = $customer->contacts()
            ->with(['creator:id,name', 'updater:id,name']);

        if (!empty($filters['search'])) {
            $keyword = $filters['search'];
            $query->where(function (Builder $builder) use ($keyword) {
                $builder->where('name', 'like', "%{$keyword}%")
                    ->orWhere('title', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%")
                    ->orWhere('phone', 'like', "%{$keyword}%")
                    ->orWhere('mobile', 'like', "%{$keyword}%");
            });
        }

        if (array_key_exists('is_primary', $filters) && $filters['is_primary'] !== null && $filters['is_primary'] !== '') {
            $query->where('is_primary', $this->normalizeBoolean($filters['is_primary']));
        }

        $query->orderByDesc('is_primary');
        $this->applySorting(
            $query,
            $filters['sort_by'] ?? 'created_at',
            $filters['sort_order'] ?? 'desc',
            self::SORTABLE_CONTACT_COLUMNS
        );

        return $query->paginate($this->normalizePerPage($filters['per_page'] ?? 15));
    }

    public function createContact(Customer $customer, array $payload, int $userId): CustomerContact
    {
        return DB::transaction(function () use ($customer, $payload, $userId) {
            $data = $this->normalizeContactPayload($payload);
            $data['customer_id'] = $customer->id;
            $data['created_by'] = $userId;
            $data['updated_by'] = $userId;

            if (($data['is_primary'] ?? false) === true) {
                $this->clearPrimaryContact($customer->id);
            }

            $contact = CustomerContact::create($data);
            return $contact->fresh(['creator:id,name', 'updater:id,name']);
        });
    }

    public function updateContact(Customer $customer, CustomerContact $contact, array $payload, int $userId): CustomerContact
    {
        $this->assertContactBelongsToCustomer($customer, $contact);

        return DB::transaction(function () use ($customer, $contact, $payload, $userId) {
            $data = $this->normalizeContactPayload($payload);
            $data['updated_by'] = $userId;

            if (($data['is_primary'] ?? false) === true) {
                $this->clearPrimaryContact($customer->id, $contact->id);
            }

            $contact->fill($data);
            $contact->save();

            return $contact->fresh(['creator:id,name', 'updater:id,name']);
        });
    }

    public function deleteContact(Customer $customer, CustomerContact $contact): void
    {
        $this->assertContactBelongsToCustomer($customer, $contact);
        $contact->delete();
    }

    public function paginateActivities(Customer $customer, array $filters = []): LengthAwarePaginator
    {
        $query = $customer->activities()
            ->with(['user:id,name,email']);

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['start_date'])) {
            $query->whereDate('activity_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('activity_at', '<=', $filters['end_date']);
        }

        if (!empty($filters['search'])) {
            $keyword = $filters['search'];
            $query->where(function (Builder $builder) use ($keyword) {
                $builder->where('subject', 'like', "%{$keyword}%")
                    ->orWhere('content', 'like', "%{$keyword}%");
            });
        }

        $this->applySorting(
            $query,
            $filters['sort_by'] ?? 'activity_at',
            $filters['sort_order'] ?? 'desc',
            self::SORTABLE_ACTIVITY_COLUMNS
        );

        return $query->paginate($this->normalizePerPage($filters['per_page'] ?? 15));
    }

    public function createActivity(Customer $customer, array $payload, int $userId): CustomerActivity
    {
        $data = $this->normalizeActivityPayload($payload);
        if (!array_key_exists('activity_at', $data)) {
            $data['activity_at'] = now();
        }
        $data['customer_id'] = $customer->id;
        $data['user_id'] = $userId;

        $activity = CustomerActivity::create($data);
        return $activity->fresh(['user:id,name,email']);
    }

    public function getContact(Customer $customer, CustomerContact $contact): CustomerContact
    {
        $this->assertContactBelongsToCustomer($customer, $contact);
        return $contact->load(['creator:id,name', 'updater:id,name']);
    }

    public function getActivity(Customer $customer, CustomerActivity $activity): CustomerActivity
    {
        $this->assertActivityBelongsToCustomer($customer, $activity);
        return $activity->load(['user:id,name,email']);
    }

    private function normalizeCustomerPayload(array $payload): array
    {
        $data = [];
        $fields = [
            'name',
            'contact_person',
            'phone',
            'mobile',
            'tax_id',
            'website',
            'industry',
            'address',
            'notes',
            'status',
        ];

        foreach ($fields as $field) {
            if (array_key_exists($field, $payload)) {
                $data[$field] = $payload[$field];
            }
        }

        if (array_key_exists('email', $payload)) {
            $data['email'] = $this->normalizeNullableEmail($payload['email']);
        }

        return $data;
    }

    private function normalizeContactPayload(array $payload): array
    {
        $data = [];
        $fields = [
            'name',
            'title',
            'phone',
            'mobile',
            'notes',
        ];

        foreach ($fields as $field) {
            if (array_key_exists($field, $payload)) {
                $data[$field] = $payload[$field];
            }
        }

        if (array_key_exists('email', $payload)) {
            $data['email'] = $this->normalizeNullableEmail($payload['email']);
        }

        if (array_key_exists('is_primary', $payload)) {
            $data['is_primary'] = $this->normalizeBoolean($payload['is_primary']);
        }

        return $data;
    }

    private function normalizeActivityPayload(array $payload): array
    {
        $data = [];
        $fields = [
            'type',
            'subject',
            'content',
            'activity_at',
            'next_action_at',
        ];

        foreach ($fields as $field) {
            if (array_key_exists($field, $payload)) {
                $data[$field] = $payload[$field];
            }
        }

        return $data;
    }

    private function clearPrimaryContact(int $customerId, ?int $exceptId = null): void
    {
        CustomerContact::query()
            ->where('customer_id', $customerId)
            ->when($exceptId, fn (Builder $query) => $query->where('id', '!=', $exceptId))
            ->update(['is_primary' => false]);
    }

    private function assertContactBelongsToCustomer(Customer $customer, CustomerContact $contact): void
    {
        if ((int)$contact->customer_id !== (int)$customer->id) {
            throw new InvalidArgumentException('指定的聯絡人不屬於該客戶。');
        }
    }

    private function assertActivityBelongsToCustomer(Customer $customer, CustomerActivity $activity): void
    {
        if ((int)$activity->customer_id !== (int)$customer->id) {
            throw new InvalidArgumentException('指定的活動不屬於該客戶。');
        }
    }

    private function applySorting(
        Builder $query,
        string $sortBy,
        string $sortOrder,
        array $allowColumns
    ): void {
        $column = in_array($sortBy, $allowColumns, true) ? $sortBy : 'created_at';
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

    private function normalizeNullableEmail($email): ?string
    {
        if ($email === null || $email === '') {
            return null;
        }

        return strtolower((string)$email);
    }

    private function normalizeBoolean($value): bool
    {
        $normalized = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($normalized === null) {
            return (bool)$value;
        }

        return $normalized;
    }
}
