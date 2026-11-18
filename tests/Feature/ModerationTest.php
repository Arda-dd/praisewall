<?php

namespace Tests\Feature;

use App\Enums\TestimonialSource;
use App\Enums\TestimonialStatus;
use App\Models\Testimonial;
use App\Models\User;
use App\Models\Wall;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ModerationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_the_inbox_lists_testimonials_by_status(): void
    {
        $wall = Wall::factory()->create();
        Testimonial::factory()->for($wall)->create(['author_name' => 'Pending Person']);
        Testimonial::factory()->for($wall)->approved()->create(['author_name' => 'Approved Person']);

        $this->get(route('walls.show', $wall))
            ->assertOk()
            ->assertSee('Pending Person')
            ->assertDontSee('Approved Person');

        $this->get(route('walls.show', [$wall, 'status' => 'approved']))
            ->assertSee('Approved Person')
            ->assertDontSee('Pending Person');
    }

    public function test_approving_publishes_and_records_the_time(): void
    {
        $testimonial = Testimonial::factory()->create();

        $this->patch(route('testimonials.status', $testimonial), ['status' => 'approved'])->assertRedirect();

        $testimonial->refresh();
        $this->assertSame(TestimonialStatus::Approved, $testimonial->status);
        $this->assertNotNull($testimonial->approved_at);
    }

    public function test_rejecting_also_unpins(): void
    {
        $testimonial = Testimonial::factory()->featured()->create();

        $this->patch(route('testimonials.status', $testimonial), ['status' => 'rejected']);

        $testimonial->refresh();
        $this->assertSame(TestimonialStatus::Rejected, $testimonial->status);
        $this->assertFalse($testimonial->featured);
    }

    public function test_an_unknown_status_is_rejected(): void
    {
        $testimonial = Testimonial::factory()->create();

        $this->patch(route('testimonials.status', $testimonial), ['status' => 'published'])
            ->assertSessionHasErrors('status');
    }

    public function test_only_approved_testimonials_can_be_pinned(): void
    {
        $pending = Testimonial::factory()->create();
        $this->patch(route('testimonials.feature', $pending))->assertStatus(422);

        $approved = Testimonial::factory()->approved()->create();
        $this->patch(route('testimonials.feature', $approved));
        $this->assertTrue($approved->refresh()->featured);

        $this->patch(route('testimonials.feature', $approved));
        $this->assertFalse($approved->refresh()->featured);
    }

    public function test_a_testimonial_can_be_added_by_hand(): void
    {
        $wall = Wall::factory()->create();

        $this->post(route('testimonials.store', $wall), [
            'body' => 'Sent by email: the best support we have had.',
            'author_name' => 'Jon',
            'author_url' => 'https://example.com/jon',
            'rating' => 5,
        ])->assertRedirect(route('walls.show', [$wall, 'status' => 'approved']));

        $testimonial = $wall->testimonials()->sole();
        $this->assertSame(TestimonialSource::Manual, $testimonial->source);
        $this->assertSame(TestimonialStatus::Approved, $testimonial->status);
    }

    public function test_links_must_be_http(): void
    {
        $wall = Wall::factory()->create();

        $this->post(route('testimonials.store', $wall), [
            'body' => 'Nice.',
            'author_name' => 'Eve',
            'author_url' => 'javascript:alert(1)',
        ])->assertSessionHasErrors('author_url');
    }

    public function test_editing_and_replacing_the_photo(): void
    {
        Storage::fake('public');
        $old = UploadedFile::fake()->image('old.jpg')->store('avatars', 'public');
        $testimonial = Testimonial::factory()->approved()->create(['avatar_path' => $old]);

        $this->put(route('testimonials.update', $testimonial), [
            'body' => 'Fixed a typo.',
            'author_name' => $testimonial->author_name,
            'avatar' => UploadedFile::fake()->image('new.jpg'),
        ])->assertRedirect();

        $testimonial->refresh();
        $this->assertSame('Fixed a typo.', $testimonial->body);
        Storage::disk('public')->assertMissing($old);
        Storage::disk('public')->assertExists($testimonial->avatar_path);
    }

    public function test_deleting_removes_the_photo(): void
    {
        Storage::fake('public');
        $path = UploadedFile::fake()->image('a.jpg')->store('avatars', 'public');
        $testimonial = Testimonial::factory()->create(['avatar_path' => $path]);

        $this->delete(route('testimonials.destroy', $testimonial))->assertRedirect();

        $this->assertModelMissing($testimonial);
        Storage::disk('public')->assertMissing($path);
    }
}
