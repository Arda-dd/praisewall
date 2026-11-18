<?php

namespace App\Http\Controllers;

use App\Enums\TestimonialSource;
use App\Enums\TestimonialStatus;
use App\Models\Wall;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The public form customers use to leave a testimonial.
 */
class CollectController extends Controller
{
    public function show(Wall $wall): View
    {
        return view('collect.show', compact('wall'));
    }

    public function store(Request $request, Wall $wall): RedirectResponse
    {
        abort_unless($wall->collecting, 404);

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
            'consent.accepted' => 'Please confirm that we may show your testimonial.',
            'body.min' => 'Please write a little more, at least a sentence.',
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

        return redirect()->route('collect.thanks', $wall);
    }

    public function thanks(Wall $wall): View
    {
        return view('collect.thanks', compact('wall'));
    }
}
