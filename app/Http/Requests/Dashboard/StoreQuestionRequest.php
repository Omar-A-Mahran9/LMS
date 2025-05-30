<?php

namespace App\Http\Requests\Dashboard;

use App\Rules\NotNumbersOnly;
use Illuminate\Foundation\Http\FormRequest;

class StoreQuestionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return abilities()->contains('create_quizzes');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
    $rules = [
        'quiz_id'     => ['required', 'exists:quizzes,id'],
        'question_ar' => ['required', 'string', new NotNumbersOnly],
        'question_en' => ['required', 'string', new NotNumbersOnly],
        'type'        => ['required', 'in:multiple_choice,true_false,short_answer'],
        'points'      => ['required', 'integer', 'min:1'],
    ];
    if ($this->type === 'multiple_choice') {
        $rules['answers'] = ['required', 'array', 'min:2'];
        $rules['answers.*.text_ar'] = ['required', 'string'];
        $rules['answers.*.text_en'] = ['required', 'string'];
        $rules['answers.*.is_correct'] = ['nullable', 'boolean'];
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

