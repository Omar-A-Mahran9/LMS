<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CategoryResource;
use App\Http\Resources\Api\GovernmentsResource;
use App\Http\Resources\Api\CommonQuestionResource;
use App\Http\Resources\Api\HowuseResource;
use App\Http\Resources\Api\RateResource;
use App\Http\Resources\Api\ServiceResource;
use App\Http\Resources\Api\SliderResource;

use App\Http\Resources\Api\WhyusResource;

use App\Models\CourseController;
use App\Models\Category;
use App\Models\City;
use App\Models\CommonQuestion;
use App\Models\Government;
use App\Models\Student_rate;
use App\Models\Howuse;
use App\Models\NewsLetter;

use App\Models\Slider;

use App\Models\Whyus;
use Illuminate\Http\Request;

class HomeController extends Controller
{

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

    public function getSliders()
    {
        $sliders = Slider::where('status', '1')->get();

        return $this->success('', SliderResource::collection($sliders));
    }


public function getCategories()
{
    $locale = app()->getLocale(); // 'ar' or 'en'
    $suffix = $locale === 'ar' ? '_ar' : '_en';

    // Get only published categories that are parent-level or subcategories if needed
    $categories = Category::where('is_publish', 1)
        ->whereNull('parent_id') // Only main categories; remove this to get all
        ->get();

    return $this->success('', [
         'categories' => CategoryResource::collection($categories),
    ]);
}



    public function getwhyus()
    {
        $Whyus = Whyus::get();

        return $this->success('', WhyusResource::collection($Whyus));
    }
    public function getgovernments()
    {
        $cities = Government::get();

        return $this->success('', GovernmentsResource::collection($cities));
    }

      public function getrates()
        {
            // Fetch all rates from the Rate model
            $rates = Student_rate::all(); // Or you can use a query like ->where('status', 'approved') to filter rates


            return $this->success('', RateResource::collection($rates));

        }

    public function getQuestions()
    {
        $CommonQuestion = CommonQuestion::get();

        return $this->success('', CommonQuestionResource::collection($CommonQuestion));
    }

    public function getHowUse()
    {
        $locale = app()->getLocale(); // 'ar' or 'en'
        $suffix = $locale === 'ar' ? '_ar' : '_en';

        $data = [
            'label'           => setting('label_about_us' . $suffix),
            'description'     => setting('description_about_us' . $suffix),
            'image_url' => getImagePathFromDirectory(setting('about_us_banner'), 'Settings') ,
            'video_url' => "https://www.youtube.com/watch?v=jaj9CPvLdy0&list=RDMMjaj9CPvLdy0&start_radio=1",

        ];
        return $this->success('',  $data);
    }



public function getAboutUs()
{
    $locale = app()->getLocale(); // 'ar' or 'en'
    $suffix = $locale === 'ar' ? '_ar' : '_en';

    $data = [
        'about_us_banner_data' => [
            'label'           => setting('label_about_us' . $suffix),
            'description'     => setting('description_about_us' . $suffix),
            'about_us_banner' => getImagePathFromDirectory(setting('about_us_banner'), 'Settings') ,
        ],
        'about_us_image' => getImagePathFromDirectory(setting('about_us_image'), 'Settings'),
        'about_us'       => setting('about_us' . $suffix),
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

            'logo' => getImagePathFromDirectory(setting('about_us_banner'), 'Settings') ,
            'Site_name'           => setting('label_about_us' . $suffix),
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
