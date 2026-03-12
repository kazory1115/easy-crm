<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\QuoteMail;
use App\Models\Quote;
use App\Services\QuoteDocumentService;
use App\Services\QuoteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Throwable;

class QuoteController extends Controller
{
    public function __construct(
        protected QuoteService $quoteService,
        protected QuoteDocumentService $quoteDocumentService
    ) {
    }

    public function index(Request $request)
    {
        $query = Quote::with(['customer', 'creator', 'items']);

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('start_date') || $request->filled('end_date')) {
            $query->dateRange($request->start_date, $request->end_date);
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $perPage = (int) $request->get('per_page', 15);

        return response()->json(
            $query->orderBy($sortBy, $sortOrder)->paginate($perPage)
        );
    }

    public function show(int $id)
    {
        $quote = Quote::with(['customer', 'items', 'creator', 'updater', 'logs.user'])->findOrFail($id);

        return response()->json([
            'data' => $quote,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->quoteRules());

        $quote = $this->quoteService->createQuote(
            array_filter($validated, fn ($key) => $key !== 'items', ARRAY_FILTER_USE_KEY),
            $validated['items'],
            auth()->id()
        );

        return response()->json([
            'message' => '報價單建立成功',
            'data' => $quote->load(['items', 'customer']),
        ], 201);
    }

    public function update(Request $request, int $id)
    {
        $quote = Quote::findOrFail($id);
        $validated = $request->validate($this->quoteRules(true));

        $updatedQuote = $this->quoteService->updateQuote(
            $quote,
            array_filter($validated, fn ($key) => $key !== 'items', ARRAY_FILTER_USE_KEY),
            $validated['items'] ?? $quote->items()->get()->map(function ($item) {
                return [
                    'item_id' => $item->item_id,
                    'type' => $item->type,
                    'name' => $item->name,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit' => $item->unit,
                    'price' => $item->price,
                    'fields' => $item->fields,
                    'notes' => $item->notes,
                ];
            })->all(),
            auth()->id()
        );

        return response()->json([
            'message' => '報價單更新成功',
            'data' => $updatedQuote->load(['items', 'customer']),
        ]);
    }

    public function destroy(int $id)
    {
        $quote = Quote::findOrFail($id);
        $quote->delete();

        return response()->json([
            'message' => '報價單已刪除',
        ]);
    }

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

    public function updateStatus(Request $request, int $id)
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

    public function send(Request $request, int $id)
    {
        $quote = Quote::with(['items', 'customer'])->findOrFail($id);

        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'subject' => 'nullable|string|max:255',
            'message' => 'nullable|string',
        ]);

        $subject = $validated['subject'] ?: sprintf('報價單 %s', $quote->quote_number);

        try {
            Mail::to($validated['email'])->send(new QuoteMail(
                quote: $quote,
                subjectLine: $subject,
                messageBody: $validated['message'] ?? null,
                pdfBinary: $this->quoteDocumentService->buildPdfBinary($quote),
                pdfFilename: $this->quoteDocumentService->buildFilename($quote, 'pdf')
            ));
        } catch (Throwable $exception) {
            return response()->json([
                'message' => '報價單寄送失敗',
                'error' => $exception->getMessage(),
            ], 500);
        }

        $quote->status = 'sent';
        $quote->sent_at = now();
        $quote->updated_by = auth()->id();
        $quote->save();

        return response()->json([
            'message' => '報價單已寄出',
            'data' => $quote,
        ]);
    }

    public function exportPdf(int $id)
    {
        $quote = Quote::findOrFail($id);

        return $this->quoteDocumentService->downloadPdf($quote);
    }

    public function exportExcel(int $id)
    {
        $quote = Quote::findOrFail($id);

        return $this->quoteDocumentService->downloadExcel($quote);
    }

    public function stats(Request $request)
    {
        $query = Quote::query();

        if ($request->filled('start_date') || $request->filled('end_date')) {
            $query->dateRange($request->get('start_date'), $request->get('end_date'));
        }

        return response()->json([
            'data' => [
                'total' => $query->count(),
                'draft' => (clone $query)->byStatus('draft')->count(),
                'sent' => (clone $query)->byStatus('sent')->count(),
                'approved' => (clone $query)->byStatus('approved')->count(),
                'rejected' => (clone $query)->byStatus('rejected')->count(),
                'total_amount' => (clone $query)->sum('total'),
                'approved_amount' => (clone $query)->byStatus('approved')->sum('total'),
            ],
        ]);
    }

    public function convertToOrder(Request $request, int $id)
    {
        $quote = Quote::findOrFail($id);

        try {
            $order = $this->quoteService->createOrderFromQuote($quote, auth()->id());

            return response()->json([
                'message' => '報價單已轉為訂單',
                'data' => $order,
            ], 201);
        } catch (Throwable $exception) {
            return response()->json([
                'message' => '轉單失敗',
                'error' => $exception->getMessage(),
            ], 500);
        }
    }

    private function quoteRules(bool $isUpdate = false): array
    {
        $customerNameRule = $isUpdate ? 'sometimes|required|string|max:255' : 'required|string|max:255';
        $quoteDateRule = $isUpdate ? 'sometimes|required|date' : 'required|date';
        $itemsRule = $isUpdate ? 'sometimes|required|array|min:1' : 'required|array|min:1';
        $requiredWithItems = $isUpdate ? 'required_with:items' : 'required';

        return [
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => $customerNameRule,
            'contact_phone' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'project_name' => 'nullable|string|max:255',
            'quote_date' => $quoteDateRule,
            'valid_until' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => $itemsRule,
            'items.*.item_id' => 'nullable|exists:items,id',
            'items.*.type' => $requiredWithItems . '|in:input,drop,template',
            'items.*.name' => $requiredWithItems . '|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => $requiredWithItems . '|integer|min:1',
            'items.*.unit' => $requiredWithItems . '|string|max:50',
            'items.*.price' => $requiredWithItems . '|numeric|min:0',
            'items.*.fields' => 'nullable|array',
            'items.*.notes' => 'nullable|string',
            'tax_rate' => 'nullable|numeric|min:0|max:1',
        ];
    }
}
