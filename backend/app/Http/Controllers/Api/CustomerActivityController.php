<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerActivity;
use App\Services\CustomerService;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Throwable;

class CustomerActivityController extends Controller
{
    public function __construct(private readonly CustomerService $customerService)
    {
    }

    public function index(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:100',
            'type' => 'nullable|in:call,email,meeting,note,follow_up,other',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'sort_by' => 'nullable|string|in:id,type,activity_at,created_at',
            'sort_order' => 'nullable|string|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        return response()->json(
            $this->customerService->paginateActivities($customer, $validated)
        );
    }

    public function store(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'type' => 'required|in:call,email,meeting,note,follow_up,other',
            'subject' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'activity_at' => 'nullable|date',
            'next_action_at' => 'nullable|date|after_or_equal:activity_at',
        ]);

        try {
            $activity = $this->customerService->createActivity($customer, $validated, (int)auth()->id());

            return response()->json([
                'message' => '活動紀錄建立成功',
                'data' => $activity,
            ], 201);
        } catch (Throwable $e) {
            return response()->json([
                'message' => '活動紀錄建立失敗',
                'error' => '系統發生未預期錯誤',
            ], 500);
        }
    }

    public function show(Customer $customer, CustomerActivity $activity)
    {
        try {
            $currentActivity = $this->customerService->getActivity($customer, $activity);

            return response()->json([
                'data' => $currentActivity,
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => '查無此活動紀錄',
            ], 404);
        }
    }
}
