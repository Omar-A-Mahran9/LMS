<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CategoryResource;
use App\Http\Resources\Api\GovernmentsResource;
use App\Http\Resources\Api\CommonQuestionResource;
use App\Http\Resources\Api\CourseDetailsResource;
use App\Http\Resources\Api\CoursesDetailsResource;
use App\Http\Resources\Api\RateResource;
 use App\Http\Resources\Api\SliderResource;


use App\Models\Category;
 use App\Models\CommonQuestion;
use App\Models\Course;
use App\Models\Government;
use App\Models\Student_rate;
 use App\Models\NewsLetter;

use App\Models\Slider;

 use Illuminate\Http\Request;

class HomeController extends Controller
{


    public function getHome()
{
    $locale = app()->getLocale();
    $suffix = $locale === 'ar' ? '_ar' : '_en';

    // Sliders
    $sliders = Slider::where('status', '1')->get();
   $categories = Category::where('is_publish', 1)
        ->whereNull('parent_id') // Only main categories; remove this to get all
        ->get();
  $rates = Student_rate::all(); // Or you can use a query like ->where('status', 'approved') to filter rate
    $address = $locale === 'ar' ? setting('address_ar') : setting('address_en');

    $ask_us = [
            'image_url'=>getImagePathFromDirectory(setting('about_us_image'), 'Settings'),
            'label'           => setting('label' . $suffix),
            'description'     => setting('about_us' . $suffix),
            'experince_year'     => 20,
            'lecture_count'     => 200,
        ];
   $HowUse = [
            'label'           => setting('label_how_to_use' . $suffix),
            'description'     => setting('description_how_to_use' . $suffix),
            'image_url' => getImagePathFromDirectory(setting('how_to_use_banner'), 'Settings') ,
            'video_url' => convertToYoutubeEmbed(setting('video_how_to_use_url')),


        ];
        $CommonQuestion = CommonQuestion::get();
        $contact_us_data=[
            'label'           => setting('label_about_us' . $suffix),
            'description'     => setting('description_about_us' . $suffix),
            'phone_number'       => setting('sms_number'),
            'email'            => setting('email'),
            'address'          => $address,
'google_map_iframe' => $this->convertToIframe(setting('google_map_url')),

        ];
    // Combine and return
    return $this->success('', [
        'sliders' => SliderResource::collection($sliders),
        'categories' => CategoryResource::collection($categories),
        'rates' => RateResource::collection($rates),
        'ask_us' =>$ask_us,
        'HowUse' =>$HowUse,
        'CommonQuestion' =>[
            'label'           => setting('label_common_question' . $suffix),
            'description'     => setting('description_common_question' . $suffix),
            'image_url' => getImagePathFromDirectory(setting('common_question_banner'), 'Settings') ,
            'question_and_answer'=>CommonQuestionResource::collection($CommonQuestion),
        ],
        'contact_us_data'=>$contact_us_data

    ]);
}

protected function convertToIframe($url)
{
    // If already iframe, just return it
    if (Str::contains($url, '<iframe')) {
        return $url;
    }

    // Fallback iframe using q param (not perfect, but works)
    return '<iframe
                src="https://www.google.com/maps?q=' . urlencode($url) . '&output=embed"
                width="600"
                height="450"
                style="border:0;"
                allowfullscreen=""
                loading="lazy">
            </iframe>';
}


    public function newsLetter(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email:rfc,dns', 'unique:news_letters'],
        ]);

        NewsLetter::create([
            'email' => $request->email
        ]);

        return $this->success(__('Created Successfully'));
    }



    public function getCategory()
    {
        $categories = Category::where('is_publish', 1)
        ->whereNull('parent_id') // Only main categories; remove this to get all
        ->get();

        return $this->success('', CategoryResource::collection($categories));
    }










    public function getgovernments()
    {
        $cities = Government::get();

        return $this->success('', GovernmentsResource::collection($cities));
    }




public function getAboutUs()
{
    $locale = app()->getLocale(); // 'ar' or 'en'
    $suffix = $locale === 'ar' ? '_ar' : '_en';
    $ask_us = [
            'image_url'=>getImagePathFromDirectory(setting('about_us_image'), 'Settings'),
            'label'           => setting('label' . $suffix),
            'description'     => setting('about_us' . $suffix),
            'experince_year'     => 20,
            'lecture_count'     => 200,
        ];
    $data = [
        'ask_us' =>$ask_us,


        'our_mission'    => setting('our_mission' . $suffix),
        'our_vision'     => setting('our_vission' . $suffix), // double-check spelling
    ];

    return $this->success('', $data);
}



public function getprivacypolicy()
{
    $locale = app()->getLocale(); // e.g., 'ar' or 'en'
    $key = 'privacy_policy_' . $locale; // Will resolve to 'privacy_policy_ar' or 'privacy_policy_en'

    $data = setting($key); // Fetch the appropriate setting

    return $this->success('', $data);
}


public function getfooter()
{
    $locale = app()->getLocale(); // 'ar' or 'en'
    $suffix = $locale === 'ar' ? '_ar' : '_en';

    $data = [

            'logo' => getImagePathFromDirectory(setting('common_question_banner'), 'Settings') ,
            'Site_name'           => setting('label_about_us' . $suffix),
            'Site_description'           => setting('label_about_us' . $suffix),

            'description'     => setting('description_about_us' . $suffix),
            'instagram_link'   => setting('instagram_link'),
            'facebook_link'    => setting('facebook_link'),
            'youtube_link'     => setting('youtube_link'),
            'telegram_link'     => setting('youtube_link'),
            'tiktok_link'     => setting('youtube_link'),
            'whatsapp_number'  => setting('whatsapp_number'),
            'sms_number'       => setting('sms_number'),

    ];

    return $this->success('', $data);
}



}
