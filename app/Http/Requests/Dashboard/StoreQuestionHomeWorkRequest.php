<?php

namespace App\Http\Requests\Dashboard;

use App\Rules\NotNumbersOnly;
use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionHomeWorkRequest extends FormRequest
{

    public function authorize()
    {
        return abilities()->contains('create_homework');
    }

    public function rules()
    {
    $rules = [
        'home_work_id'     => ['required', 'exists:home_works,id'],
        'question_ar' => ['required', 'string', new NotNumbersOnly],
        'question_en' => ['required', 'string', new NotNumbersOnly],
        'type'        => ['required', 'in:multiple_choice,true_false,short_answer'],
        'points'      => ['required', 'integer', 'min:1'],
    ];
    if ($this->type === 'multiple_choice') {
        $rules['answers'] = ['required', 'array', 'min:2'];
        $rules['answers.*.text_ar'] = ['required', 'string'];
        $rules['answers.*.text_en'] = ['required', 'string'];
$rules['answers.*.is_correct'] = ['nullable'];
    }

    if ($this->type === 'true_false') {
                $rules['answers'] = ['required'];

        $rules['correct_tf'] = ['required', 'in:true,false'];
    }

    if ($this->type === 'short_answer') {
                        $rules['answers'] = ['nullable'];

        $rules['short_answer'] = ['required', 'string'];
    }

    return $rules;
}


    }

