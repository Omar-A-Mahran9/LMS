<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\User;
use App\Rules\IsActive;
use App\Rules\PasswordNumberAndLetter;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\StudentResource;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
 public function login(Request $request)
{
    $request->validate([
        'login' => ['required', 'string'], // هنا ممكن يكون إيميل أو رقم هاتف
        'password' => ['required', 'min:6'],
    ]);

    $loginInput = $request->input('login');

    // تحديد هل هو إيميل أو رقم هاتف
    $fieldType = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

    // جلب الطالب حسب النوع
    $student = Student::where($fieldType, $loginInput)->first();

    if (!$student) {
        return $this->validationFailure([
            'login' => [__('Your Data is not found')],
        ]);
    }

    if ($student->block_flag === 1) {
        return $this->validationFailure([
            'login' => [__('Your account is blocked. Please contact support.')],
        ]);
    }

    if (Hash::check($request->password, $student->password)) {
        $token = $student->createToken('Personal access token to apis')->plainTextToken;

        return $this->success("logged in successfully", [
            'token' => $token,
            'user' => new StudentResource($student),
        ]);
    }

    return $this->validationFailure([
        'password' => [__("Password mismatch")],
    ]);
}


    public function loginOTP(Request $request, Student $student)
    {
        $request['phone'] = $student->phone;
        $request->validate([
            'phone' => ['required', 'exists:students'],
            'otp' => [
                'required',
                Rule::exists('students')->where(function ($query) use ($student) {
                    return $query->where('id', $student->id);
                })
            ],
        ]);

        $student->update([
            "otp" => null
        ]);

        $student->update(['fcm_token' => $request->fcm_token]);
        $token = $student->createToken('Personal access token to apis')->plainTextToken;

        return $this->success("logged in successfully", ['token' => $token,  "student" => new StudentResource($student)]);
    }


public function register(Request $request)
{
    $data = $request->validate([
        'first_name' => ['required', 'string', 'max:255'],
        'middle_name' => ['nullable', 'string', 'max:255'],
        'last_name' => ['required', 'string', 'max:255'],

        'phone' => ['required', 'string', 'regex:/^[0-9]+$/', 'max:20', 'unique:students'],
        'parent_phone' => ['nullable', 'string', 'regex:/^[0-9]+$/', 'max:20'],
        'parent_job' => 'nullable|string|max:255',
        'gender' => 'required|in:male,female',
        'government_id' => 'required|exists:governments,id',
        'category_id' => 'required|exists:categories,id',

        'email' => 'required|string|email|unique:students',

        'password' => ['required', 'string', 'min:8', 'max:255'],
        'confirm_password' => ['required', 'same:password'],


    ]);

    // Handle image upload if provided
    if ($request->hasFile('image')) {
        $data['image'] = uploadImageToDirectory($request->file('image'), 'Students');
    }



    // Default flags
    $data['block_flag'] = false;

    // Create the student record
    $student = Student::create($data);

    // Generate OTP and expiration (valid 15 minutes)
    $student->otp = rand(111111, 999999);
    $student->otp_expiration = now()->addMinutes(15);
    $student->remember_token = Str::random(10);
    $student->save();

    // Optionally send OTP here
    // $student->sendOTP();

    // Create a personal access token
    $token = $student->createToken('Personal Access Token')->plainTextToken;

    return response()->json([
        'message' => 'Registration successful',
        'token' => $token,
        'student' => new StudentResource($student),
    ], 201);
}



    public function logout(Request $request)
    {
        $token = $request->user()->token();
        $token->revoke();

        return $this->success('You have been successfully logged out!');
    }

}
