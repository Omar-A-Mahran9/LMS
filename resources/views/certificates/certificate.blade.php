<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Certificate of Completion</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }

        html, body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, sans-serif;
            width: 100%;
            height: 100%;
        }

        .certificate {
            width: 100%;
            height: 100%;
            padding: 20mm 25mm;
            box-sizing: border-box;
            border: 8px solid #00B2A9;
            position: relative;
            text-align: center;
        }

        .logo {
            position: absolute;
            top: 25mm;
            left: 25mm;
            width: 90px;
        }

        .qr {
            position: absolute;
            top: 25mm;
            right: 25mm;
            width: 90px;
        }

        .signature {
            position: absolute;
            bottom: 25mm;
            right: 25mm;
            font-size: 12px;
            text-align: right;
        }

        .verify {
            position: absolute;
            bottom: 25mm;
            left: 25mm;
            font-size: 11px;
            color: #555;
            text-align: left;
        }

        .bg-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 120px;
            color: #f0f0f0;
            font-weight: bold;
            z-index: 0;
            white-space: nowrap;
        }

        .content {
            position: relative;
            z-index: 1;
        }

        .title {
            font-size: 28px;
            font-weight: bold;
            margin-top: 100px;
        }

        .sub-title {
            font-size: 16px;
            margin: 10px 0;
        }

        .student-name {
            font-size: 24px;
            font-weight: bold;
            color: #00B2A9;
            margin: 10px 0;
        }

        .course-title {
            font-size: 18px;
            margin: 15px 0;
        }

        .details {
            font-size: 13px;
            margin-top: 20px;
        }

        .issued {
            font-size: 12px;
            margin-top: 15px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="certificate">
        <div class="bg-text">CERTIFICATE</div>

        <div class="content">
            {{-- Logo --}}
            <img src="{{ asset(getImagePathFromDirectory(setting('logo_image'), 'Settings')) }}" class="logo" alt="Logo">

            {{-- QR Code --}}
            <img src="data:image/png;base64,{{ $qrCode }}" class="qr" alt="QR Code">

            {{-- Main Text --}}
            <div class="title">Certificate of Completion</div>
            <div class="sub-title">This certifies that</div>

            <div class="student-name">{{ $student->name }}</div>

            <div class="sub-title">has successfully completed the course</div>

            <div class="course-title">"{{ $course->title_en ?? $course->title }}"</div>

            <div class="details">
                Certificate ID: {{ $certificateId }}<br>
                Student Email: {{ $student->email }}
            </div>

            <div class="issued">
                Issued on: {{ now()->format('F d, Y') }}
            </div>
        </div>

        {{-- Instructor --}}
        <div class="signature">
            <div class="name">Instructor Name</div>
            <div>Course Instructor</div>
        </div>

        {{-- Verify link --}}
        <div class="verify">
            Verify at:<br>
            {{ $certificateUrl }}
        </div>
    </div>
</body>
</html>
