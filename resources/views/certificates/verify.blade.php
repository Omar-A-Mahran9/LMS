<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Certificate Verification - {{ $certificate->certificate_id }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; }
        h1 { color: #2c3e50; }
        .info { margin-top: 20px; }
        .info p { font-size: 16px; margin: 5px 0; }
    </style>
</head>
<body>
    <h1>Certificate Verification</h1>

    <div class="info">
        <p><strong>Certificate ID:</strong> {{ $certificate->certificate_id }}</p>
        <p><strong>Student Name:</strong> {{ $certificate->student->name ?? 'N/A' }}</p>
        <p><strong>Course Title:</strong> {{ $certificate->course->title ?? 'N/A' }}</p>
        <p><strong>Date Issued:</strong> {{ $certificate->created_at->format('Y-m-d') }}</p>
    </div>

    <div style="margin-top: 30px;">
        <p>Thank you for learning with us!</p>
    </div>
</body>
</html>
