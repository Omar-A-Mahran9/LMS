@php
    // Shared by the class codes and the bundle codes exports
    $layout = $layout ?? 'table';
    $title = $title ?? 'تقرير أكواد الدخول';
    $filters = $filters ?? [];
    $url = $url ?? \App\Http\Controllers\Dashboard\GenerateCodeController::CODES_URL;

    $statusLabels = [
        'unused' => 'غير مستخدم',
        'used' => 'مستخدم جزئياً',
        'exhausted' => 'مستنفد',
        'inactive' => 'متوقف',
    ];

    // dompdf neither joins Arabic letters nor orders right-to-left text: ArPHP gives it in visual order
    $arabic = new \ArPHP\I18N\Arabic();
    // 200: one line per text; false: Western digits (0-9), like the codes themselves
    $rtl = fn(?string $text): string => $arabic->utf8Glyphs((string) $text, 200, false);

    $itemTitle = fn($code) => $rtl($code->class->title_ar ?? ($code->bundle->title_ar ?? '-'));
    $itemSubtitle = fn($code) => $code->class?->course?->title_ar ? $rtl($code->class->course->title_ar) : null;
    $uses = fn($code) => $code->single_use
        ? 'صالح لاستخدام واحد'
        : ($code->usage_limit ? 'صالح لـ ' . $code->usage_limit . ' استخدامات' : 'استخدام غير محدود');
@endphp
<!DOCTYPE html>
<html lang="ar">

<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        @font-face {
            font-family: 'Cairo';
            font-weight: 400;
            src: url('{{ storage_path('fonts/Cairo-Regular.ttf') }}') format('truetype');
        }

        @font-face {
            font-family: 'Cairo';
            font-weight: 700;
            src: url('{{ storage_path('fonts/Cairo-Bold.ttf') }}') format('truetype');
        }

        @page {
            margin: 22px 26px 40px;
        }

        /* ltr on purpose: the Arabic text is already in visual order (see $rtl) */
        body {
            font-family: 'Cairo', sans-serif;
            text-align: right;
            color: #1f2937;
            font-size: 11px;
        }

        .code {
            font-family: 'DejaVu Sans Mono', monospace;
            font-weight: bold;
            letter-spacing: 2px;
        }

        .muted {
            color: #6b7280;
        }

        /* Header */
        .header {
            background: #1f4e46;
            color: #fff;
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 18px;
        }

        .header td {
            color: #fff;
            vertical-align: middle;
        }

        .meta {
            width: 100%;
            margin-bottom: 12px;
        }

        .chip {
            display: inline-block;
            background: #eef4f3;
            color: #1f4e46;
            border-radius: 10px;
            padding: 2px 10px;
            margin-left: 4px;
            font-size: 10px;
        }

        /* Table layout */
        table.report {
            width: 100%;
            border-collapse: collapse;
        }

        table.report th {
            background: #f3f4f6;
            color: #374151;
            font-size: 10px;
            padding: 7px 6px;
            border-bottom: 2px solid #1f4e46;
            text-align: right;
        }

        table.report td {
            padding: 6px;
            text-align: right;
            white-space: nowrap;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
        }

        table.report tr:nth-child(even) td {
            background: #fafafa;
        }

        table.report tr {
            page-break-inside: avoid;
        }

        table.report .code {
            font-size: 13px;
        }

        .badge {
            white-space: nowrap;
            font-size: 9px;
            padding: 1px 8px;
            border-radius: 8px;
        }

        .badge-unused {
            background: #dcfce7;
            color: #166534;
        }

        .badge-used {
            background: #e0f2fe;
            color: #075985;
        }

        .badge-exhausted {
            background: #f3f4f6;
            color: #4b5563;
        }

        .badge-inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Cards layout: cut along the dashed lines */
        table.cards {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px;
        }

        table.cards tr {
            page-break-inside: avoid;
        }

        table.cards td.card {
            width: 50%;
            border: 1.5px dashed #9ca3af;
            border-radius: 8px;
            padding: 10px 12px;
            text-align: center;
            vertical-align: top;
        }

        .card .item {
            font-weight: bold;
            font-size: 12px;
        }

        .card .code {
            display: block;
            font-size: 20px;
            color: #1f4e46;
            background: #eef4f3;
            border-radius: 6px;
            padding: 6px 0;
            margin: 6px 0;
        }

        .card .how {
            font-size: 9px;
        }

        .footer {
            position: fixed;
            bottom: -26px;
            left: 0;
            right: 0;
            font-size: 9px;
            color: #9ca3af;
            text-align: center;
        }
    </style>
