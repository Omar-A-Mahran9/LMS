<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>رمز التحقق</title>
    <style>
        body {
            font-family: 'Tahoma', Arial, sans-serif;
            background-color: #f5f5f5;
            padding: 30px;
            direction: rtl;
            text-align: right;
        }

        .email-container {
            max-width: 600px;
            margin: auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 8px rgba(0,0,0,0.06);
            border-top: 6px solid #1D2963;
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo img {
            max-height: 70px;
        }

        h2, p {
            color: #1D2963;
        }

        .otp {
            font-size: 34px;
            font-weight: bold;
            color: #ffffff;
            background-color: #2C3E94;
            padding: 16px;
            text-align: center;
            border-radius: 8px;
            margin: 24px 0;
            letter-spacing: 5px;
        }

        .warning {
            color: #FFC107;
            font-weight: bold;
            margin-top: 20px;
        }

        .footer {
            margin-top: 40px;
            font-size: 14px;
            color: #007771;
            text-align: right;
        }
    </style>
</head>
<body>

    <div class="email-container">

        {{-- الشعار --}}
        <div class="logo">
            <img src="{{ getImagePathFromDirectory(setting('logo_image') ?? 'default-logo.png', 'Settings') }}" alt="شعار المنصة">
        </div>

        {{-- نص الترحيب --}}
        <p>مرحبًا {{ $student->name ?? 'عزيزي المستخدم' }}،</p>

        <p>لقد حاولت تسجيل الدخول إلى حسابك. رمز التحقق الخاص بك هو:</p>

        {{-- رمز التحقق --}}
        <div class="otp">{{ $otp }}</div>

        <p>يرجى استخدام هذا الرمز خلال بضع دقائق فقط لإتمام عملية تسجيل الدخول.</p>

        {{-- التحذير --}}
        <p class="warning">⚠️ لا تشارك هذا الرمز مع أي شخص حفاظًا على أمان حسابك.</p>

        {{-- التوقيع --}}
        <div class="footer">
            مع تحياتنا،<br>
            فريق {{ config('app.name', 'LMS') }}
        </div>

    </div>

</body>
</html>
