<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <title>رمز التحقق لتسجيل الدخول</title>
    <style>
        body {
            font-family: 'Tahoma', Arial, sans-serif;
            background-color: #f7f7f7;
            padding: 20px;
            direction: rtl;
            text-align: right;
        }

        .container {
            max-width: 600px;
            margin: auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .otp-code {
            font-size: 32px;
            font-weight: bold;
            color: #2c3e50;
            background-color: #e8f0fe;
            padding: 15px;
            text-align: center;
            border-radius: 8px;
            margin: 20px 0;
            letter-spacing: 4px;
        }

        .note {
            color: #e74c3c;
            font-weight: bold;
            margin-top: 10px;
        }

        .footer {
            margin-top: 30px;
            font-size: 13px;
            color: #999;
            text-align: center;
        }
    </style>
</head>

<body>

    <div class="container">
        <h2>مرحبًا {{ $student->name ?? 'عزيزي المستخدم' }}،</h2>

        <p>لقد حاولت تسجيل الدخول إلى حسابك. رمز التحقق الخاص بك هو:</p>

        <div class="otp-code">{{ $otp }}</div>

        <p>يرجى استخدام هذا الرمز خلال بضع دقائق فقط لإتمام عملية تسجيل الدخول.</p>

        <p class="note">⚠️ لا تشارك هذا الرمز مع أي شخص حفاظًا على أمان حسابك.</p>

        <div class="footer">
            مع تحياتنا،<br>
            فريق {{ config('app.name') }}
        </div>
    </div>

</body>

</html>
