<?php

namespace App\Http\Requests;

use App\Models\Wall;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class WallRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $slug = $this->input('slug') ?: $this->input('name');

        $this->merge([
            'slug' => Str::slug((string) $slug),
            'collecting' => $this->boolean('collecting'),
            'ask_for_rating' => $this->boolean('ask_for_rating'),
            'ask_for_avatar' => $this->boolean('ask_for_avatar'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Wall|null $wall */
        $wall = $this->route('wall');

        return [
            'name' => ['required', 'string', 'max:80'],
            'slug' => ['required', 'string', 'max:80', 'alpha_dash', Rule::unique('walls', 'slug')->ignore($wall)],
            'headline' => ['required', 'string', 'max:120'],
            'prompt' => ['required', 'string', 'max:500'],
            'accent_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'thank_you_message' => ['required', 'string', 'max:500'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:1024'],
            'remove_logo' => ['boolean'],
            'collecting' => ['boolean'],
            'ask_for_rating' => ['boolean'],
            'ask_for_avatar' => ['boolean'],
        ];
    }
}
