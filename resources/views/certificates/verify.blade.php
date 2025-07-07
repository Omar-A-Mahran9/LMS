<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Certificate Verification') }} - {{ $certificate->certificate_id }}</title>

    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --background: #f4f6f9;
            --card-bg: #ffffff;
            --text-color: #333;
            --border-radius: 8px;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--background);
            margin: 0;
            padding: 0;
            color: var(--text-color);
        }

        .container {
            max-width: 600px;
            margin: 60px auto;
            background: var(--card-bg);
            padding: 40px 30px;
            border-radius: var(--border-radius);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: var(--primary-color);
            font-size: 28px;
            text-align: center;
            margin-bottom: 30px;
        }

        .info {
            margin-top: 20px;
        }

        .info p {
            font-size: 17px;
            margin: 12px 0;
            line-height: 1.6;
        }

        .info p strong {
            color: var(--secondary-color);
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            font-size: 15px;
            color: #888;
        }

        @media (max-width: 640px) {
            .container {
                margin: 30px 15px;
                padding: 25px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>{{ __('Certificate Verification') }}</h1>

        <div class="info">
            <p><strong>{{ __('Certificate ID') }}:</strong> {{ $certificate->certificate_id }}</p>
            <p><strong>{{ __('Student Name') }}:</strong> {{ $certificate->student->name ?? __('N/A') }}</p>
            <p><strong>{{ __('Course Title') }}:</strong> {{ $certificate->course->title ?? __('N/A') }}</p>
            <p><strong>{{ __('Date Issued') }}:</strong> {{ $certificate->created_at->format('Y-m-d') }}</p>
        </div>

        <div class="footer">
            {{ __('Thank you for learning with us!') }}
        </div>
    </div>
</body>
</html>
