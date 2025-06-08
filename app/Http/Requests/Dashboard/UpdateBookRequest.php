<?php

namespace App\Http\Requests\Dashboard;

use App\Rules\NotNumbersOnly;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBookRequest extends FormRequest
{

    public function authorize()
    {
        return abilities()->contains('create_books');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

return [
    // Required image
    'image' => 'nullable|image|mimes:jpg,png,jpeg,gif,svg|max:5120',
    'attachment' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,txt|max:10240', // 10MB max

    // Multilingual Titles and Descriptions
    'title_ar' => ['required', 'max:255', new NotNumbersOnly(),'unique:books,title_ar'],
    'title_en' => ['required', 'max:255', new NotNumbersOnly(), 'unique:books,title_en'],

    'description_ar' => ['required', new NotNumbersOnly()],
    'description_en' => ['required', new NotNumbersOnly()],
 'note_ar' => ['required', new NotNumbersOnly()],
    'note_en' => ['required', new NotNumbersOnly()],

    // Pricing
    'price' => ['required_unless:is_free,1', 'numeric', 'min:1'],
    'is_free' => ['nullable', 'boolean'],

    // Discount
    'have_discount' => ['nullable', 'boolean'],
    'discount_percentage' => [
        'required_if:have_discount,1',
        'nullable',
        'integer',
        'min:1',
        'max:100',
    ],

    'is_featured' => ['nullable', 'boolean'],

    'is_active' => ['nullable', 'boolean'],

];

    }
}
