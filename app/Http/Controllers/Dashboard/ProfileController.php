<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function profileInfo()
    {
        $admin = auth('admin')->user();

        return view('dashboard.profile-info', compact('admin'));
    }

    public function updateProfileInfo(Request $request)
    {
        $admin = auth('admin')->user();

        $data = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('admins', 'phone')->ignore($admin->id)],
        ]);

        $admin->update($data);

        return response(['message' => __('Profile updated successfully')]);
    }

    public function updateProfileEmail(Request $request)
    {
        $admin = auth('admin')->user();

        $request->validate([
            'email'                  => ['required', 'email', 'max:255', Rule::unique('admins', 'email')->ignore($admin->id)],
            'confirm_email_password' => ['required', 'string'],
        ]);

        if (!Hash::check($request->confirm_email_password, $admin->password)) {
            throw ValidationException::withMessages([
                'confirm_email_password' => __('The password is incorrect'),
            ]);
        }

        $admin->update(['email' => $request->email]);

        return response(['message' => __('Email updated successfully')]);
    }

    public function updateProfilePassword(Request $request)
    {
        $admin = auth('admin')->user();

        $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ]);

        if (!Hash::check($request->current_password, $admin->password)) {
            throw ValidationException::withMessages([
                'current_password' => __('The password is incorrect'),
            ]);
        }

        // Admin::setPasswordAttribute hashes it
        $admin->update(['password' => $request->password]);

        return response(['message' => __('Password updated successfully')]);
    }
}
