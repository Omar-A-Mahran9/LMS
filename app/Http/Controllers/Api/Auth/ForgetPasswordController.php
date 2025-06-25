<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\Student;
use App\Rules\PasswordNumberAndLetter;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Resources\Api\StudentResource;
use Illuminate\Support\Facades\Hash;

class ForgetPasswordController extends Controller
{
public function sendOtp(Request $request)
{
    $request->validate([
        'login' => ['required', 'string'],
    ], [], [
        'login' => __('Email or Phone'),
    ]);

    $loginInput = $request->input('login');
    $loginType  = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

    // Find student
    $student = Student::where($loginType, $loginInput)->first();

    if (!$student) {
        return $this->validationFailure([
            'login' => [__('User not found')],
        ]);
    }

    if ($student->block_flag) {
        return $this->validationFailure([
            'login' => [__('Your account is blocked. Please contact support.')],
        ]);
    }

    // Generate and save OTP
    $otp = $student->sendOTP();

    // Optional: send SMS/email here using $loginType
    $now        = now();
    $expiresAt  = $student->otp_expiration;

    if (!$expiresAt || $now->greaterThan($expiresAt)) {
        return $this->failure(__("OTP has expired."));
    }

    $remainingSeconds = $now->diffInSeconds($expiresAt, false);

    return $this->success(__("OTP sent successfully."), [
        'otp'               => $otp, // 🔐 remove in production
        'remaining_seconds' => $remainingSeconds,
        'via'               => $loginType,
    ]);
}

    // public function sendOtp(Request $request)
    // {
    //     // Validate: require at least one (email or phone)
    //     $request->validate([
    //         'email' => ['nullable', 'email', 'exists:students,email'],
    //         'phone' => ['nullable', 'string', 'exists:students,phone'],
    //     ], [], [
    //         'email' => __('Email'),
    //         'phone' => __('Phone'),
    //     ]);

    //     if (!$request->filled('email') && !$request->filled('phone')) {
    //         return $this->validationFailure(__('You must provide either an email or a phone.'));
    //     }

    //     // Determine if using email or phone
    //     if ($request->filled('email')) {
    //         $student = Student::where('email', $request->email)->first();
    //         $via = 'email';
    //     } else {
    //         $student = Student::where('phone', $request->phone)->first();
    //         $via = 'phone';
    //     }

    //     if (!$student) {
    //         return $this->validationFailure(__("This user does not exist"));
    //     }

    //     if ($student->block_flag === 1) {
    //         return $this->validationFailure(__("Your account is blocked. Please contact support."));
    //     }

    //     $otp = $student->sendOTP();

    //     // Optionally send via email or SMS here...
    //     $now = now();
    //     $expiresAt = $student->otp_expiration;

    //     // التأكد أن التاريخ موجود ولم ينتهِ
    //     if (!$expiresAt || $now->greaterThan($expiresAt)) {
    //         return $this->failure(__("OTP has expired."));
    //     }

    //     // حساب الوقت المتبقي بالثواني
    //     $remainingSeconds = $now->diffInSeconds($expiresAt, false); // false تعني نحصل على قيمة سالبة إذا انتهى الوقت

    //         return $this->success(__("OTP sent successfully."), [
    //             'otp' => $otp,
    //             // 'expiration_date' => $student->otp_expiration,
    //             'remaining_seconds' => now()->diffInSeconds($student->otp_expiration, false),

    //             'via' => $via,
    //         ]);
    // }

