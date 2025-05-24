<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreContact_usRequest;
use App\Models\Contact_us;
use Illuminate\Http\Request;

class ContactUsController extends Controller
{

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreContact_usRequest $request)
    {

        $contact_us = $request->validated();
        $contact_us_data = Contact_us::create($contact_us);
        return $this->success(__('َquestion has been registered successfully'));

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
