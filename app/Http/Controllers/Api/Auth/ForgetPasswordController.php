<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\Student;
use App\Rules\PasswordNumberAndLetter;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\StudentResource;
use App\Mail\SendOtpMail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class ForgetPasswordController extends Controller
{
    // Wrong guesses allowed per OTP before it is thrown away (the code is only 4 digits)
    private const MAX_OTP_ATTEMPTS = 5;

    // How long a verified OTP lets the student set a new password
    private const RESET_WINDOW_MINUTES = 15;

    private function failedAttemptsKey(Student $student): string
    {
        return "otp_failed_attempts_{$student->id}";
    }

    private function resetAllowedKey(Student $student): string
    {
        return "password_reset_verified_{$student->id}";
    }

public function sendOtp(Request $request)
{
    $request->validate([
        'login' => ['required', 'string'],
    ], [], [
        'login' => __('Email or Phone'),
    ]);

    $loginInput = $request->input('login');
    $loginType  = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

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
    Cache::forget($this->failedAttemptsKey($student));

    if (!empty($student->email) && filter_var($student->email, FILTER_VALIDATE_EMAIL)) {
        Mail::to($student->email)->send(new SendOtpMail($otp, $student));
    } else {
        return $this->failure(__('This user does not have a valid email address.'));
    }


    $now       = now();
    $expiresAt = $student->otp_expiration;

    if (!$expiresAt || $now->greaterThan($expiresAt)) {
        return $this->failure(__("OTP has expired."));
    }

    $remainingSeconds = $now->diffInSeconds($expiresAt, false);

    return $this->success(__("OTP sent successfully."), [
        'remaining_seconds' => $remainingSeconds,
        'via'               => $loginType,
    ]);
}


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

        if (is_null($student->otp) || (string) $student->otp !== $request->otp) {
            $attempts = Cache::increment($this->failedAttemptsKey($student));

            if ($attempts >= self::MAX_OTP_ATTEMPTS) {
                // Too many wrong guesses: the student has to request a new code
                $student->update(['otp' => null, 'otp_expiration' => null]);
                Cache::forget($this->failedAttemptsKey($student));
            }

            return $this->failure(__('The OTP is incorrect.'));
        }

        if (!$student->otp_expiration || now()->greaterThan($student->otp_expiration)) {
            return $this->failure(__('The OTP has expired.'));
        }

        $student->update([
            'otp' => null,
            'otp_expiration' => null,
        ]);
        Cache::forget($this->failedAttemptsKey($student));
        Cache::put($this->resetAllowedKey($student), true, now()->addMinutes(self::RESET_WINDOW_MINUTES));

        return $this->success(__('Verified successfully'), [
            'student' => new StudentResource($student),
        ]);
    }


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
    Cache::forget($this->failedAttemptsKey($student));
    if (!empty($student->email) && filter_var($student->email, FILTER_VALIDATE_EMAIL)) {
        Mail::to($student->email)->send(new SendOtpMail($otp, $student));
    } else {
        return $this->failure(__('This user does not have a valid email address.'));
    }
    $expiresAt = $student->otp_expiration;
    $now       = now();

    if (!$expiresAt || $now->greaterThan($expiresAt)) {
        return $this->failure(__("OTP has expired."));
    }

    $remainingSeconds = $now->diffInSeconds($expiresAt, false);

    return $this->success(__("OTP re-sent successfully."), [
        'remaining_seconds' => $remainingSeconds,
        'via' => $fieldType,
    ]);
}


 



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

    // Only right after a successful verify-otp for this student, and only once
    // (an empty otp column alone proves nothing: it is also empty for anyone who never asked for a code)
    if (!Cache::pull($this->resetAllowedKey($student))) {
        return $this->failure(__("You must verify the OTP before changing the password."));
    }

    // تحديث كلمة المرور
    $student->update(['password' => $request->password]);

    return $this->success(__("Password changed successfully."));
}



}
