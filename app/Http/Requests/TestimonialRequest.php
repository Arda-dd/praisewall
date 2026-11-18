<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * A testimonial added or edited by the admin.
 */
class TestimonialRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:2000'],
            'rating' => ['nullable', 'integer', 'between:1,5'],
            'author_name' => ['required', 'string', 'max:80'],
            'author_title' => ['nullable', 'string', 'max:120'],
            'author_email' => ['nullable', 'email', 'max:255'],
            'author_url' => ['nullable', 'url:http,https', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'remove_avatar' => ['boolean'],
        ];
    }
}
