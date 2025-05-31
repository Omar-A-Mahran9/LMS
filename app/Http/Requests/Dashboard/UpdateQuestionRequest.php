<?php

namespace App\Http\Requests\Dashboard;

use App\Models\CourseVideo;
use App\Rules\NotNumbersOnly;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateQuestionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return abilities()->contains('update_quizzes');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
  $question = request()->route('question');


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
            $rules['answers.*.is_correct'] = ['nullable']; // or 'in:true,false,1,0' if needed
        }

        if ($this->type === 'true_false') {
            $rules['answers'] = ['nullable']; // could be more specific depending on your use
            $rules['correct_tf'] = ['required', 'in:true,false'];
        }

        if ($this->type === 'short_answer') {
            $rules['answers'] = ['nullable'];
            $rules['short_answer'] = ['required', 'string'];
        }

        return $rules;
    }
  }

