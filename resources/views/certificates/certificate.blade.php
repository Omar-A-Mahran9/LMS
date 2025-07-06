<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Certificate</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }

        html, body {
            margin: 0;
            padding: 0;
            width: 1122px;
            height: 793px;
            font-family: DejaVu Sans, sans-serif;
        }

        .certificate {
            width: 100%;
            height: 100%;
            box-sizing: border-box;
            position: relative;
            text-align: center;
            padding: 40px 60px;
            border: 8px solid #00B2A9;
            overflow: hidden;
        }

        .logo {
            position: absolute;
            top: 30px;
            left: 40px;
            width: 100px;
            height: 100px;
            background-image: url('{{ asset(getImagePathFromDirectory(setting('logo_image'), 'Settings')) }}');
            background-repeat: no-repeat;
            background-size: contain;
            background-position: center;
        }

        .qr {
            position: absolute;
            top: 30px;
            right: 40px;
            width: 100px;
            height: 100px;
        }

        .bg-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 110px;
            color: #f0f0f0;
            font-weight: bold;
            white-space: nowrap;
            z-index: 0;
        }

        .content {
            position: relative;
            z-index: 1;
            margin-top: 100px;
        }

        .title {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .sub-title {
            font-size: 18px;
            margin: 8px 0;
            color: #555;
        }

        .student-name {
            font-size: 28px;
            font-weight: bold;
            color: #00B2A9;
            margin: 10px 0;
        }

        .course-title {
            font-size: 20px;
            margin: 15px 0;
            font-style: italic;
        }

        .details {
            font-size: 14px;
            margin-top: 20px;
            color: #444;
        }

        .issued {
            font-size: 13px;
            margin-top: 10px;
            color: #666;
        }

        .signature {
            position: absolute;
            bottom: 30px;
            right: 50px;
            font-size: 13px;
            text-align: right;
            color: #444;
        }

        .verify {
            position: absolute;
            bottom: 30px;
            left: 50px;
            font-size: 12px;
            text-align: left;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="certificate">
        <div class="bg-text">CERTIFICATE</div>

        <div class="logo"></div>

        <img class="qr" src="data:image/png;base64,{{ $qrCode }}" alt="QR">

        <div class="content">
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

        <div class="signature">
            Instructor Name<br>
            Course Instructor
        </div>

        <div class="verify">
            Verify at:<br>
            {{ $certificateUrl }}
        </div>
    </div>
</body>
</html>
