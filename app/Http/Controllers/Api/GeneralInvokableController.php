<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\SliderResource;
use App\Models\Slider;
use Illuminate\Http\Request;

class GeneralInvokableController extends Controller
{
public function __invoke(Request $request)
{
    $locale = app()->getLocale(); // 'ar' or 'en'
    $suffix = $locale === 'ar' ? '_ar' : '_en';

    $address = $locale === 'ar' ? setting('address_ar') : setting('address_en');

    return $this->success('', [
         'instagram_link'   => setting('instagram_link'),
        'facebook_link'    => setting('facebook_link'),
        'youtube_link'     => setting('youtube_link'),
        'whatsapp_number'  => setting('whatsapp_number'),
        'sms_number'       => setting('sms_number'),
        'email'            => setting('email'),
        'address'          => $address,
        'google_map_url'   => setting('google_map_url'),
    ]);
}

}
