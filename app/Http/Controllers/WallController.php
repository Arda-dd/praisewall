<?php

namespace App\Http\Controllers;

use App\Enums\TestimonialStatus;
use App\Http\Requests\WallRequest;
use App\Models\Wall;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class WallController extends Controller
{
    public function index(): View
    {
        $walls = Wall::query()
            ->withCount([
                'testimonials as pending_count' => fn ($query) => $query->where('status', TestimonialStatus::Pending),
                'testimonials as approved_count' => fn ($query) => $query->where('status', TestimonialStatus::Approved),
            ])
            ->withAvg(['testimonials as average_rating' => fn ($query) => $query->where('status', TestimonialStatus::Approved)], 'rating')
            ->orderBy('name')
            ->get();

        return view('walls.index', compact('walls'));
    }

    public function create(): View
    {
        return view('walls.create', ['wall' => new Wall([
            'headline' => 'Share your experience',
            'prompt' => 'What did you like the most? How did it help you or your team?',
            'accent_color' => '#6d28d9',
            'thank_you_message' => 'Thank you! Your testimonial will appear once it has been reviewed.',
            'collecting' => true,
            'ask_for_rating' => true,
            'ask_for_avatar' => true,
        ])]);
    }

    public function store(WallRequest $request): RedirectResponse
    {
        $wall = new Wall($request->safe()->except(['logo', 'remove_logo']));
        $this->saveLogo($request, $wall);
        $wall->save();

        return redirect()->route('walls.show', $wall)->with('status', 'Wall created. Share the collection link to start getting testimonials.');
    }

    public function show(Request $request, Wall $wall): View
    {
        $status = TestimonialStatus::tryFrom((string) $request->query('status')) ?? TestimonialStatus::Pending;

        $testimonials = $wall->testimonials()
            ->where('status', $status)
            ->orderByDesc('featured')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $counts = $wall->testimonials()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('walls.show', compact('wall', 'status', 'testimonials', 'counts'));
    }

    public function edit(Wall $wall): View
    {
        return view('walls.edit', compact('wall'));
    }

    public function update(WallRequest $request, Wall $wall): RedirectResponse
    {
        $wall->fill($request->safe()->except(['logo', 'remove_logo']));
        $this->saveLogo($request, $wall);
        $wall->save();

        return redirect()->route('walls.edit', $wall)->with('status', 'Settings saved.');
    }

    public function destroy(Wall $wall): RedirectResponse
    {
        $paths = $wall->testimonials()->whereNotNull('avatar_path')->pluck('avatar_path')
            ->push($wall->logo_path)
            ->filter()
            ->all();

        $wall->delete();
        Storage::disk('public')->delete($paths);

        return redirect()->route('walls.index')->with('status', "\"{$wall->name}\" was deleted.");
    }

    private function saveLogo(WallRequest $request, Wall $wall): void
    {
        $remove = $request->boolean('remove_logo') || $request->hasFile('logo');

        if ($remove && $wall->logo_path) {
            Storage::disk('public')->delete($wall->logo_path);
            $wall->logo_path = null;
        }

        if ($request->hasFile('logo')) {
            $wall->logo_path = $request->file('logo')->store('logos', 'public');
        }
    }
}
