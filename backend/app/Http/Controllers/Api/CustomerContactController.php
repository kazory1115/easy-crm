<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerContact;
use App\Services\CustomerService;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Throwable;

class CustomerContactController extends Controller
{
    public function __construct(private readonly CustomerService $customerService)
    {
    }

    public function index(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:100',
            'is_primary' => 'nullable|boolean',
            'sort_by' => 'nullable|string|in:id,name,email,is_primary,created_at',
            'sort_order' => 'nullable|string|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        return response()->json(
            $this->customerService->paginateContacts($customer, $validated)
        );
    }

    public function store(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'is_primary' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        try {
            $contact = $this->customerService->createContact($customer, $validated, (int)auth()->id());

            return response()->json([
                'message' => '聯絡人建立成功',
                'data' => $contact,
            ], 201);
        } catch (Throwable $e) {
            return response()->json([
                'message' => '聯絡人建立失敗',
                'error' => '系統發生未預期錯誤',
            ], 500);
        }
    }

    public function show(Customer $customer, CustomerContact $contact)
    {
        try {
            $currentContact = $this->customerService->getContact($customer, $contact);

            return response()->json([
                'data' => $currentContact,
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => '查無此聯絡人',
            ], 404);
        }
    }

    public function update(Request $request, Customer $customer, CustomerContact $contact)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'title' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'is_primary' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        try {
            $updatedContact = $this->customerService->updateContact($customer, $contact, $validated, (int)auth()->id());

            return response()->json([
                'message' => '聯絡人更新成功',
                'data' => $updatedContact,
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => '查無此聯絡人',
            ], 404);
        } catch (Throwable $e) {
            return response()->json([
                'message' => '聯絡人更新失敗',
                'error' => '系統發生未預期錯誤',
            ], 500);
        }
    }

    public function destroy(Customer $customer, CustomerContact $contact)
    {
        try {
            $this->customerService->deleteContact($customer, $contact);

            return response()->json([
                'message' => '聯絡人刪除成功',
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => '查無此聯絡人',
            ], 404);
        } catch (Throwable $e) {
            return response()->json([
                'message' => '聯絡人刪除失敗',
                'error' => '系統發生未預期錯誤',
            ], 500);
        }
    }
}