</head>

<body>

    <div class="footer">{{ $url }} — {{ now()->format('Y-m-d H:i') }}</div>

    <div class="header">
        <table width="100%">
            <tr>
                <td style="text-align:left">
                    <div>{{ $rtl('رابط التفعيل') }}</div>
                    <div class="code" style="font-size:10px;letter-spacing:0">{{ $url }}</div>
                </td>
                <td style="text-align:right">
                    <h1>{{ $rtl($title) }}</h1>
                    <div><strong>{{ $codes->count() }}</strong> {{ $rtl('عدد الأكواد:') }}</div>
                </td>
            </tr>
        </table>
    </div>

    @if ($filters)
        <div class="meta" style="text-align:right">
            @foreach (array_reverse($filters, true) as $label => $value)
                <span class="chip">{{ $rtl($label . ': ' . $value) }}</span>
            @endforeach
        </div>
    @endif

    @if ($codes->isEmpty())
        <p style="text-align:center;padding:40px" class="muted">{{ $rtl('لا توجد أكواد.') }}</p>
    @elseif ($layout === 'cards')
        <table class="cards">
            @foreach ($codes->chunk(2) as $pair)
                <tr>
                    @if ($pair->count() < 2)
                        <td></td>
                    @endif
                    @foreach ($pair->reverse() as $code)
                        <td class="card">
                            <div class="item">{{ $itemTitle($code) }}</div>
                            @if ($itemSubtitle($code))
                                <div class="muted" style="font-size:9px">{{ $itemSubtitle($code) }}</div>
                            @endif
                            <span class="code">{{ $code->code }}</span>
                            <div class="how muted">{{ $rtl('واكتب الكود') }}&nbsp;<span class="code" style="display:inline;font-size:9px;letter-spacing:0;background:none;padding:0;margin:0">{{ $url }}</span>&nbsp;{{ $rtl('ادخل على') }}</div>
                            <div class="how muted">{{ $rtl($uses($code)) }}</div>
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </table>
    @else
        <table class="report">
            <thead>
                <tr>
                    <th style="width:80px">{{ $rtl('الحالة') }}</th>
                    <th style="width:60px">{{ $rtl('الاستخدام') }}</th>
                    <th>{{ $rtl('الحصة / الباقة') }}</th>
                    <th>{{ $rtl('الكود') }}</th>
                    <th style="width:28px">{{ $rtl('م') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($codes->values() as $index => $code)
                    @php
                        $max = $code->single_use ? 1 : $code->usage_limit;
                        $status = $code->status ?? ($code->is_active ? 'unused' : 'inactive');
                    @endphp
                    <tr>
                        <td><span class="badge badge-{{ $status }}">{{ $rtl($statusLabels[$status] ?? $status) }}</span></td>
                        <td style="text-align:center">{{ $code->used_count }} / {{ $max ?? '∞' }}</td>
                        <td>
                            {{ $itemTitle($code) }}
                            @if ($itemSubtitle($code))
                                <div class="muted" style="font-size:9px">{{ $itemSubtitle($code) }}</div>
                            @endif
                        </td>
                        <td><span class="code">{{ $code->code }}</span></td>
                        <td class="muted">{{ $index + 1 }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

</body>

</html>
