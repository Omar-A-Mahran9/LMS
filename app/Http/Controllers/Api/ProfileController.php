<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
 use App\Http\Resources\Api\StudentResource;
use App\Http\Resources\Api\UpdateStudentProfileRequest;
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

}
