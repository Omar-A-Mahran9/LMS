<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\User;
use App\Rules\IsActive;
use App\Rules\PasswordNumberAndLetter;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Rules\NotNumbersOnly;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CustomerResource;
use App\Http\Resources\UserResource;
use App\Models\Customer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function loginByEmail(Request $request)
    {
        $request->validate([
            'email' => [
                'required',
                'email',
                'exists:customers,email',
                function ($attribute, $value, $fail) {
                    $customer = StudentwhereEmail($value)->first();

                    if ($customer && $customer->block_flag === 1 )
                    {
                        $fail(__("Your account is blocked. Please contact support."));
                    }
                }
            ],
            'password' => 'required|min:6',
        ]);

        $customer = StudentwhereEmail($request->email)->first();

        if (Hash::check($request->password, $customer->password))
        {
            $token = $customer->createToken('Personal access token to apis')->plainTextToken;

            return $this->success("logged in successfully", ['token' => $token, "user" => new CustomerResource($customer)]);

        } else
        {
            return $this->validationFailure(["password" => [__("Password mismatch")]]);
        }
    }

    public function loginOTP(Request $request, Customer $customer)
    {
        $request['phone'] = $customer->phone;
        $request->validate([
            'phone' => ['required', 'exists:customers'],
            'otp' => [
                'required',
                Rule::exists('customers')->where(function ($query) use ($customer) {
                    return $query->where('id', $customer->id);
                })
            ],
        ]);

        $customer->update([
            "otp" => null
        ]);

        $customer->update(['fcm_token' => $request->fcm_token]);
        $token = $customer->createToken('Personal access token to apis')->plainTextToken;

        return $this->success("logged in successfully", ['token' => $token, "customer" => new CustomerResource($customer)]);
    }



public function register(Request $request)
{
    $data = $request->validate([
        'image'           => 'nullable|image|mimes:jpg,png,jpeg,gif,svg',
        'first_name'      => ['required', 'string', 'max:255', new NotNumbersOnly()],
        'middle_name'     => ['nullable', 'string', 'max:255'],
        'last_name'       => ['required', 'string', 'max:255', new NotNumbersOnly()],
        'phone'           => ['required', 'string', 'regex:/^[0-9]+$/', 'max:20', 'unique:students'],
        'parent_phone'    => ['nullable', 'string', 'max:20'],
        'email'           => 'nullable|string|email|unique:students',
        'password'        => ['required', 'string', 'min:8', 'max:255', 'confirmed', new PasswordNumberAndLetter()],
        'password_confirmation' => 'required|same:password',
        'parent_job'      => ['nullable', 'string', 'max:255'],
        'gender'          => ['nullable', 'in:male,female'],
        'governorate_id'  => ['nullable', 'exists:governorates,id'],
        'category_id'     => ['nullable', 'exists:categories,id'],
    ]);

    // Upload image if present
    if ($request->hasFile('image')) {
        $data['image'] = uploadImageToDirectory($request->file('image'), 'Students');
    }

    // Generate unique login code
    do {
        $code = strtoupper(Str::random(6)); // Example: "AB12CD"
    } while (Student::where('code', $code)->exists());

    $data['code'] = $code;

    // Set flags
    $data['block_flag'] = 0;

    // Create student
    $student = Student::create($data);
    $student->remember_token = Str::random(10);
    $student->save();

    // Create Sanctum token
    $token = $student->createToken('Student Access Token')->plainTextToken;

    return $this->success("Student registered successfully", [
        'token' => $token,
        'login_code' => $student->code, // Return the code for future login
        'student' => new StudentResource($student),
    ]);
}


    /* function socialLogin(Request $request) {
        $request->validate([
            'social_id' => "required",
        ]);

        $user = User::where('social_id', $request->social_id)->first();
        if($user)
        {
            $token = $user->createToken('Personal access token to apis')->accessToken;

            return $this->success("logged in successfully", ['token' => $token, "user" => new UserResource($user)]);
        }

        $request->validate([
            'name' => "required|string:255",
            'phone' => 'required|regex:/(^(05)([0-9]{8})$)/u|max:255',
            'email' => "required|email:255",
            'social_image_link' => "nullable",
            'fcm_token' => "required",
        ]);

        $user = User::create([
            'social_id' => $request->social_id,
            'name' => $request->name,
            'social_image_link' => $request->social_image_link,
            'fcm_token' => $request->fcm_token,
            'phone' => $request->phone,
            'email' => $request->email
        ]);
        $token = $user->createToken('Personal access token to apis')->accessToken;

        return $this->success("logged in successfully", ['token' => $token, "user" => new UserResource($user)]);
    } */

    public function logout(Request $request)
    {
        $token = $request->user()->token();
        $token->revoke();

        return $this->success('You have been successfully logged out!');
    }

}
