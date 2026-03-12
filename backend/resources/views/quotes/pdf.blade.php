<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <style>
        @font-face {
            font-family: 'MicrosoftJhengHei';
            src: url('file:///C:/Windows/Fonts/msjh.ttc') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        body {
            font-family: 'MicrosoftJhengHei', DejaVu Sans, sans-serif;
            color: #1f2937;
            font-size: 12px;
        }

        .header {
            margin-bottom: 24px;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .meta-table,
        .items-table,
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-table td {
            padding: 4px 0;
            vertical-align: top;
        }

        .items-table {
            margin-top: 18px;
        }

        .items-table th,
        .items-table td,
        .summary-table td {
            border: 1px solid #d1d5db;
            padding: 8px;
        }

        .items-table th {
            background: #f3f4f6;
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .summary-table {
            margin-top: 18px;
        }

        .notes {
            margin-top: 24px;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">報價單</div>
        <table class="meta-table">
            <tr>
                <td width="18%">報價單號</td>
                <td width="32%">{{ $quote->quote_number }}</td>
                <td width="18%">報價日期</td>
                <td width="32%">{{ optional($quote->quote_date)->format('Y-m-d') }}</td>
            </tr>
            <tr>
                <td>客戶名稱</td>
                <td>{{ $quote->customer_name }}</td>
                <td>聯絡電話</td>
                <td>{{ $quote->contact_phone ?: '-' }}</td>
            </tr>
            <tr>
                <td>聯絡信箱</td>
                <td>{{ $quote->contact_email ?: '-' }}</td>
                <td>專案名稱</td>
                <td>{{ $quote->project_name ?: '-' }}</td>
            </tr>
            <tr>
                <td>有效期限</td>
                <td>{{ optional($quote->valid_until)->format('Y-m-d') ?: '-' }}</td>
                <td>狀態</td>
                <td>{{ $quote->status }}</td>
            </tr>
        </table>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th width="6%">#</th>
                <th width="28%">品項</th>
                <th width="20%">描述</th>
                <th width="10%" class="text-right">數量</th>
                <th width="10%">單位</th>
                <th width="13%" class="text-right">單價</th>
                <th width="13%" class="text-right">小計</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{!! nl2br(e(strip_tags((string) $item->name))) !!}</td>
                    <td>{{ $item->description ?: '-' }}</td>
                    <td class="text-right">{{ number_format((float) $item->quantity, 0) }}</td>
                    <td>{{ $item->unit }}</td>
                    <td class="text-right">{{ number_format((float) $item->price, 2) }}</td>
                    <td class="text-right">{{ number_format((float) $item->amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="summary-table">
        <tr>
            <td width="80%" class="text-right">小計</td>
            <td width="20%" class="text-right">{{ number_format($subtotal, 2) }}</td>
        </tr>
        <tr>
            <td class="text-right">稅額</td>
            <td class="text-right">{{ number_format($tax, 2) }}</td>
        </tr>
        <tr>
            <td class="text-right">折扣</td>
            <td class="text-right">{{ number_format($discount, 2) }}</td>
        </tr>
        <tr>
            <td class="text-right"><strong>總計</strong></td>
            <td class="text-right"><strong>{{ number_format($total, 2) }}</strong></td>
        </tr>
    </table>

    @if(!empty($quote->notes))
        <div class="notes">
            <strong>備註</strong>
            <div>{{ $quote->notes }}</div>
        </div>
    @endif
</body>
</html>