    public function checkOTP(Request $request)
    {
        $request->validate([
            'login' => ['required', 'string'],
            'otp'   => ['required', 'string'],
        ], [], [
            'login' => __('Email or Phone'),
            'otp'   => __('OTP Code'),
        ]);

        $loginInput = $request->input('login');
        $fieldType  = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        $student = Student::where($fieldType, $loginInput)->first();

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

// public function checkOTP(Request $request)
// {
//     $request->validate([
//         'otp' => ['required', 'string'],
//         'email' => ['nullable', 'email', 'exists:students,email'],
//         'phone' => ['nullable', 'string', 'exists:students,phone'],
//     ], [], [
//         'email' => __('Email'),
//         'phone' => __('Phone'),
//         'otp' => __('OTP Code'),
//     ]);

//     if (!$request->filled('email') && !$request->filled('phone')) {
//         return $this->validationFailure(__('You must provide either an email or a phone.'));
//     }

//     $student = $request->filled('email')
//         ? Student::where('email', $request->email)->first()
//         : Student::where('phone', $request->phone)->first();

//     if (!$student) {
//         return $this->failure(__('This user does not exist.'));
//     }

//     if ($student->otp !== $request->otp) {
//         return $this->failure(__('The OTP is incorrect.'));
//     }

//     if (!$student->otp_expiration || now()->greaterThan($student->otp_expiration)) {
//         return $this->failure(__('The OTP has expired.'));
//     }

//     $student->update([
//         'otp' => null,
//         'otp_expiration' => null,
//     ]);


//     return $this->success(__('Verified successfully'), [
//         'student' => new StudentResource($student),
//     ]);
// }
public function reSendOtp(Request $request)
{
    $request->validate([
        'login' => ['required', 'string'],
    ], [], [
        'login' => __('Email or Phone'),
    ]);

    $loginInput = $request->input('login');
    $fieldType  = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

    $student = Student::where($fieldType, $loginInput)->first();

    if (!$student) {
        return $this->failure(__("This user does not exist"));
    }

    if ($student->block_flag === 1) {
        return $this->failure(__("Your account is blocked. Please contact support."));
    }

    // Re-send OTP
    $otp = $student->sendOTP();

    $expiresAt = $student->otp_expiration;
    $now       = now();

    if (!$expiresAt || $now->greaterThan($expiresAt)) {
        return $this->failure(__("OTP has expired."));
    }

    $remainingSeconds = $now->diffInSeconds($expiresAt, false);

    return $this->success(__("OTP re-sent successfully."), [
        'otp' => $otp, // 🚨 Remove in production
        'remaining_seconds' => $remainingSeconds,
        'via' => $fieldType,
    ]);
}



// public function reSendOtp(Request $request)
// {
//      $request->validate([
//         'email' => ['nullable', 'email', 'exists:students,email'],
//         'phone' => ['nullable', 'string', 'exists:students,phone'],
//     ], [], [
//         'email' => __('Email'),
//         'phone' => __('Phone'),
//     ]);

//     if (!$request->filled('email') && !$request->filled('phone')) {
//         return $this->validationFailure(__('You must provide either an email or a phone.'));
//     }

//     // Find student by email or phone
//     $student = $request->filled('email')
//         ? Student::where('email', $request->email)->first()
//         : Student::where('phone', $request->phone)->first();

//     if (!$student) {
//         return $this->failure(__("This user does not exist"));
//     }

//     if ($student->block_flag === 1) {
//         return $this->failure(__("Your account is blocked. Please contact support."));
//     }

//     // Re-send OTP
//     $otp = $student->sendOTP();

//     $now = now();
//     $expiresAt = $student->otp_expiration;

//     if (!$expiresAt || $now->greaterThan($expiresAt)) {
//         return $this->failure(__("OTP has expired."));
//     }

//     $remainingSeconds = $now->diffInSeconds($expiresAt, false);

//     return $this->success(__("OTP re-sent successfully."), [
//         'otp' => $otp,
//         'remaining_seconds' => $remainingSeconds,
//         'via' => $request->filled('email') ? 'email' : 'phone',
//     ]);
// }




public function changePassword(Request $request)
{
    $request->validate([
        'login' => ['required', 'string'],
        'password' => ['required', 'min:6', new PasswordNumberAndLetter()],
        'password_confirmation' => ['required_with:password', 'same:password'],
    ], [], [
        'login' => __('Email or Phone'),
        'password' => __('Password'),
        'password_confirmation' => __('Confirm Password'),
    ]);

    $loginInput = $request->input('login');
    $fieldType  = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

    $student = Student::where($fieldType, $loginInput)->first();

    if (!$student) {
        return $this->failure(__("This user does not exist."));
    }

    // تأكد أن OTP تم التحقق منه (أي تم مسحه من السجل)
    if (!is_null($student->otp) && !is_null($student->otp_expiration)) {
        return $this->failure(__("You must verify the OTP before changing the password."));
    }

    // تحديث كلمة المرور
    $student->update(['password' => $request->password]);

    return $this->success(__("Password changed successfully."));
}



}
