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
            font-family: DejaVu Sans, sans-serif;
            width: 100%;
            height: 100%;
        }

        .certificate {
            width: 100%;
            height: 100%;
            max-width: 100%;
            max-height: 100%;
            padding: 20mm 25mm;
            box-sizing: border-box;
            border: 8px solid #00B2A9;
            position: relative;
            text-align: center;
        }

        .logo {
            position: absolute;
            top: 30mm;
            left: 25mm;
            width: 90px;
        }

        .qr {
            position: absolute;
            top: 30mm;
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
    </style>

</head>

<body>
    <div class="certificate">
        <div class="bg-text">CERTIFICATE</div>
        <div class="content">
            <img src="{{ public_path('images/logo.png') }}" class="logo" alt="Logo">
            <img src="data:image/png;base64, {!! base64_encode(QrCode::format('png')->size(100)->generate('https://example.com/certificate/1234')) !!}" class="qr" alt="QR Code">

            <div class="title">Certificate of Completion</div>
            <div class="sub-title">This certifies that</div>
            <div class="student-name">John Doe</div>
            <div class="sub-title">has successfully completed the course</div>
            <div class="course-title">"Introduction to Web Development"</div>

            <div class="details">
                Certificate ID: CERT-1234-5678<br>
                Student Email: johndoe@example.com
            </div>

            <div class="issued">Issued on: {{ now()->format('F d, Y') }}</div>
        </div>

        <div class="signature">
            <div class="name">Thomas Thorsell-Arntsen</div>
            <div>Course Instructor</div>
        </div>

        <div class="verify">
            Verify at:<br>
            https://example.com/certificate/1234
        </div>
    </div>
</body>

</html>
