<?php

namespace App\Services;

use App\Models\Quote;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QuoteDocumentService
{
    public function buildPdfBinary(Quote $quote): string
    {
        $quote = $this->prepareQuote($quote);

        return Pdf::loadView('quotes.pdf', [
            'quote' => $quote,
            'items' => $quote->items,
            'subtotal' => (float) $quote->subtotal,
            'tax' => (float) $quote->tax,
            'discount' => (float) $quote->discount,
            'total' => (float) $quote->total,
        ])->setPaper('a4')->output();
    }

    public function downloadPdf(Quote $quote): Response|StreamedResponse
    {
        $filename = $this->buildFilename($quote, 'pdf');
        $binary = $this->buildPdfBinary($quote);

        return response()->streamDownload(
            static function () use ($binary): void {
                echo $binary;
            },
            $filename,
            [
                'Content-Type' => 'application/pdf',
            ]
        );
    }

    public function downloadExcel(Quote $quote): StreamedResponse
    {
        $quote = $this->prepareQuote($quote);
        $filename = $this->buildFilename($quote, 'xlsx');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Quote');

        $sheet->fromArray([
            ['報價單'],
            ['報價單號', $quote->quote_number],
            ['客戶名稱', $quote->customer_name],
            ['聯絡電話', $quote->contact_phone],
            ['聯絡信箱', $quote->contact_email],
            ['專案名稱', $quote->project_name],
            ['報價日期', optional($quote->quote_date)->format('Y-m-d')],
            ['有效期限', optional($quote->valid_until)->format('Y-m-d')],
            ['狀態', $quote->status],
            ['備註', $quote->notes],
            [],
            ['項次', '品項名稱', '描述', '數量', '單位', '單價', '小計'],
        ], null, 'A1');

        $startRow = 13;

        foreach ($quote->items as $index => $item) {
            $sheet->fromArray([
                [
                    $index + 1,
                    strip_tags((string) $item->name),
                    $item->description,
                    (int) $item->quantity,
                    $item->unit,
                    (float) $item->price,
                    (float) $item->amount,
                ],
            ], null, 'A' . ($startRow + $index));
        }

        $summaryRow = $startRow + max($quote->items->count(), 1) + 1;
        $sheet->fromArray([
            ['小計', (float) $quote->subtotal],
            ['稅額', (float) $quote->tax],
            ['折扣', (float) $quote->discount],
            ['總計', (float) $quote->total],
        ], null, 'F' . $summaryRow);

        foreach (range('A', 'G') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return response()->streamDownload(function () use ($spreadsheet): void {
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function buildFilename(Quote $quote, string $extension): string
    {
        $number = $quote->quote_number ?: ('quote_' . $quote->id);
        $number = preg_replace('/[^A-Za-z0-9_\-]/', '_', $number) ?: ('quote_' . $quote->id);

        return $number . '.' . $extension;
    }

    public function prepareQuote(Quote $quote): Quote
    {
        return $quote->loadMissing(['items', 'customer', 'creator', 'updater']);
    }
}
