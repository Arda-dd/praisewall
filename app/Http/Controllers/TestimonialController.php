<?php

namespace App\Http\Controllers;

use App\Enums\TestimonialSource;
use App\Enums\TestimonialStatus;
use App\Http\Requests\TestimonialRequest;
use App\Models\Testimonial;
use App\Models\Wall;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TestimonialController extends Controller
{
    /**
     * Add a testimonial by hand, for example one that came in by email or on a review site.
     */
    public function store(TestimonialRequest $request, Wall $wall): RedirectResponse
    {
        $testimonial = $wall->testimonials()->make([
            ...$request->safe()->except(['avatar', 'remove_avatar']),
            'source' => TestimonialSource::Manual,
            'status' => TestimonialStatus::Approved,
            'approved_at' => now(),
        ]);
        $this->saveAvatar($request, $testimonial);
        $testimonial->save();

        return redirect()
            ->route('walls.show', [$wall, 'status' => TestimonialStatus::Approved->value])
            ->with('status', 'Testimonial added to the wall.');
    }

    public function edit(Testimonial $testimonial): View
    {
        return view('testimonials.edit', compact('testimonial'));
    }

    public function update(TestimonialRequest $request, Testimonial $testimonial): RedirectResponse
    {
        $testimonial->fill($request->safe()->except(['avatar', 'remove_avatar']));
        $this->saveAvatar($request, $testimonial);
        $testimonial->save();

        return redirect()
            ->route('walls.show', [$testimonial->wall, 'status' => $testimonial->status->value])
            ->with('status', 'Testimonial updated.');
    }

    public function status(Request $request, Testimonial $testimonial): RedirectResponse
    {
        $status = TestimonialStatus::from($request->validate([
            'status' => ['required', Rule::enum(TestimonialStatus::class)],
        ])['status']);

        match ($status) {
            TestimonialStatus::Approved => $testimonial->approve(),
            TestimonialStatus::Rejected => $testimonial->reject(),
            TestimonialStatus::Pending => $testimonial->update(['status' => $status, 'featured' => false]),
        };

        return back()->with('status', "Moved to {$status->label()}.");
    }

    public function feature(Testimonial $testimonial): RedirectResponse
    {
        abort_unless($testimonial->status === TestimonialStatus::Approved, 422, 'Only approved testimonials can be featured.');

        $testimonial->update(['featured' => ! $testimonial->featured]);

        return back()->with('status', $testimonial->featured ? 'Pinned to the top of the wall.' : 'Unpinned.');
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        if ($testimonial->avatar_path) {
            Storage::disk('public')->delete($testimonial->avatar_path);
        }

        $testimonial->delete();

        return back()->with('status', 'Testimonial deleted.');
    }

    private function saveAvatar(TestimonialRequest $request, Testimonial $testimonial): void
    {
        $remove = $request->boolean('remove_avatar') || $request->hasFile('avatar');

        if ($remove && $testimonial->avatar_path) {
            Storage::disk('public')->delete($testimonial->avatar_path);
            $testimonial->avatar_path = null;
        }

        if ($request->hasFile('avatar')) {
            $testimonial->avatar_path = $request->file('avatar')->store('avatars', 'public');
        }
    }
}
