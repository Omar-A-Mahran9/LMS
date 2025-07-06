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
            box-sizing: border-box;
            position: relative;
            text-align: center;
            overflow: hidden;
        }

        .content {
            padding: 40px 60px 80px;
            /* top, sides, bottom */
            position: relative;
            z-index: 1;
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
            top: 52%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 120px;
            color: #f0f0f0;
            font-weight: bold;
            white-space: nowrap;
            z-index: 0;
        }

        .title {
            font-size: 36px;
            font-weight: bold;
            margin-top: 80px;
            margin-bottom: 20px;
        }

        .sub-title {
            font-size: 20px;
            margin: 12px 0;
            color: #555;
        }

        .student-name {
            font-size: 30px;
            font-weight: bold;
            color: #00B2A9;
            margin: 10px 0;
        }

        .course-title {
            font-size: 22px;
            margin: 20px 0;
            font-style: italic;
        }

        .details {
            font-size: 14px;
            margin-top: 30px;
            color: #444;
        }

        .issued {
            font-size: 13px;
            margin-top: 15px;
            color: #666;
        }

        .signature {
            position: absolute;
            bottom: 30px;
            right: 50px;
            font-size: 14px;
            text-align: right;
        }

        .verify {
            position: absolute;
            bottom: 30px;
            left: 50px;
            font-size: 12px;
            color: #555;
            text-align: left;
        }
    </style>
</head>

<body>
    <div class="certificate">
        <div class="bg-text">CERTIFICATE</div>

        <div class="content">
            {{-- Logo --}}
            <div class="logo">
                <img src="{{ getImagePathFromDirectory(setting('logo_image'), 'Settings') }}" width="100"
                    alt="Mr-Mohamed Elnagar">
            </div>

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
            <div>Mohamed El-Nagar</div>
        </div>

        {{-- Verify link --}}

    </div>
</body>

</html>
