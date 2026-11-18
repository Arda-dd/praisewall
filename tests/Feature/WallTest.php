<?php

namespace Tests\Feature;

use App\Models\Testimonial;
use App\Models\User;
use App\Models\Wall;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WallTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    /**
     * @return array<string, mixed>
     */
    private function settings(array $overrides = []): array
    {
        return [
            'name' => 'Orbit Analytics',
            'headline' => 'Share your experience',
            'prompt' => 'What did you like the most?',
            'accent_color' => '#0ea5e9',
            'thank_you_message' => 'Thanks!',
            'collecting' => '1',
            'ask_for_rating' => '1',
            'ask_for_avatar' => '0',
            'notify' => '1',
            'style' => 'glass',
            'locale' => 'tr',
            ...$overrides,
        ];
    }

    public function test_creating_a_wall_derives_the_link_from_the_name(): void
    {
        $this->post(route('walls.store'), $this->settings())->assertRedirect(route('walls.show', 'orbit-analytics'));

        $wall = Wall::sole();
        $this->assertSame('orbit-analytics', $wall->slug);
        $this->assertTrue($wall->collecting);
        $this->assertFalse($wall->ask_for_avatar);
        $this->assertSame('glass', $wall->style);
        $this->assertSame('tr', $wall->locale);
    }

    public function test_settings_are_validated(): void
    {
        Wall::factory()->create(['slug' => 'orbit']);

        $this->post(route('walls.store'), $this->settings([
            'slug' => 'orbit',
            'accent_color' => 'red',
            'style' => 'neon',
            'locale' => 'xx',
        ]))->assertSessionHasErrors(['slug', 'accent_color', 'style', 'locale']);
    }

    public function test_the_index_shows_counts(): void
    {
        $wall = Wall::factory()->create(['name' => 'Orbit']);
        Testimonial::factory()->for($wall)->count(2)->create();
        Testimonial::factory()->for($wall)->approved()->count(3)->create(['rating' => 4]);

        $this->get(route('walls.index'))
            ->assertOk()
            ->assertSee('Orbit')
            ->assertSee('3 published')
            ->assertSee('2 new')
            ->assertSee('4.0');
    }

    public function test_updating_and_replacing_the_logo(): void
    {
        Storage::fake('public');
        $wall = Wall::factory()->create();

        $this->put(route('walls.update', $wall), $this->settings([
            'slug' => $wall->slug,
            'logo' => UploadedFile::fake()->image('logo.png'),
        ]))->assertRedirect(route('walls.edit', $wall));

        $first = $wall->refresh()->logo_path;
        Storage::disk('public')->assertExists($first);

        $this->put(route('walls.update', $wall), $this->settings(['slug' => $wall->slug, 'remove_logo' => '1']));
        $this->assertNull($wall->refresh()->logo_path);
        Storage::disk('public')->assertMissing($first);
    }

    public function test_deleting_a_wall_deletes_its_testimonials_and_files(): void
    {
        Storage::fake('public');
        $avatar = UploadedFile::fake()->image('a.jpg')->store('avatars', 'public');
        $wall = Wall::factory()->create();
        Testimonial::factory()->for($wall)->create(['avatar_path' => $avatar]);

        $this->delete(route('walls.destroy', $wall))->assertRedirect(route('walls.index'));

        $this->assertModelMissing($wall);
        $this->assertDatabaseCount('testimonials', 0);
        Storage::disk('public')->assertMissing($avatar);
    }

    public function test_the_widget_designer_has_the_feed_url_and_every_option(): void
    {
        $wall = Wall::factory()->create(['style' => 'bubble']);

        $this->get(route('walls.embed', $wall))
            ->assertOk()
            ->assertSee($wall->apiUrl())
            ->assertSee('widget.js')
            ->assertSee('data-wall-style="bubble"', false)
            ->assertSee(['Slider', 'Popup', 'Badge', 'Glass', 'Elegant']);
    }
}
