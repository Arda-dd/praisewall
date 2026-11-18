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
        return [
            'name' => $this->name,
            'accent_color' => $this->accent_color,
            'logo_url' => $this->logoUrl(),
            'wall_url' => $this->wallUrl(),
            'collect_url' => $this->collecting ? $this->collectUrl() : null,
            'testimonials' => $this->published->map->toPublicArray()->values()->all(),
        ];
    }
}
