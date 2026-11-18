<?php

namespace App\Models;

use App\Enums\TestimonialStatus;
use Database\Factories\WallFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'slug', 'name', 'headline', 'prompt', 'accent_color', 'logo_path',
    'thank_you_message', 'collecting', 'ask_for_rating', 'ask_for_avatar',
    'style', 'locale', 'notify',
])]
class Wall extends Model
{
    /** @use HasFactory<WallFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'collecting' => 'boolean',
            'ask_for_rating' => 'boolean',
            'ask_for_avatar' => 'boolean',
            'notify' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Testimonial, $this>
     */
    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class);
    }

    /**
     * Approved testimonials in display order: featured first, then newest.
     *
     * @return HasMany<Testimonial, $this>
     */
    public function published(): HasMany
    {
        return $this->testimonials()
            ->where('status', TestimonialStatus::Approved)
            ->orderByDesc('featured')
            ->orderByDesc('approved_at')
            ->orderByDesc('id');
    }

    public function logoUrl(): ?string
    {
        return $this->logo_path ? Storage::disk('public')->url($this->logo_path) : null;
    }

    public function collectUrl(): string
    {
        return route('collect.show', $this);
    }

    public function wallUrl(): string
    {
        return route('wall.show', $this);
    }

    public function apiUrl(): string
    {
        return route('api.wall', $this);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * The data the widget and the API expose. It never contains email addresses.
     *
     * @return array<string, mixed>
     */
    public function toPublicArray(): array
    {
        $testimonials = $this->published;
        $ratings = $testimonials->pluck('rating')->filter();

        return [
            'name' => $this->name,
            'accent_color' => $this->accent_color,
            'style' => $this->style,
            'locale' => $this->locale,
            'logo_url' => $this->logoUrl(),
            'wall_url' => $this->wallUrl(),
            'collect_url' => $this->collecting ? $this->collectUrl() : null,
            'summary' => [
                'count' => $testimonials->count(),
                'rated' => $ratings->count(),
                'average' => $ratings->isEmpty() ? null : round($ratings->avg(), 1),
            ],
            'labels' => $this->widgetLabels(),
            'testimonials' => $testimonials->map->toPublicArray()->values()->all(),
        ];
    }

    /**
     * The widget's texts in the wall's language. Placeholders like :count are filled in by the widget,
     * and "reviews" is sent as [singular, plural].
     *
     * @return array<string, string|array<int, string>>
     */
    public function widgetLabels(): array
    {
        $labels = trans('praisewall.widget', [], $this->locale);
        $labels['reviews'] = explode('|', $labels['reviews'], 2) + [1 => $labels['reviews']];
        $labels['about'] = str_replace(':name', $this->name, $labels['about']);

        return $labels;
    }
}
