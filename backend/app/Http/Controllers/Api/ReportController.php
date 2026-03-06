<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reportService
    ) {
    }

    public function dashboard(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'range_days' => 'nullable|integer|min:7|max:365',
        ]);

        return response()->json([
            'data' => $this->reportService->getDashboard($validated),
        ]);
    }

    public function exports(Request $request)
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:100',
            'report_key' => 'nullable|string|max:100',
            'format' => 'nullable|in:csv,xlsx,pdf',
            'status' => 'nullable|in:queued,processing,done,failed',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'sort_by' => 'nullable|in:id,report_key,format,status,created_at,completed_at',
            'sort_order' => 'nullable|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        return response()->json($this->reportService->paginateExportRecords($validated));
    }

    public function storeExport(Request $request)
    {
        $validated = $request->validate([
            'report_key' => 'required|string|max:100',
            'format' => 'nullable|in:csv,xlsx,pdf',
            'filters' => 'nullable|array',
        ]);

        $record = $this->reportService->createExportRecord($validated, auth()->id());

        return response()->json([
            'message' => '匯出任務已建立',
            'data' => $this->reportService->transformExportRecord($record),
        ], 201);
    }
}
