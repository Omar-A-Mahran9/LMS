<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
 use App\Http\Resources\Api\StudentResource;
use App\Http\Resources\Api\UpdateStudentProfileRequest;
use App\Models\HomeWork;
use App\Models\Quiz;
use App\Rules\PasswordNumberAndLetter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
public function profileInfo(Request $request)
{
    $student = $request->user();
    return $this->success('',new StudentResource($student));
}
public function logout(Request $request)
{
    $request->user()->currentAccessToken()->delete();

    return $this->success(null, 'تم تسجيل الخروج بنجاح');
}
public function logoutFromAllDevices(Request $request)
{
    $user = $request->user();
    $currentTokenId = $user->currentAccessToken()->id;

    // Delete all tokens except the current one
    $user->tokens()->where('id', '!=', $currentTokenId)->delete();

    return $this->success(null, 'تم تسجيل الخروج من جميع الأجهزة ماعدا هذا الجهاز.');
}

public function updatePassword(Request $request)
{
    $request->validate([
        'old_password' => ['required'],
        'password' => ['required', 'min:6', new PasswordNumberAndLetter()],
        'password_confirmation' => ['required_with:password', 'same:password'],
    ]);

    $student = $request->user();

    if (!Hash::check($request->old_password, $student->password)) {
        return $this->validationFailure([
            'old_password' => [__('The old password is incorrect.')],
        ]);
    }

    $student->update([
        'password' => $request->password,
    ]);

  $user = $request->user();
    $currentTokenId = $user->currentAccessToken()->id;

    // Delete all tokens except the current one
    $user->tokens()->where('id', '!=', $currentTokenId)->delete();

    return $this->success(__('Password updated successfully. All other sessions have been logged out.'));
}
public function updateProfileInfo(UpdateStudentProfileRequest $request)
{
    $student = $request->user();

    $data=$request->validated();
     if ($request->hasFile('image')) {
        deleteImageFromDirectory($student->image, 'Students');
        $data['image'] = uploadImageToDirectory($request->file('image'), 'Students');
    }

    $student->update($data);

    return $this->success(__('Profile updated successfully'), new StudentResource($student));
}


public function quizzesResults(Request $request)
{
    $studentId = auth()->id();

    $quizzes = Quiz::with([
            'course:id,title_ar,title_en,is_class',
            'questions:id,quiz_id,points',
            'class:id,title_ar,title_en',
            'section:id,title_ar,title_en'
        ])
        ->whereHas('attempts', function ($q) use ($studentId) {
            $q->where('student_id', $studentId);
        })
        ->get()
        ->map(function ($quiz) use ($studentId) {
            $attempts = $quiz->attempts()
                ->where('student_id', $studentId)
                ->get();

            $bestAttempt = $attempts->sortByDesc('score')->first();
            $latestAttempt = $attempts->sortByDesc('started_at')->first();

            $totalPoints = $quiz->questions->sum('points') ?: 1;

            $bestScore = $bestAttempt?->score;
            $percentage = $bestScore !== null ? round(($bestScore / $totalPoints) * 100, 2) : null;

            return [
                'quiz_title'       => $quiz->title,
                'course_title'     => $quiz->course?->title,
                'is_class'=>$quiz->course?->is_class,
                'class_title'      => $quiz->class?->title,
                'section_title'    => $quiz->section?->title,
                'attempt_count'    => $attempts->count(),
                'score'            => $bestScore . ' / ' . $totalPoints,
                'score_percentage' => $percentage,
                'last_attempt_at'  => optional($latestAttempt?->started_at)?->format('Y-m-d H:i:s'),
            ];
        });

    return $this->success('', $quizzes);
}


public function homeworksResults(Request $request)
{
    $studentId = auth()->id();

    $homeworks = HomeWork::with([
          'course:id,title_ar,title_en,is_class',
          'class:id,title_ar,title_en',
          'section:id,title_ar,title_en'
        , 'questions:id,home_work_id,points'])
        ->whereHas('attempts', function ($q) use ($studentId) {
            $q->where('student_id', $studentId);
        })
        ->get()
        ->map(function ($homework) use ($studentId) {
            $attempts = $homework->attempts()
                ->where('student_id', $studentId)
                ->get();

            $bestAttempt = $attempts->sortByDesc('score')->first();
            $latestAttempt = $attempts->sortByDesc('started_at')->first();

            $totalPoints = $homework->questions->sum('points') ?: 1;

            $bestScore = $bestAttempt?->score;
            $percentage = $bestScore !== null ? round(($bestScore / $totalPoints) * 100, 2) : null;

            return [
                'homework_title'   => $homework->title,
                'course_title'     => $homework->course?->title,
                'is_class'=>$homework->course?->is_class,
                'class_title'      => $homework->class?->title,
                'section_title'    => $homework->section?->title,
                'attempt_count'    => $attempts->count(),
                'score'            => $bestScore . ' / ' . $totalPoints,
                'score_percentage' => $percentage,
                'last_attempt_at'  => optional($latestAttempt?->started_at)?->format('Y-m-d H:i:s'),
                'is_submitted'     => $latestAttempt?->submitted_at !== null,
            ];
        });

    return $this->success('', $homeworks);
}


}
