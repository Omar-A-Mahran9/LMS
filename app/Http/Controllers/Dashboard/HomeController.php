<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\UpdateHomeSettingsRequest;
use App\Models\PaymentMethod;
use App\Models\PaymentWay;
use App\Rules\NotNumbersOnly;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(UpdateHomeSettingsRequest $request)
    {
        if ($request->isMethod('post'))
        {
            setting($request->validated())->save();
        } else
        {
            $this->authorize('view_settings');

            return view('dashboard.settings.home.index');
        }
    }
    public function banner(UpdateHomeSettingsRequest $request)
    {
        if ($request->isMethod('get')) {
            $this->authorize('view_settings');
            return view('dashboard.settings.home.banner');
        }

        $data = $request->validated();

        // Define the list of banner keys you expect to handle
        $bannerKeys = [
            'common_question_banner',
            'how_to_use_banner',
            'contact_banner',
            'home_banner',
            'feature_banner'
            // Add more banner fields here if needed
        ];

        foreach ($bannerKeys as $key) {
            if ($request->hasFile($key)) {
                // Delete old image if exists
                deleteImageFromDirectory(setting($key), "Settings");

                // Upload new image and update the data array
                $data[$key] = uploadImageToDirectory($request->file($key), "Settings");
            }
        }

        // Save all settings
        setting($data)->save();

        return redirect()->back()->with('success', __('Banner settings updated successfully.'));
    }

    public function general(UpdateHomeSettingsRequest $request)
    {
        if ($request->isMethod('get')) {
            $this->authorize('view_settings');
            return view('dashboard.settings.home.general');
        }

        $data = $request->validated();

        // ✅ Handle logo_image
        if ($request->hasFile('logo_image')) {
            deleteImageFromDirectory(setting('logo_image'), "Settings");
            $data['logo_image'] = uploadImageToDirectory($request->file('logo_image'), "Settings");
        }

        // ✅ Handle light_logo_image
        if ($request->hasFile('light_logo_image')) {
            deleteImageFromDirectory(setting('light_logo_image'), "Settings");
            $data['light_logo_image'] = uploadImageToDirectory($request->file('light_logo_image'), "Settings");
        }

        // ✅ Handle favicon_icon (.ico or others)
        if ($request->hasFile('favicon_icon')) {
            deleteImageFromDirectory(setting('favicon_icon'), "Settings");
            $data['favicon_icon'] = uploadImageToDirectory($request->file('favicon_icon'), "Settings");
        }

        // Save all settings
        setting($data)->save();

        return redirect()->back()->with('success', __('تم تحديث الإعدادات العامة بنجاح.'));
    }

    public function aboutUs(UpdateHomeSettingsRequest $request)
    {
        // Check if it's a GET request — show the form
        if ($request->isMethod('get')) {
            $this->authorize('view_settings');
            return view('dashboard.settings.home.about-us');
        }

        // Otherwise, handle POST submission
        $data = $request->validated();

        // If a new image is uploaded, handle the upload
        if ($request->hasFile('about_us_image')) {
            deleteImageFromDirectory(setting('about_us_image'), "Settings");
            $data['about_us_image'] = uploadImageToDirectory($request->file('about_us_image'), "Settings");
        }

        // Save the updated settings
        setting($data)->save();

        // Redirect back with a success message (optional)
        return redirect()->back()->with('success', 'About Us settings updated successfully.');
    }
    public function terms(UpdateHomeSettingsRequest $request)
    {
        if ($request->isMethod('post'))
        {
            setting($request->validated())->save();
        } else
        {
            $this->authorize('view_settings');

            return view('dashboard.settings.home.terms');
        }
    }

    public function privacyPolicy(UpdateHomeSettingsRequest $request)
    {
        if ($request->isMethod('post'))
        {
            setting($request->validated())->save();
        } else
        {
            $this->authorize('view_settings');

            return view('dashboard.settings.home.privacy-policy');
        }
    }
    public function ourmission(UpdateHomeSettingsRequest $request)
    {
        if ($request->isMethod('post'))
        {
            setting($request->validated())->save();
        } else
        {
            $this->authorize('view_settings');

            return view('dashboard.settings.home.our-mission');
        }
    }

    public function ourvission(UpdateHomeSettingsRequest $request)
    {
        if ($request->isMethod('post'))
        {
            setting($request->validated())->save();
        } else
        {
            $this->authorize('view_settings');

            return view('dashboard.settings.home.our-vission');
        }
    }



}
