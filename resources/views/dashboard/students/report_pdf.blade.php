<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Student Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; line-height: 1.6; }
        h2, h3 { border-bottom: 1px solid #ccc; padding-bottom: 5px; }
    </style>
</head>
<body>
    <h2>{{ __('Student Report') }}</h2>

    <h3>{{ __('Basic Info') }}</h3>
    <p><strong>{{ __('Name') }}:</strong> {{ $student->first_name }} {{ $student->last_name }}</p>
    <p><strong>{{ __('Email') }}:</strong> {{ $student->email }}</p>
    <p><strong>{{ __('Phone') }}:</strong> {{ $student->phone }}</p>

    <h3>{{ __('Courses') }}</h3>
    @foreach ($student->courses as $course)
        <p>- {{ $course->title_ar ?? $course->title_en }} ({{ $course->pivot->payment_type }})</p>
    @endforeach

    <h3>{{ __('Quizzes') }}</h3>
    <p>{{ __('Average Score') }}: {{ $quizStats['average_score'] }}%</p>
    @foreach ($student->quizAttempts as $attempt)
        <p>- {{ $attempt->quiz->title_ar ?? $attempt->quiz->title_en }}: {{ $attempt->score }}%</p>
    @endforeach

    <h3>{{ __('Homeworks') }}</h3>
    <p>{{ __('Submitted') }}: {{ $homeworkStats['submitted'] }} / {{ $homeworkStats['count'] }}</p>
    @foreach ($student->homeworks as $hw)
        <p>- {{ $hw->homework->title_ar ?? $hw->homework->title_en }}:
            {{ $hw->submitted_at ? __('Submitted') : __('Not Submitted') }}
        </p>
    @endforeach
</body>
</html>
