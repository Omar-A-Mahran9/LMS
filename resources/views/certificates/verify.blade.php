<!DOCTYPE html>
<html>

<head>
    <title>Certificate Verification</title>
</head>

<body>
    <h1>Certificate Verification</h1>
    <p>Certificate ID: {{ $certificate->certificate_id }}</p>
    <p>Student: {{ $certificate->student->name ?? 'N/A' }}</p>
    <p>Course: {{ $certificate->course->title ?? 'N/A' }}</p>
    <!-- Add more details as needed -->
</body>

</html>
