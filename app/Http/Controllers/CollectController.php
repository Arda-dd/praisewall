<?php

namespace App\Http\Controllers;

use App\Enums\TestimonialSource;
use App\Enums\TestimonialStatus;
use App\Models\User;
use App\Models\Wall;
use App\Notifications\NewTestimonial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;
use Throwable;

/**
 * The public form customers use to leave a testimonial, in the wall's language.
 */
class CollectController extends Controller
{
    public function show(Wall $wall): View
    {
        App::setLocale($wall->locale);

        return view('collect.show', compact('wall'));
    }

    public function store(Request $request, Wall $wall): RedirectResponse
    {
        abort_unless($wall->collecting, 404);
        App::setLocale($wall->locale);

        // Bots fill in every field, people never see this one.
        if (filled($request->input('website'))) {
            return redirect()->route('collect.thanks', $wall);
        }

        $data = $request->validate([
            'rating' => [$wall->ask_for_rating ? 'required' : 'nullable', 'integer', 'between:1,5'],
            'body' => ['required', 'string', 'min:10', 'max:2000'],
            'author_name' => ['required', 'string', 'max:80'],
            'author_title' => ['nullable', 'string', 'max:120'],
            'author_email' => ['nullable', 'email', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'consent' => ['accepted'],
        ], [
            'required' => __('praisewall.validation.required'),
            'max' => __('praisewall.validation.too_long'),
            'rating.*' => __('praisewall.validation.rating'),
            'body.min' => __('praisewall.validation.body_min'),
            'author_email.email' => __('praisewall.validation.email'),
            'avatar.*' => __('praisewall.validation.image'),
            'consent.accepted' => __('praisewall.validation.consent'),
        ]);

        $testimonial = $wall->testimonials()->make([
            'rating' => $wall->ask_for_rating ? $data['rating'] : null,
            'body' => $data['body'],
            'author_name' => $data['author_name'],
            'author_title' => $data['author_title'] ?? null,
            'author_email' => $data['author_email'] ?? null,
            'source' => TestimonialSource::Form,
            'status' => TestimonialStatus::Pending,
        ]);

        if ($wall->ask_for_avatar && $request->hasFile('avatar')) {
            $testimonial->avatar_path = $request->file('avatar')->store('avatars', 'public');
        }

        $testimonial->save();

        if ($wall->notify) {
            // A broken mail setup shouldn't cost the customer their testimonial.
            try {
                Notification::send(User::all(), new NewTestimonial($testimonial));
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        return redirect()->route('collect.thanks', $wall);
    }

    public function thanks(Wall $wall): View
    {
        App::setLocale($wall->locale);

        return view('collect.thanks', compact('wall'));
    }
}
