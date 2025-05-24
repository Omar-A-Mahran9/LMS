<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\Student;
use App\Rules\PasswordNumberAndLetter;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Resources\Api\StudentResource;
use App\Models\Student;

class ForgetPasswordController extends Controller
{

    public function sendOtp(Request $request, $phone)
    {
        $student = Student::where('phone', $phone)->first();
        if (!$student)
            return $this->failure(__("This user does not exist"));

        $request['phone'] = $student->phone;

        $request->validate([
            'phone' => ['required', 'exists:students'],
        ]);
        if ($student->block_flag === 1)
        {
            return $this->failure(__("Your account is blocked. Please contact support."));
        }
        $student->sendOTP();

        return $this->success("Send otp is successfully", [ "student" => new StudentResource($student)]);
    }

    public function reSendOtp(Request $request, $phone)
    {
        $student = Student::where('phone', $phone)->first();
        if (!$student)
            return $this->failure(__("This user does not exist"));

        $request['phone'] = $student->phone;

        $request->validate([
            'phone' => ['required', 'exists:students'],
        ]);
        if ($student->block_flag === 1)
        {
            return $this->failure(__("Your account is blocked. Please contact support."));
        }
        $student->sendOTP();
        return $this->success("Re-send otp is successfully", [ "student" => new StudentResource($student)]);
    }
    public function checkOTP(Request $request, Student $student)
    {
        $request->validate([
            'otp' => [
                'required',
                Rule::exists('students')->where(function ($query) use ($student) {
                    return $query->where('id', $student->id);
                })
            ]
        ]);
        $student->update([
            "otp" => null
        ]);
        $token = $student->createToken('Personal access token to apis')->plainTextToken;

        return $this->success("verified successfully", ['token' => $token,  "student" => new StudentResource($student)]);
     }

    public function changePassword(Request $request, Student $student)
    {
        $request->validate([
            'password' => ['required', 'min:6', new PasswordNumberAndLetter()],
            'password_confirmation' => 'required|required_with:password|min:6|same:password',
        ]);

        $student->update(['password' => $request->password]);

        return $this->success("password changed successfully");
    }
}
