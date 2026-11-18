<?php

namespace Tests\Feature;

use App\Models\Testimonial;
use App\Models\Wall;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_feed_has_only_approved_testimonials_featured_first(): void
    {
        $wall = Wall::factory()->create(['name' => 'Orbit']);
        Testimonial::factory()->for($wall)->create(['author_name' => 'Pending']);
        Testimonial::factory()->for($wall)->rejected()->create(['author_name' => 'Rejected']);
        Testimonial::factory()->for($wall)->approved()->create(['author_name' => 'Older', 'approved_at' => now()->subDay()]);
        Testimonial::factory()->for($wall)->approved()->create(['author_name' => 'Newer']);
        Testimonial::factory()->for($wall)->featured()->create(['author_name' => 'Pinned', 'approved_at' => now()->subWeek()]);
        Testimonial::factory()->approved()->create(['author_name' => 'Other wall']);

        $response = $this->getJson(route('api.wall', $wall))
            ->assertOk()
            ->assertHeader('Access-Control-Allow-Origin', '*')
            ->assertJsonPath('name', 'Orbit');

        $this->assertSame(['Pinned', 'Newer', 'Older'], array_column(array_column($response->json('testimonials'), 'author'), 'name'));
    }

    public function test_the_feed_never_contains_email_addresses(): void
    {
        $wall = Wall::factory()->create();
        Testimonial::factory()->for($wall)->approved()->create(['author_email' => 'secret@example.com']);

        $response = $this->getJson(route('api.wall', $wall));

        $this->assertStringNotContainsString('secret@example.com', $response->getContent());
        $this->assertStringNotContainsString('email', $response->getContent());
    }

    public function test_the_feed_shape(): void
    {
        $wall = Wall::factory()->create();
        Testimonial::factory()->for($wall)->approved()->create([
            'author_name' => 'Maya Kaya',
            'author_title' => 'CTO',
            'rating' => 4,
        ]);

        $this->getJson(route('api.wall', $wall))->assertJsonStructure([
            'name', 'accent_color', 'logo_url', 'wall_url', 'collect_url',
            'testimonials' => [['id', 'body', 'rating', 'featured', 'date', 'author' => ['name', 'title', 'url', 'avatar_url', 'initials']]],
        ])->assertJsonPath('testimonials.0.author.initials', 'MK');
    }

    public function test_a_closed_wall_has_no_collect_link(): void
    {
        $wall = Wall::factory()->create(['collecting' => false]);

        $this->getJson(route('api.wall', $wall))->assertJsonPath('collect_url', null);
    }

    public function test_unchanged_feeds_answer_304(): void
    {
        $wall = Wall::factory()->create();
        $etag = $this->getJson(route('api.wall', $wall))->headers->get('ETag');

        $this->getJson(route('api.wall', $wall), ['If-None-Match' => $etag])->assertStatus(304);
    }

    public function test_the_public_wall_page_embeds_the_feed_safely(): void
    {
        $wall = Wall::factory()->create(['name' => 'Orbit']);
        Testimonial::factory()->for($wall)->approved()->create(['body' => '</script><script>alert(1)</script>']);
        Testimonial::factory()->for($wall)->create(['body' => 'Still pending']);

        $this->get(route('wall.show', $wall))
            ->assertOk()
            ->assertSee('What people say about Orbit')
            ->assertDontSee('</script><script>alert(1)', false)
            ->assertDontSee('Still pending');
    }
}
