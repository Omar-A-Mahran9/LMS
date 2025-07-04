<?php

namespace App\Http\Requests\Dashboard;

use App\Rules\NotNumbersOnly;
use Illuminate\Foundation\Http\FormRequest;

class StoreReadingPassageRequest extends FormRequest
{

    public function authorize()
    {
        return abilities()->contains('view_quizzes');
    }


    public function rules()
    {
        return [
            'title_ar' => ['required', new NotNumbersOnly()],
            'title_en' => ['required', new NotNumbersOnly()],
            'description_ar' => ['required', new NotNumbersOnly()],
            'description_en' => ['required', new NotNumbersOnly()],
        ];
    }
}
