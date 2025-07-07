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

        html,
        body {
            margin: 0;
            padding: 0;
            font-family: 'DejaVu Sans', sans-serif;
            width: 100%;
            height: 100%;
            background: #fff;
        }

        .certificate {
            width: 100%;
            height: 100%;
            position: relative;
            box-sizing: border-box;
            overflow: hidden;
            text-align: center;
        }

        .content {
            padding: 40px 60px 80px;
            position: relative;
            z-index: 1;
        }

        .logo {
            margin-bottom: 20px;
        }

        .logo img {
            height: 80px;
        }

        .qr {
            position: absolute;
            top: 30px;
            right: 40px;
            width: 90px;
            height: 90px;
        }

        .bg-text {
            position: absolute;
            top: 52%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 110px;
            color: #f2f2f2;
            font-weight: bold;
            white-space: nowrap;
            z-index: 0;
            letter-spacing: 4px;
        }

        .title {
            font-size: 38px;
            font-weight: 700;
            margin-top: 60px;
            margin-bottom: 20px;
            color: #222;
        }

        .sub-title {
            font-size: 18px;
            margin: 12px 0;
            color: #555;
        }

        .student-name {
            font-size: 32px;
            font-weight: bold;
            color: #008080;
            margin: 10px 0;
        }

        .course-title {
            font-size: 22px;
            font-style: italic;
            margin: 25px 0;
            color: #333;
        }

        .details {
            font-size: 14px;
            margin-top: 30px;
            color: #444;
            line-height: 1.6;
        }

        .issued {
            font-size: 13px;
            margin-top: 20px;
            color: #666;
        }

        .signature {
            position: absolute;
            bottom: 40px;
            right: 50px;
            font-size: 14px;
            text-align: right;
            color: #333;
        }

        .signature .name {
            font-weight: bold;
            font-size: 16px;
            margin-bottom: 2px;
        }

        .verify {
            position: absolute;
            bottom: 40px;
            left: 50px;
            font-size: 12px;
            color: #555;
            text-align: left;
        }

        .verify a {
            color: #006699;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="certificate">
        {{-- Background watermark --}}
        <div class="bg-text">CERTIFICATE</div>

        <div class="content">
            {{-- Logo --}}
            <div class="logo">
                <img src="{{ getImagePathFromDirectory(setting('logo_image'), 'Settings') }}" alt="Logo">
            </div>

            {{-- QR Code --}}
            <img src="data:image/png;base64,{{ $qrCode }}" class="qr" alt="QR Code">

            {{-- Title --}}
            <div class="title">Certificate of Completion</div>
            <div class="sub-title">This is proudly presented to</div>

            {{-- STUDENT NAME --}}
            <div class="student-name">{{ $student->name }}</div>

            <div class="sub-title">for successfully completing the course</div>

            {{-- COURSE TITLE --}}
            <div class="course-title">"{{ $course->title_en ?? $course->title }}"</div>

            {{-- DETAILS --}}
            <div class="details">
                Certificate ID: <strong>{{ $certificateId }}</strong><br>
                Student Email: <strong>{{ $student->email }}</strong>
            </div>

            {{-- ISSUE DATE --}}
            <div class="issued">
                Issued on: <strong>{{ now()->format('F d, Y') }}</strong>
            </div>
        </div>

        {{-- INSTRUCTOR SIGNATURE --}}
        <div class="signature">
            <div class="name">Mohamed El-Nagar</div>
            <div>Instructor</div>
        </div>

        {{-- VERIFICATION LINK --}}
        <div class="verify">
            Verify this certificate at:<br>
            <a href="https://yourdomain.com/certificates/{{ $certificateId }}">
                yourdomain.com/certificates/{{ $certificateId }}
            </a>
        </div>
    </div>
</body>

</html>
