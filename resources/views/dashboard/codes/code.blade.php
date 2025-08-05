<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Access Codes Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 12px;
        }

        th,
        td {
            border: 1px solid #444;
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #eee;
        }
    </style>
</head>

<body>

    <h2 style="text-align:center;">Access Codes Report</h2>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Code</th>
                <th>URL</th>
                <th>Class</th>
                <th>Usage Limit</th>
                <th>Single Use</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($codes as $index => $code)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $code->code }}</td>
                    <td>"https://mohamed-elnagar.com/classes-by-code"</td>
                    <td>{{ $code->class->title ?? '-' }}</td>

                </tr>
            @empty
                <tr>
                    <td colspan="8">No codes found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>

</html>
