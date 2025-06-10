<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\Student;
use App\Rules\PasswordNumberAndLetter;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Resources\Api\StudentResource;

class ForgetPasswordController extends Controller
{

    public function sendOtp(Request $request)
    {
        // Validate: require at least one (email or phone)
        $request->validate([
            'email' => ['nullable', 'email', 'exists:students,email'],
            'phone' => ['nullable', 'string', 'exists:students,phone'],
        ], [], [
            'email' => __('Email'),
            'phone' => __('Phone'),
        ]);

        if (!$request->filled('email') && !$request->filled('phone')) {
            return $this->validationFailure(__('You must provide either an email or a phone.'));
        }

        // Determine if using email or phone
        if ($request->filled('email')) {
            $student = Student::where('email', $request->email)->first();
            $via = 'email';
        } else {
            $student = Student::where('phone', $request->phone)->first();
            $via = 'phone';
        }

        if (!$student) {
            return $this->validationFailure(__("This user does not exist"));
        }

        if ($student->block_flag === 1) {
            return $this->validationFailure(__("Your account is blocked. Please contact support."));
        }

        $otp = $student->sendOTP();

        // Optionally send via email or SMS here...
        $now = now();
        $expiresAt = $student->otp_expiration;

        // التأكد أن التاريخ موجود ولم ينتهِ
        if (!$expiresAt || $now->greaterThan($expiresAt)) {
            return $this->failure(__("OTP has expired."));
        }

        // حساب الوقت المتبقي بالثواني
        $remainingSeconds = $now->diffInSeconds($expiresAt, false); // false تعني نحصل على قيمة سالبة إذا انتهى الوقت

            return $this->success(__("OTP sent successfully."), [
                'otp' => $otp,
                // 'expiration_date' => $student->otp_expiration,
                'remaining_seconds' => now()->diffInSeconds($student->otp_expiration, false),

                'via' => $via,
            ]);
    }

public function checkOTP(Request $request)
{
    $request->validate([
        'otp' => ['required', 'string'],
        'email' => ['nullable', 'email', 'exists:students,email'],
        'phone' => ['nullable', 'string', 'exists:students,phone'],
    ], [], [
        'email' => __('Email'),
        'phone' => __('Phone'),
        'otp' => __('OTP Code'),
    ]);

    if (!$request->filled('email') && !$request->filled('phone')) {
        return $this->validationFailure(__('You must provide either an email or a phone.'));
    }

    $student = $request->filled('email')
        ? Student::where('email', $request->email)->first()
        : Student::where('phone', $request->phone)->first();

    if (!$student) {
        return $this->failure(__('This user does not exist.'));
    }

    if ($student->otp !== $request->otp) {
        return $this->failure(__('The OTP is incorrect.'));
    }

    if (!$student->otp_expiration || now()->greaterThan($student->otp_expiration)) {
        return $this->failure(__('The OTP has expired.'));
    }

    $student->update([
        'otp' => null,
        'otp_expiration' => null,
    ]);


    return $this->success(__('Verified successfully'), [
        'student' => new StudentResource($student),
    ]);
}



public function reSendOtp(Request $request)
{
     $request->validate([
        'email' => ['nullable', 'email', 'exists:students,email'],
        'phone' => ['nullable', 'string', 'exists:students,phone'],
    ], [], [
        'email' => __('Email'),
        'phone' => __('Phone'),
    ]);

    if (!$request->filled('email') && !$request->filled('phone')) {
        return $this->validationFailure(__('You must provide either an email or a phone.'));
    }

    // Find student by email or phone
    $student = $request->filled('email')
        ? Student::where('email', $request->email)->first()
        : Student::where('phone', $request->phone)->first();

    if (!$student) {
        return $this->failure(__("This user does not exist"));
    }

    if ($student->block_flag === 1) {
        return $this->failure(__("Your account is blocked. Please contact support."));
    }

    // Re-send OTP
    $otp = $student->sendOTP();

    $now = now();
    $expiresAt = $student->otp_expiration;

    if (!$expiresAt || $now->greaterThan($expiresAt)) {
        return $this->failure(__("OTP has expired."));
    }

    $remainingSeconds = $now->diffInSeconds($expiresAt, false);

    return $this->success(__("OTP re-sent successfully."), [
        'otp' => $otp,
        'remaining_seconds' => $remainingSeconds,
        'via' => $request->filled('email') ? 'email' : 'phone',
    ]);
}



public function changePassword(Request $request)
{
    // التحقق من صحة المدخلات
    $request->validate([
        'email' => ['nullable', 'email', 'exists:students,email'],
        'phone' => ['nullable', 'string', 'exists:students,phone'],
        'password' => ['required', 'min:6', new PasswordNumberAndLetter()],
        'password_confirmation' => 'required_with:password|same:password',
    ], [], [
        'email' => __('Email'),
        'phone' => __('Phone'),
        'password' => __('Password'),
        'password_confirmation' => __('Confirm Password'),
    ]);

    if (!$request->filled('email') && !$request->filled('phone')) {
        return $this->validationFailure(__('You must provide either an email or a phone.'));
    }

    // ابحث عن الطالب باستخدام البريد الإلكتروني أو الهاتف
    $student = $request->filled('email')
        ? Student::where('email', $request->email)->first()
        : Student::where('phone', $request->phone)->first();

    if (!$student) {
        return $this->failure(__("This user does not exist"));
    }

    // التحقق من أن الـ OTP تم التحقق منه مسبقًا
    if (!is_null($student->otp) && !is_null($student->otp_expiration)) {
        return $this->failure(__("You must verify the OTP before changing the password."));
    }

    // تحديث كلمة المرور
    $student->update(['password' => $request->password]);

    return $this->success(__("Password changed successfully."));
}


}
