<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Course Certificate</title>
    <style>
        body {
            text-align: center;
            font-family: DejaVu Sans, sans-serif;
            padding: 50px;
        }

        .certificate {
            border: 10px solid #3490dc;
            padding: 50px;
            margin: 0 auto;
            width: 90%;
            height: 90%;
        }

        h1 {
            font-size: 40px;
            margin-bottom: 20px;
        }

        .name {
            font-size: 30px;
            font-weight: bold;
        }

        .course {
            font-size: 24px;
        }

        .date {
            margin-top: 30px;
            font-size: 18px;
        }
    </style>
</head>

<body>
    <div class="certificate">
        <h1>Certificate of Completion</h1>
        <p>This certificate is proudly presented to</p>
        <div class="name">{{ $student->name }}</div>
        <p>for successfully completing the course:</p>
        <div class="course">{{ $course->title }}</div>
        <div class="date">Date: {{ now()->format('F d, Y') }}</div>
    </div>
</body>

</html>
