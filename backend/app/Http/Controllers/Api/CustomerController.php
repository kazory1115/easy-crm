<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Http\Request;
use Throwable;

class CustomerController extends Controller
{
    public function __construct(private readonly CustomerService $customerService)
    {
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:100',
            'status' => 'nullable|in:active,inactive',
            'industry' => 'nullable|string|max:100',
            'sort_by' => 'nullable|string|in:id,name,status,industry,created_at,updated_at',
            'sort_order' => 'nullable|string|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        return response()->json(
            $this->customerService->paginateCustomers($validated)
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'tax_id' => 'nullable|string|max:20',
            'website' => 'nullable|url|max:255',
            'industry' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
        ]);

        try {
            $customer = $this->customerService->createCustomer($validated, (int)auth()->id());

            return response()->json([
                'message' => '客戶建立成功',
                'data' => $customer,
            ], 201);
        } catch (Throwable $e) {
            return response()->json([
                'message' => '客戶建立失敗',
                'error' => '系統發生未預期錯誤',
            ], 500);
        }
    }

    public function show(Customer $customer)
    {
        $customer->load([
            'creator:id,name,email',
            'updater:id,name,email',
        ])->loadCount(['contacts', 'activities', 'opportunities']);

        return response()->json([
            'data' => $customer,
        ]);
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'tax_id' => 'nullable|string|max:20',
            'website' => 'nullable|url|max:255',
            'industry' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
        ]);

        try {
            $updatedCustomer = $this->customerService->updateCustomer($customer, $validated, (int)auth()->id());

            return response()->json([
                'message' => '客戶更新成功',
                'data' => $updatedCustomer,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => '客戶更新失敗',
                'error' => '系統發生未預期錯誤',
            ], 500);
        }
    }

    public function destroy(Customer $customer)
    {
        try {
            $this->customerService->deleteCustomer($customer);

            return response()->json([
                'message' => '客戶刪除成功',
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => '客戶刪除失敗',
                'error' => '系統發生未預期錯誤',
            ], 500);
        }
    }
}
