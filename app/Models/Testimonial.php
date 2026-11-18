<?php

namespace App\Models;

use App\Enums\TestimonialSource;
use App\Enums\TestimonialStatus;
use Database\Factories\TestimonialFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable([
    'status', 'source', 'rating', 'body', 'author_name', 'author_title',
    'author_email', 'author_url', 'avatar_path', 'featured', 'approved_at',
])]
#[Hidden(['author_email'])]
class Testimonial extends Model
{
    /** @use HasFactory<TestimonialFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => TestimonialStatus::class,
            'source' => TestimonialSource::class,
            'rating' => 'integer',
            'featured' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Wall, $this>
     */
    public function wall(): BelongsTo
    {
        return $this->belongsTo(Wall::class);
    }

    public function approve(): void
    {
        $this->update([
            'status' => TestimonialStatus::Approved,
            'approved_at' => $this->approved_at ?? now(),
        ]);
    }

    public function reject(): void
    {
        $this->update(['status' => TestimonialStatus::Rejected, 'featured' => false]);
    }

    public function avatarUrl(): ?string
    {
        return $this->avatar_path ? Storage::disk('public')->url($this->avatar_path) : null;
    }

    public function initials(): string
    {
        $words = preg_split('/\s+/u', trim($this->author_name)) ?: [];
        $first = $words[0] ?? '';
        $last = count($words) > 1 ? end($words) : '';

        return Str::upper(mb_substr($first, 0, 1).mb_substr($last, 0, 1));
    }

    /**
     * @return array<string, mixed>
     */
    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'body' => $this->body,
            'rating' => $this->rating,
            'featured' => $this->featured,
            'author' => [
                'name' => $this->author_name,
                'title' => $this->author_title,
                'url' => $this->author_url,
                'avatar_url' => $this->avatarUrl(),
                'initials' => $this->initials(),
            ],
            'date' => ($this->approved_at ?? $this->created_at)?->toDateString(),
        ];
    }
}
