<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Services\QuoteService; // Added QuoteService
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuoteController extends Controller
{
    protected QuoteService $quoteService; // Injected QuoteService

    public function __construct(QuoteService $quoteService)
    {
        $this->quoteService = $quoteService;
    }

    /**
     * 取得報價單列表
     */
    public function index(Request $request)
    {
        $query = Quote::with(['customer', 'creator', 'items']);

        // 搜尋
        if ($request->has('search')) {
            $query->search($request->search);
        }

        // 狀態篩選
        if ($request->has('status')) {
            $query->byStatus($request->status);
        }

        // 日期範圍篩選
        if ($request->has('start_date') || $request->has('end_date')) {
            $query->dateRange($request->start_date, $request->end_date);
        }

        // 排序
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // 分頁
        $perPage = $request->get('per_page', 15);
        $quotes = $query->paginate($perPage);

        return response()->json($quotes);
    }

    /**
     * 取得單一報價單
     */
    public function show($id)
    {
        $quote = Quote::with(['customer', 'items', 'creator', 'updater', 'logs.user'])
            ->findOrFail($id);

        return response()->json(['data' => $quote]);
    }

    /**
     * 建立報價單
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'required|string|max:255',
            'contact_phone' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'project_name' => 'nullable|string|max:255',
            'quote_date' => 'required|date',
            'valid_until' => 'nullable|date|after:quote_date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'nullable|exists:items,id',
            'items.*.type' => 'required|in:input,drop,template',
            'items.*.name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit' => 'required|string|max:50',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.fields' => 'nullable|array',
            'items.*.notes' => 'nullable|string',
            'tax_rate' => 'nullable|numeric|min:0|max:1', // Added tax_rate validation
        ]);

        $quote = $this->quoteService->createQuote(
            array_filter($validated, fn($key) => $key !== 'items', ARRAY_FILTER_USE_KEY),
            $validated['items'],
            auth()->id()
        );

        // 重新載入關聯資料
        $quote->load(['items', 'customer']);

        return response()->json([
            'message' => '報價單建立成功',
            'data' => $quote,
        ], 201);
    }

    /**
     * 更新報價單
     */
    public function update(Request $request, $id)
    {
        $quote = Quote::findOrFail($id);

        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'sometimes|required|string|max:255',
            'contact_phone' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'project_name' => 'nullable|string|max:255',
            'quote_date' => 'sometimes|required|date',
            'valid_until' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'sometimes|required|array|min:1',
            'items.*.item_id' => 'nullable|exists:items,id',
            'items.*.type' => 'required_with:items|in:input,drop,template',
            'items.*.name' => 'required_with:items|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'items.*.unit' => 'required_with:items|string|max:50',
            'items.*.price' => 'required_with:items|numeric|min:0',
            'items.*.fields' => 'nullable|array',
            'items.*.notes' => 'nullable|string',
            'tax_rate' => 'nullable|numeric|min:0|max:1', // Added tax_rate validation
        ]);

        $quote = $this->quoteService->updateQuote(
            $quote,
            array_filter($validated, fn($key) => $key !== 'items', ARRAY_FILTER_USE_KEY),
            $validated['items'] ?? [], // Pass empty array if items are not present
            auth()->id()
        );

        // 重新載入關聯資料
        $quote->load(['items', 'customer']);

        return response()->json([
            'message' => '報價單更新成功',
            'data' => $quote,
        ]);
    }

    /**
     * 刪除報價單
     */
    public function destroy($id)
    {
        $quote = Quote::findOrFail($id);
        $quote->delete();

        return response()->json([
            'message' => '報價單刪除成功',
        ]);
    }

    /**
     * 批次刪除報價單
     */
    public function batchDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|exists:quotes,id',
        ]);

        Quote::whereIn('id', $validated['ids'])->delete();

        return response()->json([
            'message' => '批次刪除成功',
        ]);
    }

    /**
     * 更新報價單狀態
     */
    public function updateStatus(Request $request, $id)
    {
        $quote = Quote::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:draft,sent,approved,rejected,expired',
        ]);

        $quote->status = $validated['status'];

        if ($validated['status'] === 'sent' && !$quote->sent_at) {
            $quote->sent_at = now();
        }

        if ($validated['status'] === 'approved' && !$quote->approved_at) {
            $quote->approved_at = now();
        }

        $quote->updated_by = auth()->id();
        $quote->save();

        return response()->json([
            'message' => '狀態更新成功',
            'data' => $quote,
        ]);
    }

    /**
     * 發送報價單
     */
    public function send(Request $request, $id)
    {
        $quote = Quote::findOrFail($id);

        $validated = $request->validate([
            'email' => 'required|email',
            'subject' => 'nullable|string|max:255',
            'message' => 'nullable|string',
        ]);

        // TODO: 實作郵件發送功能
        // Mail::to($validated['email'])->send(new QuoteMail($quote, $validated));

        // 更新狀態
        $quote->status = 'sent';
        $quote->sent_at = now();
        $quote->updated_by = auth()->id();
        $quote->save();

        return response()->json([
            'message' => '報價單已發送',
            'data' => $quote,
        ]);
    }

    /**
     * 取得報價單統計
     */
    public function stats(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = Quote::query();

        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }

        $stats = [
            'total' => $query->count(),
            'draft' => (clone $query)->byStatus('draft')->count(),
            'sent' => (clone $query)->byStatus('sent')->count(),
            'approved' => (clone $query)->byStatus('approved')->count(),
            'rejected' => (clone $query)->byStatus('rejected')->count(),
            'total_amount' => $query->sum('total'),
            'approved_amount' => (clone $query)->byStatus('approved')->sum('total'),
        ];

        return response()->json(['data' => $stats]);
    }

    /**
     * 將已核准的報價單轉換為訂單。
     *
     * @param Request $request
     * @param int $id 報價單ID
     * @return \Illuminate\Http\Response
     */
    public function convertToOrder(Request $request, $id)
    {
        $quote = Quote::findOrFail($id);

        try {
            $order = $this->quoteService->createOrderFromQuote($quote, auth()->id());

            return response()->json([
                'message' => '報價單已成功轉換為訂單',
                'data' => $order,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => '轉換失敗',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
