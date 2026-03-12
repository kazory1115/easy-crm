<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>{{ $subjectLine }}</title>
</head>
<body style="font-family: Arial, 'Microsoft JhengHei', sans-serif; color: #1f2937; line-height: 1.6;">
    <p>您好，</p>

    <p>附件為報價單 <strong>{{ $quote->quote_number }}</strong>。</p>

    @if(!empty($messageBody))
        <div style="margin: 16px 0; padding: 12px 16px; background: #f8fafc; border-left: 4px solid #2563eb; white-space: pre-wrap;">{{ $messageBody }}</div>
    @endif

    <table style="border-collapse: collapse; margin-top: 16px;">
        <tr>
            <td style="padding: 4px 12px 4px 0;">客戶</td>
            <td style="padding: 4px 0;">{{ $quote->customer_name }}</td>
        </tr>
        <tr>
            <td style="padding: 4px 12px 4px 0;">專案</td>
            <td style="padding: 4px 0;">{{ $quote->project_name ?: '-' }}</td>
        </tr>
        <tr>
            <td style="padding: 4px 12px 4px 0;">報價日期</td>
            <td style="padding: 4px 0;">{{ optional($quote->quote_date)->format('Y-m-d') }}</td>
        </tr>
        <tr>
            <td style="padding: 4px 12px 4px 0;">總計</td>
            <td style="padding: 4px 0;">NT$ {{ number_format((float) $quote->total, 2) }}</td>
        </tr>
    </table>

    <p style="margin-top: 24px;">Easy CRM</p>
</body>
</html>
