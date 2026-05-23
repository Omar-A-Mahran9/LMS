<!DOCTYPE html>
<html lang="ar">

<head>
    <meta charset="UTF-8">
    <style>
        @font-face {
            font-family: 'Cairo';
            font-style: normal;
            font-weight: 400;
            src: url('{{ public_path('fonts/Cairo-Regular.ttf') }}') format('truetype');
        }

        @font-face {
            font-family: 'Cairo';
            font-style: normal;
            font-weight: 700;
            src: url('{{ public_path('fonts/Cairo-Bold.ttf') }}') format('truetype');
        }

        body {
            font-family: 'Cairo', sans-serif;
            direction: rtl;
            text-align: right;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #444;
            padding: 8px;
        }

        th {
            background-color: #eee;
        }
    </style>
    <title>تقرير أكواد الدخول</title>
</head>

<body>

    <h2 style="text-align:center;">تقرير أكواد الدخول</h2>

    <table>
        <thead>
            <tr>
                <th>م</th>
                <th>الكود</th>
                <th>الرابط</th>
                <th>الصف</th>
                <th>الحد الأقصى للاستخدام</th>
                <th>استخدام لمرة واحدة</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($codes as $index => $code)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $code->code }}</td>
                    <td>https://mohamed-elnagar.com/classes-by-code</td>
                    <td>{{ $code->class->title_ar ?? '-' }}</td>
                    <td>{{ $code->usage_limit ?? 'غير محدود' }}</td>
                    <td>{{ $code->single_use ? 'نعم' : 'لا' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">لا توجد أكواد.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>

</html>
