<?php

namespace App\Http\Requests\Dashboard;

use App\Models\Banner;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBannerRequest extends FormRequest
{
    public function authorize()
    {
        return abilities()->contains('create_banners');
    }

    protected function prepareForValidation()
    {
        // Unchecked switches are not sent at all
        $this->merge([
            'is_active'       => $this->boolean('is_active'),
            'open_in_new_tab' => $this->boolean('open_in_new_tab'),
            'show_countdown'  => $this->boolean('show_countdown'),
            'sort_order'      => $this->input('sort_order') ?: 0,
        ]);
    }

    public function rules()
    {
        return [
            'placement'       => ['required', Rule::in(array_keys(Banner::PLACEMENTS))],
            // The top bar is text only, everything else needs a picture
            'image'           => [$this->imageRequired() ? 'required' : 'nullable', 'image', 'mimes:jpg,png,jpeg,gif,webp', 'max:4096'],
            'mobile_image'    => ['nullable', 'image', 'mimes:jpg,png,jpeg,gif,webp', 'max:4096'],

            'title_ar'        => ['required', 'string', 'max:255'],
            'title_en'        => ['nullable', 'string', 'max:255'],
            'subtitle_ar'     => ['nullable', 'string', 'max:500'],
            'subtitle_en'     => ['nullable', 'string', 'max:500'],
            'badge_ar'        => ['nullable', 'string', 'max:60'],
            'badge_en'        => ['nullable', 'string', 'max:60'],

            'btn_title_ar'    => ['nullable', 'string', 'max:255'],
            'btn_title_en'    => ['nullable', 'string', 'max:255'],
            // A page of the website ("/courses/5") or a full URL
            'link'            => ['nullable', 'string', 'max:1000', 'regex:#^(/|https?://)#i'],
            'open_in_new_tab' => ['boolean'],

            'bg_color'        => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'text_color'      => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],

            'audience'        => ['required', Rule::in(array_keys(Banner::AUDIENCES))],
            'category_id'     => ['nullable', 'exists:categories,id'],

            'starts_at'       => ['nullable', 'date'],
            'ends_at'         => ['nullable', 'date', 'after:starts_at', 'required_if:show_countdown,true'],
            'show_countdown'  => ['boolean'],

            'sort_order'      => ['integer', 'min:0'],
            'is_active'       => ['boolean'],
        ];
    }

    public function messages()
    {
        return [
            'link.regex'          => __('The link must start with / for a page of the website, or with http(s)://'),
            'ends_at.required_if' => __('Set an end date to show the countdown'),
        ];
    }

    protected function imageRequired(): bool
    {
        return $this->input('placement') !== 'top_bar';
    }
}
