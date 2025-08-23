<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreContact_usRequest;
use App\Models\Contact_us;
use Illuminate\Http\Request;

class ContactUsController extends Controller
{

public function store(StoreContact_usRequest $request)
{
    $data = $request->validated();

    if (auth('api')->check()) {
        $data['student_id'] = auth('student')->id();
    }

    Contact_us::create($data);

    return $this->success(__('Question has been registered successfully'));
}


    public function data(){
    $locale = app()->getLocale(); // 'ar' or 'en'
    $suffix = $locale === 'ar' ? '_ar' : '_en';

    return $this->success('', [

            'label'           => setting('label_contact' . $suffix),
            'description'     => setting('description_contact' . $suffix),
            'count_of_experince'=>20,
            'count_of_students'=>20,

    ]);
    }



}
