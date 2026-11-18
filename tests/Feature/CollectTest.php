<?php

namespace Tests\Feature;

use App\Enums\TestimonialSource;
use App\Enums\TestimonialStatus;
use App\Models\Testimonial;
use App\Models\Wall;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CollectTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function submission(array $overrides = []): array
    {
        return [
            'rating' => 5,
            'body' => 'It cut our onboarding time in half. Our customers noticed.',
            'author_name' => 'Maya Kaya',
            'author_title' => 'CTO at Orbit',
            'author_email' => 'maya@example.com',
            'consent' => '1',
            ...$overrides,
        ];
    }

    public function test_the_form_shows_the_wall_settings(): void
    {
        $wall = Wall::factory()->create(['headline' => 'Tell us about Orbit', 'prompt' => 'What changed for you?']);

        $this->get("/c/{$wall->slug}")
            ->assertOk()
            ->assertSee('Tell us about Orbit')
            ->assertSee('What changed for you?')
            ->assertSee('Your rating');
    }

    public function test_a_submission_lands_in_the_inbox_as_pending(): void
    {
        $wall = Wall::factory()->create();

        $this->post("/c/{$wall->slug}", $this->submission())
            ->assertRedirect(route('collect.thanks', $wall));

        $testimonial = $wall->testimonials()->sole();
        $this->assertSame(TestimonialStatus::Pending, $testimonial->status);
        $this->assertSame(TestimonialSource::Form, $testimonial->source);
        $this->assertSame(5, $testimonial->rating);
        $this->assertSame('maya@example.com', $testimonial->author_email);
        $this->assertNull($testimonial->approved_at);
    }

    public function test_it_requires_consent_and_a_real_answer(): void
    {
        $wall = Wall::factory()->create();

        $this->post("/c/{$wall->slug}", $this->submission(['consent' => null, 'body' => 'ok']))
            ->assertSessionHasErrors(['consent', 'body']);

        $this->assertDatabaseCount('testimonials', 0);
    }

    public function test_the_rating_follows_the_wall_setting(): void
    {
        $asks = Wall::factory()->create();
        $this->post("/c/{$asks->slug}", $this->submission(['rating' => null]))->assertSessionHasErrors('rating');
        $this->post("/c/{$asks->slug}", $this->submission(['rating' => 6]))->assertSessionHasErrors('rating');

        $doesNotAsk = Wall::factory()->create(['ask_for_rating' => false]);
        $this->post("/c/{$doesNotAsk->slug}", $this->submission(['rating' => 1]))->assertSessionHasNoErrors();
        $this->assertNull($doesNotAsk->testimonials()->sole()->rating);
    }

    public function test_a_photo_is_stored_when_the_wall_asks_for_one(): void
    {
        Storage::fake('public');
        $wall = Wall::factory()->create();

        $this->post("/c/{$wall->slug}", $this->submission([
            'avatar' => UploadedFile::fake()->image('me.jpg', 200, 200),
        ]))->assertSessionHasNoErrors();

        $path = $wall->testimonials()->sole()->avatar_path;
        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_only_images_are_accepted_as_photos(): void
    {
        Storage::fake('public');
        $wall = Wall::factory()->create();

        $this->post("/c/{$wall->slug}", $this->submission([
            'avatar' => UploadedFile::fake()->create('me.svg', 5, 'image/svg+xml'),
        ]))->assertSessionHasErrors('avatar');
    }

    public function test_bots_that_fill_the_honeypot_are_ignored(): void
    {
        $wall = Wall::factory()->create();

        $this->post("/c/{$wall->slug}", $this->submission(['website' => 'https://spam.example']))
            ->assertRedirect(route('collect.thanks', $wall));

        $this->assertDatabaseCount('testimonials', 0);
    }

    public function test_a_closed_wall_does_not_accept_testimonials(): void
    {
        $wall = Wall::factory()->create(['collecting' => false]);

        $this->get("/c/{$wall->slug}")->assertOk()->assertSee("isn't collecting testimonials right now");
        $this->post("/c/{$wall->slug}", $this->submission())->assertNotFound();
        $this->assertDatabaseCount('testimonials', 0);
    }

    public function test_submissions_are_rate_limited(): void
    {
        RateLimiter::clear('collect');
        $wall = Wall::factory()->create();

        for ($i = 0; $i < 3; $i++) {
            $this->post("/c/{$wall->slug}", $this->submission())->assertRedirect();
        }

        $this->post("/c/{$wall->slug}", $this->submission())->assertTooManyRequests();
        $this->assertSame(3, Testimonial::count());
    }

    public function test_an_unknown_wall_is_not_found(): void
    {
        $this->get('/c/nope')->assertNotFound();
    }
}
