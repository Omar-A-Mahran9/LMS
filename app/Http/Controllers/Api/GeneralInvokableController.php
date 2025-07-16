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
            'logo'           =>  getImagePathFromDirectory(setting('logo_image'), "Settings"),
            'light_logo'           =>  getImagePathFromDirectory(setting('light_logo_image'), "Settings"),

            // Dynamic SEO Meta Tags
            // 'meta' => [
            //     'title'             => $locale === 'ar' ? 'منصة تعليمية' : 'LMS Platform',
            //     'description' => $locale === 'ar'
            //         ? 'منصة تعليمية رقمية متكاملة تقدم كورسات اللغة الإنجليزية لجميع المستويات، بإشراف مستر محمد النجار. نوفر تجربة تعلم تفاعلية تشمل فيديوهات تعليمية، تمارين واختبارات، نظام تتبع التقدم، وشهادات معتمدة. انضم إلينا وابدأ رحلتك نحو التميز الأكاديمي واللغوي.'
            //         : 'An all-in-one digital learning platform offering English courses for all levels, led by Mr. Mohamed El Nagar. We provide an interactive learning experience with video lessons, exercises, quizzes, progress tracking, and certified completion certificates. Join us and start your journey toward academic and language excellence.',
            //     'keywords'          => $locale === 'ar' ? 'منصة, تعليم, كورسات, مستر محمد النجار, لغة إنجليزية' : 'lms, courses, English, Mohamed El Nagar, education',
            //     'author'            => 'Mohamed El Nagar',
            //     'robots'            => 'index, follow',
            //     'og:title'          => $locale === 'ar' ? 'تواصل معنا | منصة تعليمية' : 'Contact Us | LMS Platform',
            //     'og:description'    => $locale === 'ar' ? 'كورسات لغة إنجليزية بإدارة مستر محمد النجار' : 'English language courses by Mr. Mohamed El Nagar',
            //     'og:image'          => asset('images/contact.png'), // make sure this image exists
            //     'og:url'            => url()->current(),
            //     'favicon'           =>  getImagePathFromDirectory(setting('favicon_icon'), "Settings")
            // ],

             'meta' => [
                'title' => $locale === 'ar' ? 'منصة تعليمية - مستر محمد النجار' : 'LMS Platform - Mr. Mohamed El Nagar',
                'description' => $locale === 'ar'
                    ? 'منصة رقمية متكاملة لتعلم اللغة الإنجليزية لجميع المستويات بإشراف مستر محمد النجار، تشمل فيديوهات، اختبارات، متابعة تقدم، وشهادات معتمدة.'
                    : 'A complete digital platform for learning English at all levels, led by Mr. Mohamed El Nagar. Includes videos, quizzes, progress tracking, and certified certificates.',
                'keywords' => $locale === 'ar'
                    ? 'منصة تعليمية, تعليم عن بعد, كورسات أونلاين, كورسات إنجليزي, تعلم الإنجليزية, قواعد اللغة الإنجليزية, محادثة باللغة الإنجليزية, استماع, قراءة, كتابة, مهارات اللغة الإنجليزية, اختبار تحديد مستوى, شهادة معتمدة, شهادة إلكترونية, ثانوية عامة, مراجعة نهائية, مدرس خصوصي, دروس خصوصية, حجز مدرس, حصة مباشرة, تعليم فردي, مدرس إنجليزي, معلم لغة إنجليزية, دروس أونلاين, شرح قواعد, تدريب على الامتحانات, بنك أسئلة, نماذج امتحانات, امتحانات تجريبية, تعليم تفاعلي, منصة رقمية, كورس تمهيدي, مبتدئين إنجليزي, متوسط, متقدم, خطة مذاكرة, متابعة دراسية, تقوية الطلاب, منصة شرح, تعلم بالهاتف, دروس للثانوية العامة, حصص مراجعة, حصة بث مباشر, تعلم ذاتي, تطوير اللغة, كورسات للمبتدئين, معلم خاص, متابعة مستمرة, محتوى تعليمي, فيديوهات تعليمية, تعلم في أي وقت'
                    : 'LMS, e-learning platform, English courses, online English course, English grammar, English conversation, listening skills, reading skills, writing skills, language skills, certified certificate, online certificate, high school preparation, private tutor, English teacher, live classes, private lessons, one-on-one tutoring, English grammar course, exam preparation, question bank, mock exams, interactive learning, digital learning, English for beginners, intermediate English, advanced English, study plan, student support, academic follow-up, explainer videos, on-demand learning, self-paced learning, language development, mobile learning, study at home, remote education, placement test, preparatory course, educational videos, online tutor, tutoring service, high school revision, live revision sessions, English skills training',
                'author' => 'Mohamed El Nagar',
                'robots' => 'index, follow',
                'og:title' => $locale === 'ar' ? 'عن المنصة | مستر محمد النجار' : 'About the Platform | Mohamed El Nagar',
                'og:description' => $locale === 'ar'
                    ? 'تعلم الإنجليزية بأسلوب سهل مع مستر محمد النجار وخطتك الأكاديمية للنجاح'
                    : 'Learn English with ease and structure with Mohamed El Nagar’s academic method.',
                'og:image' => getImagePathFromDirectory(setting('logo_image')),
                'og:url' => 'https://mohamed-elnagar.com/about',
                'favicon' => getImagePathFromDirectory(setting('favicon_icon'), "Settings"),
            ],
        ]);
    }
}
