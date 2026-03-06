<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Opportunity;
use App\Services\OpportunityService;
use Illuminate\Http\Request;
use Throwable;

class OpportunityController extends Controller
{
    public function __construct(private readonly OpportunityService $opportunityService)
    {
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:100',
            'status' => 'nullable|in:open,won,lost',
            'stage' => 'nullable|in:new,qualified,proposal,negotiation,won,lost',
            'customer_id' => 'nullable|integer|exists:customers,id',
            'close_date_from' => 'nullable|date',
            'close_date_to' => 'nullable|date|after_or_equal:close_date_from',
            'sort_by' => 'nullable|string|in:id,name,stage,status,amount,expected_close_date,created_at,updated_at',
            'sort_order' => 'nullable|string|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        return response()->json(
            $this->opportunityService->paginateOpportunities($validated)
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'name' => 'required|string|max:255',
            'stage' => 'nullable|in:new,qualified,proposal,negotiation,won,lost',
            'amount' => 'nullable|numeric|min:0',
            'expected_close_date' => 'nullable|date',
            'status' => 'nullable|in:open,won,lost',
            'notes' => 'nullable|string',
        ]);

        try {
            $opportunity = $this->opportunityService->createOpportunity($validated, (int)auth()->id());

            return response()->json([
                'message' => '商機建立成功',
                'data' => $opportunity,
            ], 201);
        } catch (Throwable $e) {
            return response()->json([
                'message' => '商機建立失敗',
                'error' => '系統發生未預期錯誤',
            ], 500);
        }
    }

    public function show(Opportunity $opportunity)
    {
        $opportunity->load([
            'customer:id,name,industry,status',
            'creator:id,name,email',
            'updater:id,name,email',
            'logs.user:id,name,email',
        ]);

        return response()->json([
            'data' => $opportunity,
        ]);
    }

    public function update(Request $request, Opportunity $opportunity)
    {
        $validated = $request->validate([
            'customer_id' => 'sometimes|required|exists:customers,id',
            'name' => 'sometimes|required|string|max:255',
            'stage' => 'nullable|in:new,qualified,proposal,negotiation,won,lost',
            'amount' => 'nullable|numeric|min:0',
            'expected_close_date' => 'nullable|date',
            'status' => 'nullable|in:open,won,lost',
            'notes' => 'nullable|string',
        ]);

        try {
            $updatedOpportunity = $this->opportunityService->updateOpportunity($opportunity, $validated, (int)auth()->id());

            return response()->json([
                'message' => '商機更新成功',
                'data' => $updatedOpportunity,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => '商機更新失敗',
                'error' => '系統發生未預期錯誤',
            ], 500);
        }
    }

    public function updateStatus(Request $request, Opportunity $opportunity)
    {
        $validated = $request->validate([
            'status' => 'required|in:open,won,lost',
        ]);

        try {
            $updatedOpportunity = $this->opportunityService->updateStatus(
                $opportunity,
                $validated['status'],
                (int)auth()->id()
            );

            return response()->json([
                'message' => '商機狀態更新成功',
                'data' => $updatedOpportunity,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => '商機狀態更新失敗',
                'error' => '系統發生未預期錯誤',
            ], 500);
        }
    }

    public function destroy(Opportunity $opportunity)
    {
        try {
            $this->opportunityService->deleteOpportunity($opportunity);

            return response()->json([
                'message' => '商機刪除成功',
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => '商機刪除失敗',
                'error' => '系統發生未預期錯誤',
            ], 500);
        }
    }
}
