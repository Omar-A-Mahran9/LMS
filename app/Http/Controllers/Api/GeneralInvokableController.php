<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

            // Dynamic SEO Meta Tags
            'meta' => [
                'title'             => $locale === 'ar' ? 'منصة تعليمية' : 'LMS Platform',
                'description' => $locale === 'ar'
                    ? 'منصة تعليمية رقمية متكاملة تقدم كورسات اللغة الإنجليزية لجميع المستويات، بإشراف مستر محمد النجار. نوفر تجربة تعلم تفاعلية تشمل فيديوهات تعليمية، تمارين واختبارات، نظام تتبع التقدم، وشهادات معتمدة. انضم إلينا وابدأ رحلتك نحو التميز الأكاديمي واللغوي.'
                    : 'An all-in-one digital learning platform offering English courses for all levels, led by Mr. Mohamed El Nagar. We provide an interactive learning experience with video lessons, exercises, quizzes, progress tracking, and certified completion certificates. Join us and start your journey toward academic and language excellence.',
                'keywords'          => $locale === 'ar' ? 'منصة, تعليم, كورسات, مستر محمد النجار, لغة إنجليزية' : 'lms, courses, English, Mohamed El Nagar, education',
                'author'            => 'Mohamed El Nagar',
                'robots'            => 'index, follow',
                'og:title'          => $locale === 'ar' ? 'تواصل معنا | منصة تعليمية' : 'Contact Us | LMS Platform',
                'og:description'    => $locale === 'ar' ? 'كورسات لغة إنجليزية بإدارة مستر محمد النجار' : 'English language courses by Mr. Mohamed El Nagar',
                'og:image'          => asset('images/contact.png'), // make sure this image exists
                'og:url'            => url()->current(),
                'favicon'           => asset('favicons/favicon-' . $locale . '.ico'), // example: favicon-ar.ico or favicon-en.ico
            ],
        ]);
    }
}
