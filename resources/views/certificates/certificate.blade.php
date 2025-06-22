<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>{{ __('Course Certificate') }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 0;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 0;
            padding: 0;
            background: #fdfdfd;
            color: #333;
        }

        .certificate {
            width: 100%;
            height: 100%;
            padding: 60px;
            box-sizing: border-box;
            border: 12px solid #00B2A9;
            background: repeating-linear-gradient(45deg,
                    #fdfdfd,
                    #fdfdfd 20px,
                    #e6f9f9 20px,
                    #e6f9f9 40px);
            /* optional pattern */
            position: relative;
            text-align: center;
        }



        .logo {
            width: 120px;
            position: absolute;
            top: 40px;
            left: 60px;
        }

        .qr {
            position: absolute;
            bottom: 40px;
            right: 60px;
            width: 100px;
        }

        h1 {
            font-size: 48px;
            margin-top: 30px;
            margin-bottom: 20px;
            color: #00B2A9;
        }

        .sub-title {
            font-size: 22px;
            font-weight: 500;
            margin-bottom: 40px;
        }

        .name {
            font-size: 36px;
            font-weight: bold;
            color: #111;
            margin-bottom: 15px;
        }

        .course {
            font-size: 26px;
            margin: 10px 0;
            color: #444;
        }

        .details {
            font-size: 16px;
            color: #666;
            margin-top: 30px;
            line-height: 1.6;
        }

        .date {
            font-size: 18px;
            margin-top: 25px;
            color: #444;
        }

        /* Hide download button in PDF view, show on screen */
        .download-button {
            display: none;
        }

        @media screen {
            body {
                background: #f0f0f0;
                padding: 30px;
            }

            .certificate {
                box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
                margin: auto;
                max-width: 90%;
            }

            .download-button {
                display: block;
                text-align: center;
                margin-top: 40px;
            }

            .download-button button {
                padding: 12px 24px;
                background-color: #00B2A9;
                color: white;
                border: none;
                border-radius: 4px;
                font-size: 16px;
                cursor: pointer;
                transition: background 0.3s ease;
            }

            .download-button button:hover {
                background-color: #008f86;
            }
        }
    </style>
</head>

<body>
    <div class="certificate">
        <img src="{{ asset('images/logo.png') }}" class="logo" alt="Logo">

        <h1>{{ __('Certificate of Completion') }}</h1>
        <p class="sub-title">{{ __('This is to certify that') }}</p>

        <div class="name">{{ $student->name }}</div>

        <p class="course">{{ __('has successfully completed the course') }}:</p>
        <div class="course">"{{ $course->title }}"</div>

        <div class="details">
            {{ __('Certificate ID') }}: CERT-{{ $student->id }}-{{ $course->id }}<br>
            {{ __('Student Email') }}: {{ $student->email }}
        </div>

        <div class="date">
            {{ __('Issued on') }}: {{ now()->format('F d, Y') }}
        </div>

        <img src="data:image/png;base64, {!! base64_encode(QrCode::size(100)->generate(url()->current())) !!}" class="qr" alt="QR Code">
    </div>

    <div class="download-button">
        <form method="GET" action="{{ route('certificate.download', $course->id) }}">
            <button type="submit">{{ __('Download PDF') }}</button>
        </form>
    </div>
</body>

</html>
